<?php

/**
 * @file
 * Contains varbase_tour_update_9###(s) hook updates.
 */

/**
 * Issue #3499644: Remove the Tour UI module.
 *
 * As it has officially been merged into Tour ~2.0, Hide tour when empty, Change the machine
 * name id of tours to follow The machine-readable name must contain only
 * lowercase letters, numbers, and underscores.
 */
function varbase_tour_update_90001() {
  $config_factory = \Drupal::service('config.factory');

  // Hide tour when empty and do not display custom labels.
  $tour_settings = $config_factory->getEditable('tour.settings');
  $tour_settings->set('hide_tour_when_empty', TRUE);
  $tour_settings->set('display_custom_labels', FALSE);
  $tour_settings->set('tour_avail_text', '');
  $tour_settings->set('tour_no_avail_text', '');
  $tour_settings->save();

  // Fetch all existing tour configurations.
  $tours = $config_factory->listAll('tour.tour.');
  foreach ($tours as $tour_config_name) {
    // Load the old tour configuration.
    $old_tour_config_factory = $config_factory->getEditable($tour_config_name);
    $tour_config_data = $old_tour_config_factory->get();

    // Process and update tour tips to adhere to naming conventions.
    $old_tour_tips = $old_tour_config_factory->get('tips');

    $new_tour_tips = [];
    foreach ($old_tour_tips as $tour_tip_index => $tour_tip) {
      $new_tour_tip_index = str_replace('-', '_', $tour_tip_index);
      $new_tour_tips[$new_tour_tip_index] = $tour_tip;
      $new_tour_tips[$new_tour_tip_index]['id'] = str_replace('-', '_', $tour_tip['id']);
    }

    // Update tour ID to follow naming conventions.
    $new_tour_config_data = $tour_config_data;
    $new_tour_config_data['id'] = str_replace('-', '_', $tour_config_data['id']);
    $new_tour_config_data['tips'] = $new_tour_tips;

    if ($new_tour_config_data != $tour_config_data) {
      // Save the updated configuration under the new machine-readable name.
      $new_tour_config_name = str_replace('-', '_', $tour_config_name);
      $new_tour_config_factory = $config_factory->getEditable($new_tour_config_name);
      $new_tour_config_factory->setData($new_tour_config_data);
      $new_tour_config_factory->save(TRUE);

      // Delete the old configuration.
      $old_tour_config_factory->delete();
    }
  }
}
