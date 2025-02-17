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

        list($fileName, $pdfContent) = CRM_Membershipcard_Utils_Memberships::generateMemberCard($membership, TRUE);
        $pdf_filename = CRM_Core_Config::singleton()->templateCompileDir . CRM_Utils_File::makeFileName($fileName);
        file_put_contents($pdf_filename, $pdfContent);

        $attachments = [
          [
            'fullPath' => $pdf_filename,
            'mime_type' => 'application/pdf',
            'cleanName' => $fileName,
          ],
        ];

        $this->sendMail($contact_id, $this->email_address_from, $contact_info['email'] ?? null, $this->card_template_id, $attachments, $membership['contribution_id']);
        if ($write_activity) {
          $this->createActivity(
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
          $this->createActivity(
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

  private function createActivity($activity_id, $target_id, $status_id, $year, $card_number, $contribution_id, $title, $description): void
  {
    try {
      if ($activity_id) {
        $params = [
          'status_id' => $status_id,
          'subject' => E::ts($title),
          'location' => $description,
          'Tessera_attivit_.Contributo_collegato' => $contribution_id,
          'Tessera_attivit_.Anno' => $year,
          'Tessera_attivit_.Numero' => $card_number,
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
          'activity_type_id' => 61, // = invio tessera
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
}
