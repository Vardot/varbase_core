<?php

/**
 * @file
 * Contains varbase_admin_update_10###(s) hook updates.
 */

use Vardot\Entity\EntityDefinitionUpdateManager;
use Vardot\Installer\ModuleInstallerFactory;

/**
 * Issue #3398817: Add Views Date Filter ~1.0.0 module.
 *
 * Enable it and change default configs for Date Filters.
 */
function varbase_admin_update_100001() {
  if (!\Drupal::moduleHandler()->moduleExists('date_filter')) {
    \Drupal::service('module_installer')->install(['date_filter'], FALSE);

    $managed_configs = [
      'views.view.content',
      'views.view.user_admin_people',
    ];
    ModuleInstallerFactory::importConfigsFromList('varbase_admin', $managed_configs, 'config/optional');

    // Entity updates to clear up any mismatched entity and/or field definitions
    // And Fix changes were detected in the entity type and field definitions.
    \Drupal::classResolver()
      ->getInstanceFromDefinition(EntityDefinitionUpdateManager::class)
      ->applyUpdates();
  }
}
