<?php

require_once 'membershipcard.civix.php';

$autoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload)) {
  require_once $autoload;
}

use CRM_Membershipcard_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function membershipcard_civicrm_config(&$config): void {
  _membershipcard_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function membershipcard_civicrm_install(): void {
  _membershipcard_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function membershipcard_civicrm_enable(): void {
  _membershipcard_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_summaryActions
 */
function membershipcard_civicrm_summaryActions(&$actions, $contactID)
{
  $currentYear = CRM_Membershipcard_Utils_Memberships::currentYear();
  $years = [];

  $membership = CRM_Membershipcard_Utils_Memberships::getContactActiveMembership($contactID);
  if ($membership) {
    $years[] = $currentYear;
  }

  if ($currentYear > date('Y')) {
    $membership = CRM_Membershipcard_Utils_Memberships::getContactActiveMembership($contactID, null, date('Y'));
    if ($membership) {
      $years[] = date('Y');
    }
  }

  foreach ($years as $y) {
    $actions['otherActions']['download_membercard_' . $y] = [
      'title' => E::ts('Download the %1 membership card', [1 => $y]),
      'weight' => 999,
      'ref' => 'download-member-card',
      'key' => 'download_membercard',
      'href' => CRM_Utils_System::url('civicrm/membercard', 'cid=' . $contactID . '&y=' . $y),
      'class' => 'no-popup',
      'icon' => 'crm-i fa-address-card',
    ];
    $actions['otherActions']['send_membercard' . $y] = [
      'title' => E::ts('Send the %1 membership card', [1 => $y]),
      'weight' => 999,
      'ref' => 'send-member-card',
      'key' => 'send_membercard',
      'href' => CRM_Utils_System::url('civicrm/send-membercard', 'cid=' . $contactID . '&y=' . $y),
      'class' => 'no-popup',
      'icon' => 'crm-i fa-mail-forward',
    ];
  }
}

/**
 * Implements hook_civicrm_pageRun().
 */
function membershipcard_civicrm_pageRun(&$page) {
  $pageName = get_class($page);
  if ($pageName == 'CRM_Contact_Page_View_UserDashBoard') {
    CRM_Membershipcard_Contact_Page_View_UserDashBoard::pageRun($page);
  }
}
