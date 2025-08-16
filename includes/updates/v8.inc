<?php

/**
 * @file
 * Contains varbase_core_update_8###(s) hook updates.
 */

use Symfony\Component\Yaml\Yaml;
use Drupal\Core\Config\InstallStorage;
use Drupal\file\Entity\File;
use Drupal\embed\Entity\EmbedButton;
use Vardot\Installer\ModuleInstallerFactory;
use Drupal\Core\File\FileSystemInterface;

/**
 * Enable [Varbase Update Helper] module.
 */
function varbase_core_update_8600() {

}

/**
 * Enhance the usability of admin views (Content, Media, and People).
 */
function varbase_core_optional_update_8601($force = FALSE) {

}

/**
 * Enable Blazy loading for most media sizes (Image, Video, Remote Video).
 */
function varbase_core_optional_update_8602($force = FALSE) {

}

/**
 * Enable Varbase Email.
 */
function varbase_core_optional_update_8603($force = FALSE) {

}

/**
 * Enable colored length indicator for Varbase Basic page.
 */
function varbase_core_optional_update_8604($force = FALSE) {

}

/**
 * Enable colored length indicator for Varbase Landing page.
 */
function varbase_core_optional_update_8605($force = FALSE) {

}

/**
 * Enable colored length indicator for Varbase Hero Slider.
 */
function varbase_core_optional_update_8606($force = FALSE) {

}

/**
 * Enable colored length indicator for Varbase Blog.
 */
function varbase_core_optional_update_8607($force = FALSE) {

}

/**
 * Change and shift Varbase Media responsive image styles to work with.
 *
 * The mapping of Bootstrap 3 breakpoints into Bootstrap 4 breakpoints.
 */
function varbase_core_update_8608() {

}

/**
 * Rename config updates in Varbase Update Helper to match with new.
 *
 * Update ids standard naming conventions.
 */
function varbase_core_update_8609() {

  // Mapping old update ids naming conventions.
  $map_old_to_new_update_ids = [
    'varbase_core:varbase_admin_vbo_8601' => 'varbase_core:varbase_core_optional_update_8601',
    'varbase_core:varbase_media_blazy_8602' => 'varbase_core:varbase_core_optional_update_8602',
    'varbase_core:varbase_email_enable_8603' => 'varbase_core:varbase_core_optional_update_8603',
    'varbase_core:varbase_page_li_8604' => 'varbase_core:varbase_core_optional_update_8604',
    'varbase_core:varbase_landing_li_8605' => 'varbase_core:varbase_core_optional_update_8605',
    'varbase_core:varbase_heroslider_media_li_8606' => 'varbase_core:varbase_core_optional_update_8606',
    'varbase_core:varbase_blog_li_8607' => 'varbase_core:varbase_core_optional_update_8607',
    'varbase_core:varbase_media_bs3_to_bs4_8608' => 'varbase_core:varbase_core_update_8608',
  ];

  // Check the old update ids and change them with new update ids.
  foreach ($map_old_to_new_update_ids as $old_update_id => $new_update_id) {

    // If we do have the old update id in the system.
    if ($old_update = Update::load($old_update_id)) {

      // Create the new update id if it was not in the system.
      $new_update = Update::load($new_update_id);
      if (isset($new_update) && !$new_update) {
        Update::create(
          [
            'id' => $new_update_id,
            'successful_by_hook' => $old_update->get('successful_by_hook'),
          ]
        )->save();
      }

      // Delete old update id.
      $old_update->delete();

    }
  }
}

/**
 * Fix regression of responsive image styles when updating to.
 *
 * Bootstrap 4 breakpoints.
 */
function varbase_core_update_8610() {

}

/**
 * Add [Gallery] media type.
 */
function varbase_core_optional_update_8701() {

}

/**
 * Revamp Varbase Entity Browsers with multiple media selection behaviors.
 */
function varbase_core_optional_update_8702() {

}

/**
 * Remove language button from the Rich Editor text format.
 */
function varbase_core_update_8703() {

}

/**
 * Issue #3085537: Add [Token Filter] module 8.1.1 ( ~1.0 )
 */
function varbase_core_update_8704() {

}

/**
 * Enable Script Manager module.
 */
function varbase_core_optional_update_8705($force = FALSE) {

}

/**
 * Use blurred image loading effect for Lazy-loaded images.
 */
function varbase_core_optional_update_8706($force = FALSE) {

}

/**
 * Issue #3175756: Fix deprecated function getCurrentUserId replaced.
 *
 * By getDefaultEntityOwner for all content types and media types.
 */
function varbase_core_update_8707() {
  $config_factory = \Drupal::service('config.factory');

  // Update for all content types.
  $node_uid_configs = $config_factory->listAll('core.base_field_override.node.');
  foreach ($node_uid_configs as $node_uid_config) {
    if (substr($node_uid_config, -4) === ".uid") {

      $node_uid_config_factory = $config_factory->getEditable($node_uid_config);
      $default_value_callback = $node_uid_config_factory->get('default_value_callback');

      if (isset($default_value_callback)
        && !empty($default_value_callback)
        && $default_value_callback === 'Drupal\node\Entity\Node::getCurrentUserId') {

        $node_uid_config_factory->set('default_value_callback', 'Drupal\node\Entity\Node::getDefaultEntityOwner')->save(TRUE);
      }
    }
  }

  // Update for all media types.
  $media_uid_configs = $config_factory->listAll('core.base_field_override.media.');
  foreach ($media_uid_configs as $media_uid_config) {
    if (substr($media_uid_config, -4) === ".uid") {

      $media_uid_config_factory = $config_factory->getEditable($media_uid_config);
      $default_value_callback = $media_uid_config_factory->get('default_value_callback');

      if (isset($default_value_callback)
        && !empty($default_value_callback)
        && $default_value_callback === 'Drupal\media\Entity\Media::getCurrentUserId') {

        $media_uid_config_factory->set('default_value_callback', 'Drupal\media\Entity\Media::getDefaultEntityOwner')->save(TRUE);
      }
    }
  }

}
