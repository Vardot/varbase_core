<?php

/**
 * @file
 * Contains varbase_webform_update_8###(s) hook updates.
 */

/**
 * Enable [Webform Analysis] module.
 */
function varbase_webform_update_8601() {

}

/**
 * Enable [Webform Views Integration] module.
 */
function varbase_webform_update_8602() {
  if (!\Drupal::moduleHandler()->moduleExists('webform_views')) {
    \Drupal::service('module_installer')->install(['webform_views'], TRUE);
  }
}
