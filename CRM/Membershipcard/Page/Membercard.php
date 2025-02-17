<?php

use CRM_Membershipcard_ExtensionUtil as E;

class CRM_Membershipcard_Page_Membercard extends CRM_Core_Page
{
  public $_contactId = NULL;
  public $_year = NULL;

  public function run()
  {
    $this->_contactId = CRM_Utils_Request::retrieve('cid', 'Positive', $this);
    $this->_year = CRM_Utils_Request::retrieve('y', 'Positive', $this, FALSE, CRM_Membershipcard_Utils_Memberships::currentYear());

    if (!$this->_contactId) {
      // check logged in user permission
      if (!CRM_Core_Permission::check('CiviCRM: access Contact Dashboard')) {
        CRM_Core_Error::statusBounce(ts('You are not authorized to access this page.'));
        return;
      }

      // force current logged
      $userId = CRM_Core_Session::getLoggedInContactID();
      $this->_contactId = $userId;
    }
    $this->assign('contactId', $this->_contactId);

    $membership = CRM_Membershipcard_Utils_Memberships::getContactActiveMembership($this->_contactId, NULL, $this->_year);
    if ($membership) {
      CRM_Membershipcard_Utils_Memberships::generateMemberCard($membership);
    }
  }
}
