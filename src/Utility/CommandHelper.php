<?php

namespace Drupal\varbase_core\Utility;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class CommandHelper implements LoggerAwareInterface {

  use LoggerAwareTrait;

  public function __construct() {

  }

  /**
   * applying an (optional) update hook (function) from module install file
   *
   * @param string $module - drupal module name
   * @param string $update_hook - name of update_hook to apply
   * @param bool $force - force the update
   */
  public function varbase_apply_update($module = '', $update_hook = '', $force = FALSE) {
    if (!$update_hook || !$module) {
      $this->logger->info(dt('Please provide a module name and an update hook. Example: drush varbase-up <module> <update_hook>'));
      return;
    }

    module_load_install($module);
    if (function_exists($update_hook)) {
      call_user_func($update_hook, $force);
    }
    else {
      $this->logger->error(dt("Couldn't find an update hook: !update_hook. Please verify the update hook name.", ["!update_hook" => $update_hook]));
    }
  }

}