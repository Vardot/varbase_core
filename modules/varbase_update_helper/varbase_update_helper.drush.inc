<?php

/**
 * @file
 * Contains drush command for Varbase update helpers.
 */

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
  if (!$update_hook || !$module) {
    return drush_print(dt('Please provide a module name and an update hook. Example: drush varbase-up <module> <update_hook>'));
  }
  $force = drush_get_option('force', FALSE);

  module_load_install($module);
  if (function_exists($update_hook)) {
    call_user_func($update_hook, $force);
  }
  else {
    // @Todo: make this output as an error
    drush_print(dt("Couldn't find an update hook: !update_hook. Please verify the update hook name.", ["!update_hook" => $update_hook]));
  }
}
