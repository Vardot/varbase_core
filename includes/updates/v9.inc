<?php

/**
 * @file
 * Contains varbase_core_update_9###(s) hook updates.
 */

use Vardot\Entity\EntityDefinitionUpdateManager;
use Vardot\Installer\ModuleInstallerFactory;

/**
 * Issue #3248635: Add Reroute Email ~2.0 module.
 *
 * And enable with default config to Varbase Development module.
 */
function varbase_core_update_9001() {

  /** @var \Drupal\update_helper\Updater $updateHelper */
  $updateHelper = \Drupal::service('update_helper.updater');

  // Execute configuration update definitions with logging of success.
  $updateHelper->executeUpdate('varbase_core', 'varbase_core_update_9001');

  // Output logged messages to related channel of update execution.
  return $updateHelper->logger()->output();
}

/**
 * Add the ability to filter by "Author".
 *
 * In administration views (Content and Media).
 */
function varbase_core_update_9002() {

  /** @var \Drupal\update_helper\Updater $updateHelper */
  $updateHelper = \Drupal::service('update_helper.updater');

  // Execute configuration update definitions with logging of success.
  $updateHelper->executeUpdate('varbase_core', 'varbase_core_update_9002');

  // Output logged messages to related channel of update execution.
  return $updateHelper->logger()->output();
}

/**
 * Enable Email Registration.
 */
function varbase_core_optional_update_9003($force = FALSE) {

  /** @var \Drupal\update_helper\Updater $updateHelper */
  $updateHelper = \Drupal::service('update_helper.updater');

  // Execute configuration update definitions with logging of success.
  $updateHelper->executeUpdate('varbase_core', 'varbase_core_optional_update_9003');

  // Output logged messages to related channel of update execution.
  return $updateHelper->logger()->output();
}

/**
 * Issue #3265975: Fix errors on validating Automated Cron.
 *
 * Config synchronization when the module is not enabled yet.
 */
function varbase_core_update_9004() {
  if (!\Drupal::moduleHandler()->moduleExists('automated_cron')) {
    // Delete Automated Cron configs if the module is not enable yet.
    // Which they will be imported again after installing the module.
    \Drupal::configFactory()->getEditable('automated_cron.settings')->delete();
  }
}

/**
 * Issue #3270834: Switch to use the Module Installer Factory.
 *
 * In the Varbase Core module.
 */
function varbase_core_update_90005() {
  // Set the weight of the module after installation of list of modules.
  // To make sure that any hook or event subscriber worker
  // after all used modules.
  ModuleInstallerFactory::setModuleWeightAfterInstallation('varbase_core');
}

/**
 * Issue #3317082: Update Block Class module from ~1.0 to ~2.0.
 *
 * Import default config on update.
 */
function varbase_core_update_90006() {
  if (\Drupal::moduleHandler()->moduleExists('block_class')) {
    $managed_configs = [
      'block_class.settings',
    ];
    ModuleInstallerFactory::importConfigsFromList('varbase_core', $managed_configs, 'config/optional');

    // Entity updates to clear up any mismatched entity and/or field definitions
    // And Fix changes were detected in the entity type and field definitions.
    \Drupal::classResolver()
      ->getInstanceFromDefinition(EntityDefinitionUpdateManager::class)
      ->applyUpdates();
  }
}

/**
 * Issue #3338305: Have a smooth update for Config Ignore module.
 *
 * Which removed the use of Config Filter module using core
 * API instead of Config Filter.
 */
function varbase_core_update_90007() {
  if (\Drupal::moduleHandler()->moduleExists('config_filter')) {
    \Drupal::service('module_installer')->uninstall(['config_filter'], FALSE);
  }
}

/**
 * Issue #3378806: Update Link Attributes widget module from ~2.0.0 to ~2.1.0.
 *
 * Enable the new sub module Link attributes: Menu Link Content integration.
 */
function varbase_core_update_90008() {
  if (!\Drupal::moduleHandler()->moduleExists('link_attributes_menu_link_content')) {
    \Drupal::service('module_installer')->install(['link_attributes_menu_link_content'], FALSE);
  }
}

/**
 * Issue #3399759: Update Config Ignore module from 3.0.0 to 3.0.1 ( ~3.0 )
 *
 * Add mode: simple to old config_ignore.settings, which has no mode yet.
 */
function varbase_core_update_90009() {
  if (\Drupal::moduleHandler()->moduleExists('config_ignore')) {

    $config_factory = \Drupal::configFactory()->getEditable('config_ignore.settings');

    $mode = $config_factory->get('mode');

    if (!isset($mode)) {
      $config_factory->set('mode', 'simple');
    }

    $config_factory->save(TRUE);
  }
}

/**
 * Issue #3399834: Add Allow custom account name to Varbase general settings.
 *
 * To work with Email Registration ~2.0.0
 */
function varbase_core_update_90010() {
  if (\Drupal::moduleHandler()->moduleExists('email_registration')) {

    $config_factory = \Drupal::configFactory()->getEditable('varbase_core.general_settings');

    $allow_custom_account_name = $config_factory->get('allow_custom_account_name');

    if (!isset($allow_custom_account_name)) {
      $config_factory->set('allow_custom_account_name', 1);
    }

    $config_factory->save(TRUE);
  }
}
