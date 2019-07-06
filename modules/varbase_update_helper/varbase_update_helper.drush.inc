<?php

/**
 * @file
 * Contains drush command for Varbase update helpers.
 */

use Drupal\varbase_core\Utility\CommandHelper;

/**
 * Implements hook_drush_command().
 */
function varbase_update_helper_drush_command() {
  $commands['varbase-apply-update'] = [
    'description' => 'Apply Varbase updates by invoking the related update hooks.',
    'aliases' => ['varbase-up'],
    'arguments' => [
      'module' => 'Module',
      'update_hook' => 'Update hook.',
    ],
    'options' => [
      'force' => FALSE,
    ],
    'examples' => [
      'drush varbase-up <module> <update_hook>' => 'Apply the update <module> <update_hook>',
      'drush varbase-up --force <module> <update_hook>' => 'Force apply the update <module> <update_hook>',
    ],
  ];

  return $commands;
}

/**
 * Drush command logic.
 *
 * The drush_[MODULE_NAME]_[COMMAND_NAME]().
 */
function drush_varbase_update_helper_varbase_apply_update($module = "", $update_hook = "") {
  $commandhelper = _varbase_update_helper_drush_command_helper();
  $force = drush_get_option('force', FALSE);
  $commandhelper->varbaseApplyUpdate($module, $update_hook, $force);
}

/**
 * Returns an instance of the command helper.
 *
 * @return \Drupal\varbase_core\Utility\CommandHelper
 *   An instance of the command helper class.
 */
function _varbase_update_helper_drush_command_helper() {
  $command_helper = new CommandHelper();
  $command_helper->setLogger(\Drupal::logger('varbase_update_helper'));
  return $command_helper;
}
