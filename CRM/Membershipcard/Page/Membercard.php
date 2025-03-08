<?php

use CRM_Membershipcard_ExtensionUtil as E;

class CRM_Membershipcard_Page_Membercard extends CRM_Core_Page
{
  public $_contactId = NULL;
  public $_year = NULL;

  public function run()
  {
    $this->_contactId = $this->getContactID();
    $this->_year = CRM_Utils_Request::retrieve('y', 'Positive', $this, FALSE, CRM_Membershipcard_Utils_Memberships::currentYear());

    if (!$this->_contactId) {
      CRM_Core_Error::statusBounce(ts('You do not have permission to access this page.'));
      return;
    }

    $this->assign('contactId', $this->_contactId);

    $membership = CRM_Membershipcard_Utils_Memberships::getContactActiveMembership($this->_contactId, NULL, $this->_year);
    if ($membership) {
      CRM_Membershipcard_Utils_Memberships::generateMemberCard($membership);
    }
  }

  protected function getContactID() {
    $contact_id = CRM_Utils_Request::retrieveValue('cid', 'Positive');
    if ($contact_id && CRM_Core_Permission::check('view all contacts')) {
        return $contact_id;
    }

    //check if this is a checksum authentication
    $userChecksum = CRM_Utils_Request::retrieveValue('cs', 'String');
    if ($userChecksum) {
      //check for anonymous user.
      $validUser = CRM_Contact_BAO_Contact_Utils::validChecksum($contact_id, $userChecksum);
      if ($validUser) {
        return $contact_id;
      }
    }

    // check if the user is registered and we have a contact ID
    return CRM_Core_Session::getLoggedInContactID();
  }
}
