<?php

use CRM_Membershipcard_ExtensionUtil as E;

class CRM_Membershipcard_Mailer
{
  private ?int $card_template_id;
  private ?string $email_address_from;

  /**
   * @throws Exception
   */
  public function __construct()
  {
    $this->card_template_id = CRM_Membershipcard_Utils_Config::get('memberships.card.template');
    if (empty($this->card_template_id)) {
      throw new Exception(
        E::LONG_NAME . " " . "Membership card message template not found."
      );
    }

    try {
      $email_name_mix = CRM_Core_BAO_Domain::getFromEmail();
      $this->email_address_from = CRM_Utils_Mail::pluckEmailFromHeader($email_name_mix);
    } catch (TypeError $typeError) {
      throw new Exception(
        E::LONG_NAME . " " . "Pre selected outgoing email not found. Please set an outgoing email address in CiviCRM Settings $typeError"
      );
    }

    if (empty($this->email_address_from)) {
      throw new Exception(
        E::LONG_NAME . " " . "Pre selected outgoing email is empty. Please set an outgoing email address in CiviCRM settings"
      );
    }
  }

  /**
   * @param $contacts
   * @param $write_activity
   * @return int error count
   */
  public function sendMembershipCard($contacts, $write_activity): int
  {
    $error_count = 0;
    foreach ($contacts as $contact_id => $contact_info) {
      try {

        $membership = CRM_Membershipcard_Utils_Memberships::getContactActiveMembership($contact_id, $contact_info['membership_id'] ?? null, $contact_info['year'] ?? null);
        if (!$membership) {
          continue;
        }

        $attachments = [
          self::createAttachment($membership),
        ];

        $this->sendMail($contact_id, $this->email_address_from, $contact_info['email'] ?? null, $this->card_template_id, $attachments, $membership['contribution_id']);
        if ($write_activity) {
          self::createActivity(
            $contact_info['activity_id'],
            $contact_id,
            2, // = completed
            $membership['year'],
            $membership['card_number'],
            $membership['contribution_id'],
            E::ts('Tessera inviata con successo'),
            E::ts(
              'L\'email con la tessera è stata inviata con successo! È stato usato il template ID %1.',
              [1 => $this->card_template_id]
            )
          );
        }
      } catch (Exception $exception) {
        if ($write_activity) {
          self::createActivity(
            $contact_info['activity_id'],
            $contact_id,
            3, // annulled
            $membership['year'],
            $membership['card_number'],
            $membership['contribution_id'],
            E::ts('Errore nell\'invio della email della tessera'),
            E::ts(
              "Errore nell\'invio della email della tessera con il template ID nr %1. Errore: %2",
              [1 => $this->card_template_id, 2 => $exception]
            )
          );
        }
        ++$error_count;
      }
    }
    return $error_count;
  }

  /**
   * @throws CRM_Core_Exception
   * @throws Exception
   */
  private function sendMail($contact_id, $from_email_address, $to_email_address, $template_id, $attachments = [], $contribution_id): void
  {
    try {
      $contact = \Civi\Api4\Contact::get(FALSE)
        ->addSelect('display_name', 'email.email')
        ->addJoin('Email AS email', 'LEFT', ['email.contact_id', '=', 'id'])
        ->addWhere('id', '=', $contact_id)
        ->addWhere('email.is_primary', '=', '1')
        ->execute()
        ->single();
      civicrm_api3('MessageTemplate', 'send', [
        'check_permissions' => 0,
        'id' => $template_id,
        'to_name' => $contact['display_name'],
        'from' => trim($from_email_address),
        'contact_id' => $contact_id,
        'to_email' => $to_email_address ? trim($to_email_address) : $contact['email.email'],
        'attachments' => $attachments,
        'tokenContext' => ['contactId' => $contact_id, 'contributionId' => $contribution_id],
      ]);
    } catch (Exception $exception) {
      throw new Exception(E::LONG_NAME . " " . "MessageTemplate exception: $exception");
    }
  }

  public static function createActivity($activity_id, $target_id, $status_id, $year, $card_number, $contribution_id, $title, $description): void
  {
    try {
      if ($activity_id) {
        $params = [
          'status_id' => $status_id,
          'subject' => E::ts($title),
          'location' => $description,
          'Tessera_attivit_.Contributo_collegato' => $contribution_id, // @todo Add in configuration settings
          'Tessera_attivit_.Anno' => $year, // @todo Add in configuration settings
          'Tessera_attivit_.Numero' => $card_number, // @todo Add in configuration settings
        ];
        $results = civicrm_api4('Activity', 'update', [
          'values' => $params,
          'where' => [
            ['id', '=', $activity_id],
          ],
          'checkPermissions' => TRUE,
        ]);
      } else {
        $params = [
          'activity_type_id' => 61, // @todo Add in configuration settings "Membership card sent" activity type
          'subject' => E::ts($title),
          'location' => $description,
          'source_contact_id' => 'user_contact_id',
          'target_contact_id' => $target_id,
          'status_id' => $status_id,
          'Tessera_attivit_.Contributo_collegato' => $contribution_id,
          'Tessera_attivit_.Anno' => $year,
          'Tessera_attivit_.Numero' => $card_number,
        ];
        $results = civicrm_api4('Activity', 'create', [
          'values' => $params,
          'checkPermissions' => TRUE,
        ]);
      }
    } catch (Exception $exception) {
      Civi::log()->debug(E::LONG_NAME . ' ' . "Unable to write activity: $exception");
    }
  }

  public static function createAttachment($membership)
  {
    list($fileName, $pdfContent) = CRM_Membershipcard_Utils_Memberships::generateMemberCard($membership, TRUE);
    $pdf_filename = CRM_Core_Config::singleton()->templateCompileDir . CRM_Utils_File::makeFileName($fileName);
    file_put_contents($pdf_filename, $pdfContent);

    return [ [
      'fullPath' => $pdf_filename,
      'mime_type' => 'application/pdf',
      'cleanName' => $fileName,
    ]];
  }

  /**
   * Implements hook_civicrm_alterMailParams().
   *
   * @param $params
   * @param $context
   *
   * @throws \CRM_Core_Exception
   * @throws \CiviCRM_API3_Exception
   */
  public static function alterMailParams(&$params, $context, $validateForm_template_id = false)
  {
    $template_id = self::getTemplateID($params, $validateForm_template_id);
    if (!isset($template_id) || !isset($params['contactId'])) {
      return;
    }

    $messageTemplateIDs = CRM_Membershipcard_Utils_Config::get('attach_to_templates');

    if (!array_key_exists($template_id, $messageTemplateIDs)) {
      return;
    }

    $membership = CRM_Membershipcard_Utils_Memberships::getContactActiveMembership($params['contactId']);
    if (!$membership) {
      return;
    }

    // add the membership card to attachments
    $params['attachments'] = array_merge($params['attachments'] ?? [], self::createAttachment($membership));
  }

  public static function getTemplateID($params, $validateForm_template_id)
  {
    $template_id = false;
    if (!empty($params['messageTemplateID'])) {
      $template_id = $params['messageTemplateID'];
    } elseif (!empty($validateForm_template_id)) {
      $template_id = $validateForm_template_id;
    } elseif (isset($params['job_id'])) {
      $job_id = $params['job_id'];
      $sql = "SELECT civicrm_mailing.msg_template_id
            FROM civicrm_mailing_job
            INNER JOIN civicrm_mailing ON civicrm_mailing_job.mailing_id = civicrm_mailing.id
            WHERE civicrm_mailing_job.id = %1";
      $sql_params[1] = array($job_id, 'Integer');
      $template_id = CRM_Core_DAO::singleValueQuery($sql, $sql_params);
    } elseif (isset($params['groupName']) && $params['groupName'] == 'msg_tpl_workflow_contribution' && !empty($params['valueName'])) {
      $sql = 'SELECT mt.id as id
            FROM civicrm_msg_template mt
            JOIN civicrm_option_value ov ON workflow_id = ov.id
            JOIN civicrm_option_group og ON ov.option_group_id = og.id
            WHERE og.name = %1 AND ov.name = %2 AND mt.is_default = 1';
      $sql_params = array(1 => array($params['groupName'], 'String'), 2 => array($params['valueName'], 'String'));
      $template_id = CRM_Core_DAO::singleValueQuery($sql, $sql_params);
    }
    return $template_id;
  }
}
