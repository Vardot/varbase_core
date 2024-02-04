<?php

/**
 * @file
 * Contains varbase_page_update_10###(s) hook updates.
 */

use Vardot\Installer\ModuleInstallerFactory;

/**
 * Issue #3419075: Fix fatal error when editing settings for Rabbit Hole.
 *
 * Configurations over entity types.
 */
function varbase_page_update_100001() {
  $varbase_page_config = [
    'rabbit_hole.behavior_settings.node.page',
  ];

  ModuleInstallerFactory::importConfigsFromList('varbase_page', $varbase_page_config, 'config/optional');
}
