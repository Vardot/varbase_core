<?php

/**
 * @file
 * Contains varbase_page_update_9###(s) hook updates.
 */

use Vardot\Installer\ModuleInstallerFactory;

/**
 * Issue #3270834: Switch to use the Module Installer Factory.
 *
 * In the Varbase Page module.
 */
function varbase_page_update_90001() {
  // Set the weight of the module after installation
  // of list of modules. To make sure that any hook or event subscriber
  // worker after all used modules.
  ModuleInstallerFactory::setModuleWeightAfterInstallation('varbase_page');
}

/**
 * Issue #3301361: Change node_type to entity_bundle.node for default pathauto.
 *
 * Pattern configs for the Basic Page content type.
 */
function varbase_page_update_90002() {
  // Having a hook update with a copy of pathauto_update_8108()
  // https://git.drupalcode.org/project/pathauto/-/blob/8.x-1.10/pathauto.install#L304
  // Based on the following comment:
  // https://www.drupal.org/project/pathauto/issues/2925169#comment-14636138
  // Load all pattern configuration entities.
  foreach (\Drupal::configFactory()->listAll('pathauto.pattern.') as $pattern_config_name) {
    $pattern_config = \Drupal::configFactory()->getEditable($pattern_config_name);

    // Loop patterns and swap the node_type plugin by the entity_bundle:node
    // plugin.
    if ($pattern_config->get('type') === 'canonical_entities:node') {
      $selection_criteria = $pattern_config->get('selection_criteria');
      foreach ($selection_criteria as $uuid => $condition) {
        if ($condition['id'] === 'node_type') {
          $pattern_config->set("selection_criteria.$uuid.id", 'entity_bundle:node');
          $pattern_config->save();
          break;
        }
      }
    }
  }
}

/**
 * Issue #3419075: Fix fatal error when editing settings for Rabbit Hole.
 *
 * Configurations over entity types.
 */
function varbase_page_update_90003() {
  $varbase_page_config = [
    'rabbit_hole.behavior_settings.node.page',
  ];

  ModuleInstallerFactory::importConfigsFromList('varbase_page', $varbase_page_config, 'config/optional');
}
