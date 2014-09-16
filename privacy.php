<?php

require_once 'privacy.civix.php';

/**
 * Returns whether the current user has access to see the privacy activity
 * 
 * @return boolean
 */
function _privacy_civicrm_has_access() {
  if (function_exists('user_access')) {
    if (user_access('CiviCRM_Privacy_Activity')) {
      return true;
    }
  }
  return false;
}
/**
 * Function to create activity types for Private information 
 */
function _privacy_create_activity_types() {
  $optionNames['private_case_information'] = array('component_id' => 7, 'label' => 'Private case information');
  $optionNames['private_information'] = array('component_id' => null, 'label' => 'Private information');
  foreach ($optionNames as $optionName => $optionValue) {
    if (_privacy_activity_type_exists($optionName) == false) {
      $params = array(
        'name' => $optionName,
        'label' => $optionValue['label'],
        'component_id' => $optionValue['component_id'],
        'weight' => 1,
        'is_active' => 1,
        'is_reserved' => 1
      );
      civicrm_api3('ActivityType', 'Create', $params);
    }
  }  
}
/**
 * Function to check if activity type exists
 */
function _privacy_activity_type_exists($optionName) {
  $optionGroupParams = array('name' => 'activity_type', 'return' => 'id');
  try {
    $optionGroupId = civicrm_api3('OptionGroup', 'Getvalue', $optionGroupParams);
  } catch (CiviCRM_API3_Exception $ex) {
    throw new Exception('Could not find an Option Group with name activity_type, '
      . 'error from API OptionGroup Getvalue :'.$ex->getMessage());
  }
  $params = array(
    'option_group_id' => $optionGroupId,
    'name' => $optionName
  );
  $count = civicrm_api3('OptionValue', 'Getcount', $params);
  if ($count >= 1) {
    return true;
  } else {
    return false;
  }
}
/**
 * Implementation of hook_civicrm_buildForm
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_buildForm
 */
function privacy_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Activity_Form_Activity') {
    $defaults['details'] = 'Test Erik';
    $form->setDefaults($defaults);
    //CRM_Core_Error::debug('form', $form);
    //exit();
  }
}

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function privacy_civicrm_config(&$config) {
  _privacy_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function privacy_civicrm_xmlMenu(&$files) {
  _privacy_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function privacy_civicrm_install() {
  /*
   * create activity type 'private information' if not exists
   */  
  _privacy_create_activity_types();
  return _privacy_civix_civicrm_install();
}
/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function privacy_civicrm_uninstall() {
  return _privacy_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function privacy_civicrm_enable() {
  return _privacy_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function privacy_civicrm_disable() {
  return _privacy_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function privacy_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _privacy_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function privacy_civicrm_managed(&$entities) {
  return _privacy_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function privacy_civicrm_caseTypes(&$caseTypes) {
  _privacy_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function privacy_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _privacy_civix_civicrm_alterSettingsFolders($metaDataFolders);
}
