<?php
  define('CHILD_RULE_ID', 10);

require_once 'aodupechildimport.civix.php';
use CRM_Aodupechildimport_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/ 
 */
function aodupechildimport_civicrm_config(&$config) {
  _aodupechildimport_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_xmlMenu
 */
function aodupechildimport_civicrm_xmlMenu(&$files) {
  _aodupechildimport_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function aodupechildimport_civicrm_install() {
  _aodupechildimport_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function aodupechildimport_civicrm_postInstall() {
  _aodupechildimport_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function aodupechildimport_civicrm_uninstall() {
  _aodupechildimport_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function aodupechildimport_civicrm_enable() {
  _aodupechildimport_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function aodupechildimport_civicrm_disable() {
  _aodupechildimport_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function aodupechildimport_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _aodupechildimport_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
 */
function aodupechildimport_civicrm_managed(&$entities) {
  _aodupechildimport_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_caseTypes
 */
function aodupechildimport_civicrm_caseTypes(&$caseTypes) {
  _aodupechildimport_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules
 */
function aodupechildimport_civicrm_angularModules(&$angularModules) {
  _aodupechildimport_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_alterSettingsFolders
 */
function aodupechildimport_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _aodupechildimport_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function aodupechildimport_civicrm_entityTypes(&$entityTypes) {
  _aodupechildimport_civix_civicrm_entityTypes($entityTypes);
}

/**
 * Implements hook_civicrm_thems().
 */
function aodupechildimport_civicrm_themes(&$themes) {
  _aodupechildimport_civix_civicrm_themes($themes);
}
/**
 * Implements hook_civicrm_pre().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_pre/
 */
function aodupechildimport_civicrm_pre($op, $objectName, $id, &$params) {
  if ($op == 'create' && !empty($params['contact_sub_type']) && $params['contact_sub_type'] == 'Child') {
    // Child contact is being created, apply necessary dedupe rule.
    $dedupeParams = CRM_Dedupe_Finder::formatParams($params, 'Individual');
    $dedupeParams['check_permission'] = 0;
    $dupes = CRM_Dedupe_Finder::dupesByParams($dedupeParams, 'Individual', NULL, [], CHILD_RULE_ID);
    // We take the first match found, and update that.
    $params['contact_id'] = CRM_Utils_Array::value('0', $dupes, NULL);
  }
}

  function aodupechildimport_civicrm_post($op, $objectName, $objectId, &$objectRef) {
    if ($objectName == "Relationship" && $op == 'create') {
      // This is done at the time of creating a new relationship between the child and parent.
      $childRel = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_RelationshipType', 'Child of', 'id', 'name_a_b');
      if ($objectRef->relationship_type_id == $childRel) {
        createSharedAddress($objectRef->contact_id_b, $objectRef->contact_id_a);
      }
    }
    if ($objectName == 'Individual' && $op == 'edit'
      && !empty($objectRef->contact_sub_type) && strpos($objectRef->contact_sub_type, 'Child') !== false) {
      // This is done if the child exists, and is an update. The method above isn't called for existing relationships.
      $childRel = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_RelationshipType', 'Child of', 'id', 'name_a_b');
      $relationships = civicrm_api3('Relationship', 'get', ['contact_id_a' => $objectId, 'relationship_type_id' => $childRel]);
      if (!empty($relationships['values'])) {
        foreach ($relationships['values'] as $relationship) {
          deleteChildAddresses($relationship['contact_id_a']);
          createSharedAddress($relationship['contact_id_b'], $relationship['contact_id_a']);
        }
      }
    }
  }

  function deleteChildAddresses($childId) {
    $addresses = civicrm_api3('Address', 'get', ['contact_id' => $childId]);
    if (!empty($addresses['values'])) {
      foreach ($addresses['values'] as $address) {
        civicrm_api3('Address', 'delete', ['id' => $address['id']]);
      }
    }
  }

  function createSharedAddress($parentId, $childId) {
    $addresses = civicrm_api3('Address', 'get', ['contact_id' => $parentId]);
    if (!empty($addresses['values'])) {
      foreach ($addresses['values'] as $address) {
        civicrm_api3('Address', 'create', ['master_id' => $address['id'], 'contact_id' => $childId]);
      }
    }
  }

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 *
function aodupechildimport_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 *
function aodupechildimport_civicrm_navigationMenu(&$menu) {
  _aodupechildimport_civix_insert_navigation_menu($menu, 'Mailings', array(
    'label' => E::ts('New subliminal message'),
    'name' => 'mailing_subliminal_message',
    'url' => 'civicrm/mailing/subliminal',
    'permission' => 'access CiviMail',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _aodupechildimport_civix_navigationMenu($menu);
} // */
