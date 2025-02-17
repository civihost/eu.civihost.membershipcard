<?php

require_once 'CRM/Core/Form.php';

use CRM_Membershipcard_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Membershipcard_Form_SendMembershipCard extends CRM_Core_Form
{
  /**
   * The Membership ID
   *
   * @var int
   */
  protected $_membershipId;

  /**
   * The contact id of the person for whom membership was created based on the id in the url
   * @var int
   */
  public $_contactId;

  /**
   * The year of the membership
   * @var int
   */
  public $_year;


  /**
   * check permissions
   */
  public function preProcess()
  {
    //check for delete
    if (!CRM_Core_Permission::checkActionPermission('CiviMember', CRM_Core_Action::UPDATE)) {
      CRM_Core_Error::fatal(ts('You do not have permission to access this page.'));
    }
    parent::preProcess();
  }

  public function buildQuickForm()
  {
    $this->_membershipId = CRM_Utils_Request::retrieve('id', 'Positive', $this);
    $this->_contactId = CRM_Utils_Request::retrieve('cid', 'Positive', $this);
    $this->_year = CRM_Utils_Request::retrieve('y', 'Positive', $this, FALSE, CRM_Membershipcard_Utils_Memberships::currentYear());

    if (!$this->_contactId && $this->_membershipId) {
      $this->_contactId = civicrm_api3('membership', 'getvalue', array(
        'id' => $this->_membershipId,
        'return' => 'contact_id',
      ));
    }

    //get current contact name.
    $this->assign('currentContactName', CRM_Contact_BAO_Contact::displayName($this->_contactId));

    $this->add('hidden', 'contact_id', $this->_contactId, array('id' => 'contact_id'));
    $this->add('hidden', 'year', $this->_year, array('id' => 'year'));
    //$this->add('hidden', 'membership_id', $this->_membershipId, array('id' => 'membership_id'));
    $this->assign('contactId', $this->_contactId);

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => ts('Invia tessera'),
        'isDefault' => TRUE,
      ),
    ));

    parent::buildQuickForm();
  }

  public function postProcess()
  {
    $values = $this->exportValues();
    // Civi::log()->debug('postProcess', array('values' => $values));

    $results = civicrm_api4('Chapter', 'sendCard', [
      'contact_id' => $values['contact_id'],
      'membership_id' => null,
      'year' => $values['year'],
      'checkPermissions' => FALSE,
    ]);

    if ($results['error']) {
      CRM_Core_Session::setStatus(ts('C\'è stato un errore nell\'invio della tessera.'), ts('Error'), 'error');
    } else {
      CRM_Core_Session::setStatus(ts('Tessera inviata con successo.'), ts('Invio tessera'), 'success');
    }

    parent::postProcess();
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames()
  {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }
}
