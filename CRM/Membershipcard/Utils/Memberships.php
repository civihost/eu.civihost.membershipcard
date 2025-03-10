<?php

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Civi\Token\TokenProcessor;
use CRM_Membershipcard_ExtensionUtil as E;

class CRM_Membershipcard_Utils_Memberships
{
  /**
   * Returns the current membership year
   *
   * @return integer
   */
  public static function currentYear()
  {
    $year = date('Y');
    $year_starts_from = CRM_Membershipcard_Utils_Config::get('memberships.year_starts_from');
    if ($year_starts_from && time() > strtotime(date('Y') . '-' . $year_starts_from)) {
      $year = date('Y') + 1;
    }
    return $year;
  }

  /**
   * Return the membership details for the contact if it has a valid contribution for the year
   *
   * @param int|null $membership_id
   * @param int|null $year
   * @return array
   */
  public static function getContactActiveMembership($contact_id, $membership_id = null, $year = null)
  {
    $year = $year ?? self::currentYear();

    $contributionCompletedStatusId = CRM_Core_PseudoConstant::getKey('CRM_Contribute_BAO_Contribution', 'contribution_status_id', 'Completed');

    if ($membership_id) {
      $membership_where = "m.id = {$membership_id}";
    } else {
      $membership_where = "m.contact_id = {$contact_id}";
    }

    // @todo How to handle the year parameter: now we assume that membership is active and is current
    $sql = "select m.*,
      c.first_name, c.last_name,
      contribution.receive_date,
      p.contribution_id,
      concat('{$year}-', m.id) as card_number
      from civicrm_membership as m
      left outer join civicrm_membership_status as status_id_1 ON m.status_id =  status_id_1.id
      left outer join civicrm_contact as c on m.contact_id = c.id
      left outer join civicrm_membership_payment as p on p.membership_id = if(m.owner_membership_id is null, m.id, m.owner_membership_id)
      left outer join civicrm_contribution as contribution on contribution.id = p.contribution_id
      where
        {$membership_where}
        and status_id_1.is_active = 1 and status_id_1.is_current_member = 1
        and (contribution.contribution_status_id = {$contributionCompletedStatusId} or contribution.id is null)
        ";

    $result = [];
    $query = \CRM_Core_DAO::executeQuery($sql);
    while ($query->fetch()) {
      $result = (array) $query;
    }
    if ($result) {
      $result = array_merge($result, [
        'year' => $year,
      ]);
    }

    return $result;
  }

  public static function generateMemberCard($membership, $output = FALSE)
  {
    $membership = self::getContactActiveMembership($membership['contact_id'], $membership['id'], $membership['year']);
    if (!$membership) {
      return;
    }

    $select = [];
    $custom_fields = CRM_Membershipcard_Utils_Config::get('memberships.card.custom_fields') ?? [];
    foreach($custom_fields as $field) {
        $select[] = $field;
    }

    if ($select) {
        $custom_contact = civicrm_api4('Contact', 'get', [
        'select' => $select,
        'where' => [
            ['id', '=', $membership['contact_id']],
        ],
        'checkPermissions' => FALSE,
        ])->single();
        foreach($custom_contact as $k => $v) {
            $membership[str_replace('.', '_', $k)] = $v;
        }
    }


    // assign info to template
    $smarty = CRM_Core_Smarty::singleton();
    foreach ($membership as $name => $value) {
      $smarty->assign($name, $value);
    }

    // barcode
    //$generator = new Picqer\Barcode\BarcodeGeneratorPNG();
    //$barcode = $generator->getBarcode($membership['card_number'], $generator::TYPE_CODE_128, 2, 20, [42, 63, 108]);
    //$smarty->assign('barcode_base64', base64_encode($barcode));

    $tokenProcessor = new TokenProcessor(Civi::dispatcher(), ['schema' => ['contactId']]);
    $tokenProcessor->addMessage('barcode', CRM_Membershipcard_Utils_Config::get('memberships.card.barcode_text'), 'text/html');
    $tokenProcessor->addRow(['contactId' => $membership['contact_id']]);
    $tokenProcessor->evaluate();
    $row = $tokenProcessor->getRow(0);
    $barcode = trim($row->render('barcode'));

    // instance will be invoked with default settings
    $qrcode  = new QRCode;
    $options = new QROptions;

    $options->quietzoneSize = 1;
    $qrcode->setOptions($options);

    $smarty->assign('barcode', $qrcode->render($barcode));

    $smarty->assign('backgroundimgfront', sprintf(CRM_Membershipcard_Utils_Config::get('memberships.card.url'), $membership['year']));

    // call template
    $template = 'MembershipCard.tpl';
    try {
      $content = $smarty->fetch('CRM/Membershipcard/' . $membership['year'] . '/' . $template);
    } catch (\Throwable $th) {
      $content = $smarty->fetch('CRM/Membershipcard/' . $template);
    }

    $fileName = E::ts('MembershipCard-%1.pdf', [1 => $membership['year']]);

    // 'Membership Card' page format
    $pageFormat = CRM_Membershipcard_Utils_Config::get('memberships.card.page_format');

    $pdf = CRM_Utils_PDF_Utils::html2pdf($content, $fileName, $output, $pageFormat);
    if ($output) {
      return [$fileName, $pdf];
    }
    CRM_Utils_System::civiExit();
  }
}
