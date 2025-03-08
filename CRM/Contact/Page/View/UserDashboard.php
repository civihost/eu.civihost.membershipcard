<?php

use CRM_Membershipcard_ExtensionUtil as E;

class CRM_Membershipcard_Contact_Page_View_UserDashBoard {
  /**
   * @see emembercard_civicrm_pageRun()
   */
  public static function pageRun(&$page) {
    //if (!Civi::settings()->get('emembercard_contact_dashboard')) {
    //  return;
    //}
    $contact_id = $page->_contactId;
    $page->assign('membershipcard_download', CRM_Utils_System::url('civicrm/membercard', 'cid=' . $contact_id . '&cs=' . CRM_Contact_BAO_Contact_Utils::generateChecksum($contact_id)));

    self::addElement([
      'class' => 'crm-dashboard-membershipcard',
      'sectionTitle' => E::ts('Membership Card'),
      'templatePath' => 'CRM/Membershipcard/Contact/Page/View/UserDashBoard/Membershipcard.tpl',
      'rows' => [],
    ]);
  }
  /**
   *
   */
  private static function addElement($element) {
    $smarty = CRM_Core_Smarty::singleton();
    $dashboardElements = $smarty->get_template_vars()['dashboardElements'];
    $dashboardElements[] = $element;
    $smarty->assign('dashboardElements', $dashboardElements);
  }
}