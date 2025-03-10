<?php

require_once 'membershipcard.civix.php';

$autoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload)) {
  require_once $autoload;
}

use CRM_Membershipcard_ExtensionUtil as E;

$membershipcard_template_id = false;

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
  if (!$contactID) {
    return;
  }

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
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
/* @todo Now settings are in defined in settings.php
function membershipcard_civicrm_navigationMenu(&$menu) {
  _membershipcard_civix_insert_navigation_menu($menu, 'Administer/CiviMember', [
    'label' => E::ts('Membership Card'),
    'name' => 'membershipcard_setting',
    'url' => 'civicrm/admin/setting/membershipcard',
    'permission' => 'administer CiviCRM',
    'operator' => 'OR',
    'separator' => 0,
  ]);
  _membershipcard_civix_navigationMenu($menu);
}
*/

/**
 * Implements hook_civicrm_pageRun().
 */
function membershipcard_civicrm_pageRun(&$page) {
  $pageName = get_class($page);
  if ($pageName == 'CRM_Contact_Page_View_UserDashBoard') {

    if (!CRM_Membershipcard_Utils_Config::get('enable_user_dashboard')) {
      return;
    }

    $contact_id = $page->_contactId;
    $page->assign('membershipcard_download', CRM_Utils_System::url('civicrm/membercard', 'cid=' . $contact_id . '&cs=' . CRM_Contact_BAO_Contact_Utils::generateChecksum($contact_id)));

    $smarty = CRM_Core_Smarty::singleton();
    $dashboardElements = $smarty->get_template_vars()['dashboardElements'];
    $dashboardElements[] = [
      'class' => 'crm-dashboard-membershipcard',
      'sectionTitle' => E::ts('Membership Card'),
      'templatePath' => 'CRM/Membershipcard/Contact/Page/View/UserDashBoard/Membershipcard.tpl',
      'rows' => [],
    ];
    $smarty->assign('dashboardElements', $dashboardElements);
  }
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
function membershipcard_civicrm_alterMailParams(&$params, $context)
{
  global $membershipcard_template_id;
  CRM_Membershipcard_Mailer::alterMailParams($params, $context, $membershipcard_template_id);
}

/**
 * Implements hook_civicrm_validateForm().
 *
 * @param string $formName
 * @param array $fields
 * @param array $files
 * @param CRM_Core_Form $form
 * @param array $errors
 *
 * @see https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_validateForm
 */
function membershipcard_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  global $membershipcard_template_id;
  if ($formName == 'CRM_Contact_Form_Task_Email') {
    if (!empty($fields['template'])) {
      $membershipcard_template_id = $fields['template'];
    }
  }
}