<?php

/**
 * @file
 * Contains varbase_admin_update_10###(s) hook updates.
 */

use Vardot\Entity\EntityDefinitionUpdateManager;
use Vardot\Installer\ModuleInstallerFactory;
use Drupal\Core\Recipe\Recipe;
use Drupal\Core\Recipe\RecipeRunner;

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

/**
 * Issue #3426342: Add Gin Everywhere ~1 module to Varbase Admin.
 *
 * As part of enhancing Editorial UX for taxonomy and term management.
 */
function varbase_admin_update_100002() {
  if (!\Drupal::moduleHandler()->moduleExists('gin_everywhere')) {
    \Drupal::service('module_installer')->install(['gin_everywhere'], FALSE);
  }
}

/**
 * Issue #3426347: Add Taxonomy Manager ~2 module to Varbase Admin.
 *
 * As part of enhancing Editorial UX for taxonomy and term management.
 */
function varbase_admin_update_100003() {
  if (!\Drupal::moduleHandler()->moduleExists('taxonomy_manager')) {
    $module_path = Drupal::service('module_handler')->getModule('varbase_admin')->getPath();
    $recipe = Recipe::createFromDirectory($module_path . '/recipes/updates/varbase_admin_update_100003');
    RecipeRunner::processRecipe($recipe);
  }
}
