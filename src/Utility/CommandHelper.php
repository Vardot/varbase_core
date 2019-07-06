<?php

namespace Drupal\varbase_core\Utility;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

/**
 * Command Helper.
 */
class CommandHelper implements LoggerAwareInterface {

  use LoggerAwareTrait;

  /**
   * Constructs an Command Helper object.
   */
  public function __construct() {

  }

  /**
   * Applying an (optional) update hook (function) from module install file.
   *
   * @param string $module
   *   Drupal module name.
   * @param string $update_hook
   *   Name of update_hook to apply.
   * @param bool $force
   *   Force the update.
   */
  public function varbaseApplyUpdate($module = '', $update_hook = '', $force = FALSE) {
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
