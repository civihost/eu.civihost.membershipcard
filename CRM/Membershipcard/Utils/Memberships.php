<?php

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
    if (time() > strtotime(date('Y') . '-' . CRM_Membershipcard_Utils_Config::get('memberships.year_starts_from'))) {
      $year = date('Y') + 1;
    } else {
      $year = date('Y');
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
      -- left outer join civicrm_value_anno_di_rifer_22 as y on y.entity_id = p.contribution_id
      where
        {$membership_where}
        and status_id_1.is_active = 1
        and contribution.contribution_status_id = {$contributionCompletedStatusId}
        -- and y.anno_43 = {$year}
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
    /*
    $membership = self::getContactActiveMembership($contact_id, $membership_id, $year);
    if (!$membership) {
      return;
    }*/

    $year = $membership['year'];

    $contacts = \Civi\Api4\Contact::get(TRUE)
      ->addSelect('organization_name', 'website.url', 'email.email', 'email.is_primary')
      ->addJoin('Website AS website', 'LEFT', ['website.contact_id', '=', 'id'], ['website.website_type_id', '=', 2])
      ->addJoin('Email AS email', 'LEFT', ['email.contact_id', '=', 'id'])
      ->addWhere('id', '=', $membership['chapter_id'])
      ->execute();
    foreach ($contacts as $contact) {
      $membership['chapter_name'] = $contact['organization_name'];
      $membership['chapter_url'] = $contact['website.url'];
      if ($contact['email.is_primary']) {
        $membership['chapter_email'] = $contact['email.email'];
      }
    }

    // assign info to template
    $smarty = CRM_Core_Smarty::singleton();
    foreach ($membership as $name => $value) {
      $smarty->assign($name, $value);
    }

    // barcode
    $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
    $barcode = $generator->getBarcode($membership['card_number'], $generator::TYPE_CODE_128, 2, 20, [42, 63, 108]);
    Civi::log()->debug('barcode ' . print_r($barcode, true));
    $smarty->assign('barcode_base64', base64_encode($barcode));

    $smarty->assign('backgroundimgfront', sprintf(CRM_Membershipcard_Utils_Config::get('memberships.card.url'), $year));

    // call template
    $template = 'MembershipCard.tpl';
    try {
      $content = $smarty->fetch('CRM/Membershipcard/' . $year . '/' . $template);
    } catch (\Throwable $th) {
      $content = $smarty->fetch('CRM/Membershipcard/' . $template);
    }

    $fileName = E::ts('MembershipCard-%1.pdf', [1 => $year]);

    // 'Membership Card' page format
    $pageFormat = CRM_Membershipcard_Utils_Config::get('memberships.card.page_format');

    $pdf = CRM_Utils_PDF_Utils::html2pdf($content, $fileName, $output, $pageFormat);
    if ($output) {
      return [$fileName, $pdf];
    }
    CRM_Utils_System::civiExit();
  }
}
