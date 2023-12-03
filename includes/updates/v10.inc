<?php

/**
 * @file
 * Contains varbase_core_update_10###(s) hook updates.
 */

/**
 * Issue #3399650: Add Config ignore for Import/Export for Dynamic Responsive Image (drimage).
 *
 * Generated image styles.
 */
function varbase_core_update_100001() {
  if (\Drupal::moduleHandler()->moduleExists('drimage')
    && \Drupal::moduleHandler()->moduleExists('config_ignore')) {

    $config_factory = \Drupal::configFactory()->getEditable('config_ignore.settings');

    $mode = $config_factory->get('mode');

    if (!isset($mode)) {
      $config_factory->set('mode', 'simple');
    }

    $ignored_config_entities = $config_factory->get('ignored_config_entities');

    if (isset($ignored_config_entities)) {
      $ignored_config_entities[] = 'image.style.drimage_*';
      $config_factory->set('ignored_config_entities', $ignored_config_entities);
    }

    $config_factory->save(TRUE);
  }
}
