<?php

namespace Civi\Api4\Action\Membershipcard;

use Civi;
use Civi\Api4\Generic\Result;
use CRM_Membershipcard_ExtensionUtil as E;

final class SendCard extends \Civi\Api4\Generic\AbstractAction
{
  /**
   * Membershipcard::sendCard() API v4
   *
   * These parameters allow you to create a chapter
   *
   * @see \Civi\Api4\Generic\AbstractAction
   *
   * @package Civi\Api4\Action\Membershipcard
   */

  /**
   * Contact ID
   *
   * @var int
   */
  protected $contact_id;

  /**
   * Membership ID
   *
   * @var int
   */
  protected $membership_id = NULL;

  /**
   * Year of contribution
   *
   * @var int
   */
  protected $year = NULL;

  /**
   * Activity ID
   *
   * @var int
   */
  protected $activity_id = NULL;

  public function _run(Result $result): void
  {
    if (!$this->contact_id) {
      throw new \CRM_Core_Exception(ts("The contact id cannot be emtpy"));
    }

    $mailer = new \CRM_Membershipcard_Mailer();
    $error_count = $mailer->sendMembershipCard([
      $this->contact_id => [
        'email' => null,
        'membership_id' => $this->membership_id,
        'year' => $this->year,
        'activity_id' => $this->activity_id,
      ],
    ], TRUE);

    $result[] = [
      'error' => $error_count,
      'status' => $error_count ? E::ts('Errore nell\'invio della tessera') : E::ts('Email della tessera inviata'),
    ];
  }
}
