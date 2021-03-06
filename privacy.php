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
  $optionGroupId = _privacy_get_activity_type_option_group_id();
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
 * Function to add config elements for privacy to CiviCRM config
 * 
 * Array $activityTypeNames holds names of activity types that
 * will be added to config
 * Default text for privacy message is added too
 */
function _privacy_set_privacy_config(&$config) {
  $activityTypeNames = array('private_case_information', 'private_information');
  $optionGroupId = _privacy_get_activity_type_option_group_id();
  foreach ($activityTypeNames as $activityTypeName) {
    $params = array(
      'option_group_id' => $optionGroupId,
      'name' => $activityTypeName,
      'return' => 'value'
    );
    try {
      $optionValue = civicrm_api3('OptionValue','Getvalue', $params);
      $config->pumPrivacyActivityTypes[] = $optionValue;
    } catch (CiviCRM_API3_Exception $ex) {
    }
  }
  $config->pumPrivacyText = 'De details van deze activiteit zijn afgeschermd, '
    . 'neem contact op met de expert coordinator voor nadere informatie';
}
/**
 * Function to get the option group id for activity types
 * 
 * @return int $optionGroupId
 * @throws Exception when error in API
 */
function _privacy_get_activity_type_option_group_id() {
  $optionGroupParams = array('name' => 'activity_type', 'return' => 'id');
  try {
    $optionGroupId = civicrm_api3('OptionGroup', 'Getvalue', $optionGroupParams);
  } catch (CiviCRM_API3_Exception $ex) {
  throw new Exception('Could not find an Option Group with name activity_type, '
    . 'error from API OptionGroup Getvalue :'.$ex->getMessage());      
  }
  return $optionGroupId;
}
/**
 * Function to remove options from list for privacy activity types
 * 
 * @param array $activityList
 */
function _privacy_remove_case_activity_options(&$activityList) {
  $listOptions = &$activityList->_options;
  foreach ($listOptions as $key => $listOption) {
    $config = CRM_Core_Config::singleton();
    if (in_array($listOption['attr']['value'], $config->pumPrivacyActivityTypes)) {
      unset($listOptions[$key]);
    }
  }
}
/**
 * Function to handle buildForm for case activity
 */
function _privacy_build_case_activity_form(&$form) {
  $snippet = CRM_Utils_Request::retrieve('snippet', 'Positive');
  if (_privacy_civicrm_has_access()) {
    $form->assign('pumPrivacy', 1);
  } else {
    $form->assign('pumPrivacy', 0);
  }
  if ($snippet != '4') {
    $form->addElement('text', 'pumActivityRedirect', '');
    $form->assign('pumActivityRedirect', 1);
    $session = CRM_Core_Session::singleton();
    $form->assign('doneUrl', $session->readUserContext());
  }
}
/**
 * Function to handle buildForm for activity links
 */
function _privacy_build_activity_links_form(&$form) {
  if (_privacy_civicrm_has_access()) {
    $form->assign('pumPrivacy', 1);
  } else {
    $form->assign('pumPrivacy', 0);
  }
}
/**
 * Function to redirect edit to view for case activity when required
 */
function _privacy_redirect_case_activity_form($form) {
  $activityTypeId = $form->getVar('_activityTypeId');
  $config = CRM_Core_Config::singleton();
  if (in_array($activityTypeId, $config->pumPrivacyActivityTypes)) {
    $pumPrivacy = $form->getElement('pumPrivacy');
    if ($pumPrivacy->_attributes['value'] == 0) {
      $caseId = $form->getVar('_caseId');
      $activityId = $form->getVar('_activityId');
      $viewUrl = CRM_Utils_System::url('civicrm/case/activity/view', 'cid='.$caseId.'&aid='.$activityId.'&type=', true);
      CRM_Utils_System::redirect($viewUrl);
    }
  }
}
/**
 * Function to remove options from select list for activity
 */
function _privacy_remove_activity_options(&$elements) {
  foreach ($elements as &$formElement) {
    if ($formElement->_attributes['name'] == 'activity_type_id' || 
      $formElement->_attributes['name'] == 'followup_activity_type_id') {
      $options = &$formElement->_options;
      foreach ($options as $key => $option) {
        $config = CRM_Core_Config::singleton();
        if (in_array($option['attr']['value'], $config->pumPrivacyActivityTypes)) {
          unset($options[$key]);
        }
      }
    }
  }
}
/**
 * Function to reset default values for details for Activity in View mode
 */
function _privacy_build_activity_view_form(&$form) {
  $config = CRM_Core_Config::singleton();
  $activityTypeId = $form->getVar('_activityTypeId');
  if (in_array($activityTypeId, $config->pumPrivacyActivityTypes)) {
    $defaults['details'] = $config->pumPrivacyText;
    $defaults['activity_details'] = $config->pumPrivacyText;
    $form->setDefaults($defaults);
  }
}
/**
 * Function to save and change the default details for Activity edit
 */
function _privacy_set_details_activity_edit(&$form) {
  $config = CRM_Core_Config::singleton();
  $activityTypeId = $form->getVar('_activityTypeId');
  if (in_array($activityTypeId, $config->pumPrivacyActivityTypes)) {
    $defaultValues = $form->getVar('_defaultValues');
    /*
     * hack with GLOBALS to save details so they can be put back in the pre
     * hook
     */
    $GLOBALS['activity_details'] = $defaultValues['details'];
    $defaults['details'] = $config->pumPrivacyText;
    $defaults['activity_details'] = $config->pumPrivacyText;
    $form->setDefaults($defaults);
    $element = $form->getElement('details');
    $element->freeze();
  }
}
/**
 * Function to re-apply original acitivity details
 */
function _privacy_reapply_activity_details(&$params) {
  $config = CRM_Core_Config::singleton();
  if (isset($params['activity_type_id']) && in_array($params['activity_type_id'], $config->pumPrivacyActivityTypes)) {
    if (isset($GLOBALS['activity_details'])) {
      $params['details'] = $GLOBALS['activity_details'];
      unset($GLOBALS['activity_details']);
    }
  }
}
/**
 * Implementation of hook_civicrm_buildForm
 *
 *pe @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_buildForm
 */
function privacy_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Case_Form_ActivityView' || $formName == 'CRM_Case_Form_Activity') {
    _privacy_build_case_activity_form($form);
  }
  
  if ($formName == 'CRM_Activity_Form_ActivityLinks') {
    _privacy_build_activity_links_form($form);
  }
  
  if ($formName == 'CRM_Case_Form_Activity') {
    _privacy_redirect_case_activity_form($form);
  }
  
  if ($formName == 'CRM_Activity_Form_Activity') {
    if (_privacy_civicrm_has_access() == false) {
      $action = $form->getVar('_action');
      if ($action == CRM_Core_Action::VIEW) {
        _privacy_build_activity_view_form($form);
      } else {
        if ($action == CRM_Core_Action::UPDATE) {
          _privacy_set_details_activity_edit($form);
        }
        $elements = $form->getVar('_elements');
        _privacy_remove_activity_options($elements);
      }
    }
  }
  
  if ($formName == 'CRM_Case_Form_CaseView') {
    if (_privacy_civicrm_has_access() == false) {
      $activityList =  &$form->getElement('activity_type_id');
      _privacy_remove_case_activity_options($activityList);
    }
  }
}
/**
 * Implementation of hook_civicrm_alterTemplateFile
 * 
 */
function privacy_civicrm_alterTemplateFile($formName, &$form, $context, &$tplName) {
  if ($formName === 'CRM_Case_Form_ActivityView') { 
    $tplName = 'PumCaseActivityView.tpl';
  }
}
/**
 * Implementation of hook_civicrm_pre
 */
function privacy_civicrm_pre($op, $objectName, $id, &$params) {
  if ($objectName == 'Activity' && $op == 'edit') {
    if (_privacy_civicrm_has_access() == false) {
      _privacy_reapply_activity_details($params);
    }
  } 
}
/**
 * Implementation of hook_civicrm_conefig
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function privacy_civicrm_config(&$config) {
  /*
   * set config properties for privacy activity types
   */
  _privacy_set_privacy_config($config);
  _privacy_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
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
  /*
   * create activity type 'private information' if not exists
   */  
  _privacy_create_activity_types();
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
