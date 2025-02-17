<?php

require_once 'membershipcard.civix.php';

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
