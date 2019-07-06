<?php

namespace Drupal\varbase_update_helper\Commands;

use Drush\Commands\DrushCommands;
use Drupal\varbase_core\Utility\CommandHelper;
use Psr\Log\LoggerInterface;

/**
 * Class VarbaseUpdateHelperCommands.
 *
 * Define drush commands for varbase_update_helper module.
 *
 * @package Drupal\varbase_update_helper\Commands
 */
class VarbaseUpdateHelperCommands extends DrushCommands {

  /**
   * Command helper object (inspired by search API module)
   *
   * @var \Drupal\varbase_core\Utility\CommandHelper
   */
  protected $commandHelper;

  /**
   * VarbaseUpdateHelperCommands constructor.
   */
  public function __construct() {
    $this->commandHelper = new CommandHelper();
  }

  /**
   * {@inheritdoc}
   */
  public function setLogger(LoggerInterface $logger) {
    parent::setLogger($logger);
    $this->commandHelper->setLogger($logger);
  }

  /**
   * Applying an (optional) update hook (function) from module install file.
   *
   * Apply Varbase updates by invoking the related update hooks.
   *
   * @param string $module
   *   Module name.
   * @param string $update_hook
   *   Update hook.
   * @param array $options
   *   Options.
   *
   * @option force
   *
   * @command varbase_update_helper:varbase-apply-update
   * @aliases varbase-up
   */
  public function varbaseApplyUpdate($module = '', $update_hook = '', array $options = ['force' => FALSE]) {
    $force = $options['force'];
    $this->commandHelper->varbaseApplyUpdate($module, $update_hook, $force);
  }

}
