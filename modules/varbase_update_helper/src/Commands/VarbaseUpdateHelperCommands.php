<?php

namespace Drupal\varbase_update_helper\Commands;

use Drush\Commands\DrushCommands;
use Drupal\varbase_core\Utility\CommandHelper;
use Psr\Log\LoggerInterface;

/**
 * Class VarbaseUpdateHelperCommands
 *
 * define drush commands for varbase_update_helper module
 *
 * @package Drupal\varbase_update_helper\Commands
 */
class VarbaseUpdateHelperCommands extends DrushCommands {

  /**
   * command helper object (inspired by search API module)
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
   * applying an (optional) update hook (function) from module install file
   * Apply Varbase updates by invoking the related update hooks.
   *
   * @param string $module
   * @param string $update_hook
   * @param array $options
   * @option force
   *
   * @command varbase_update_helper:varbase-apply-update
   * @aliases varbase-up
   */
  public function varbase_apply_update ($module = '', $update_hook = '', $options = ['force' => FALSE]) {
    $force = $options['force'];
    $this->commandHelper->varbase_apply_update($module, $update_hook, $force);
  }
}