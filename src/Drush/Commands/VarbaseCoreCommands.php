<?php

namespace Drupal\varbase_core\Drush\Commands;

use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

use Vardot\Installer\ModuleInstallerFactory;
use Vardot\Entity\EntityDefinitionUpdateManager;

/**
 * A Drush command file for Varbase Core.
 */
final class VarbaseCoreCommands extends DrushCommands {

  /**
   * Constructs a VarbaseCoreCommands object.
   */
  public function __construct() {
    parent::__construct();
  }

  /**
   * Remove non-existent permissions, to be used for upgrades with missing static and dynamic permissions.
   */
  #[CLI\Command(name: 'varbase:remove-non-existent-permissions', aliases: ['rnep'])]
  #[CLI\Usage(name: 'varbase:remove-non-existent-permissions', description: 'Remove non-existent permissions, to be used for upgrades with missing static and dynamic permissions')]
  public function removeNoneExistentPermissions() {

    // Remove non-existent permissions Testing uninstall hook.
    $this->logger->info(ModuleInstallerFactory::removeNoneExistentPermissions());

    $this->logger()->success(dt('Removed non-existent permissions.'));
  }

  /**
   * Entity updates to clear up any mismatched entity and/or field definitions
   * Fix changes were detected in the entity type and field definitions.
   */
  #[CLI\Command(name: 'varbase:entity-update', aliases: ['edupdb'])]
  #[CLI\Usage(name: 'varbase:entity-update', description: 'Entity updates to clear up any mismatched entity and/or field definitions. Fix changes were detected in the entity type and field definitions.')]
  public function applyUpdatesWithEntityDefinitionUpdateManager() {

    // Entity updates to clear up any mismatched entity and/or field definitions
    // And Fix changes were detected in the entity type and field definitions.
    $this->logger->info(\Drupal::classResolver()->getInstanceFromDefinition(EntityDefinitionUpdateManager::class)->applyUpdates());

    $this->logger()->success(dt('Applied Entity updates for mismatched entity and/or field definitions'));
  }

}
<?php

namespace Drupal\varbase_core\Drush\Commands;

use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

use Vardot\Installer\ModuleInstallerFactory;
use Vardot\Entity\EntityDefinitionUpdateManager;

/**
 * A Drush command file for Varbase Core.
 */
final class VarbaseCoreCommands extends DrushCommands {

  /**
   * Constructs a VarbaseCoreCommands object.
   */
  public function __construct() {
    parent::__construct();
  }

  /**
   * Remove non-existent permissions, to be used for upgrades with missing static and dynamic permissions.
   */
  #[CLI\Command(name: 'varbase:remove-non-existent-permissions', aliases: ['rnep'])]
  #[CLI\Usage(name: 'varbase:remove-non-existent-permissions', description: 'Remove non-existent permissions, to be used for upgrades with missing static and dynamic permissions')]
  public function removeNoneExistentPermissions() {
    try {
      // Remove non-existent permissions Testing uninstall hook.
      $outoutMessage = ModuleInstallerFactory::removeNoneExistentPermissions();

      if (isset($outoutMessage) && is_string($outoutMessage)) {
        $this->logger->info($outoutMessage);
      }

      $this->logger()->success(dt('Removed non-existent permissions.'));
    }
    catch (\Exception $e) {
      \Drupal::logger('Varbase')->critical('Error while drush varbase:remove-non-existent-permissions. !code !exception', [
        '!code' => $e->getCode(),
        '!exception' => $e->getMessage(),
      ]);
    }
  }

  /**
   * Entity updates to clear up any mismatched entity and/or field definitions
   * Fix changes were detected in the entity type and field definitions.
   */
  #[CLI\Command(name: 'varbase:entity-update', aliases: ['edupdb'])]
  #[CLI\Usage(name: 'varbase:entity-update', description: 'Entity updates to clear up any mismatched entity and/or field definitions. Fix changes were detected in the entity type and field definitions.')]
  public function applyUpdatesWithEntityDefinitionUpdateManager() {
    try {
      // Entity updates to clear up any mismatched entity and/or field definitions
      // And Fix changes were detected in the entity type and field definitions.
      $outoutMessage = \Drupal::classResolver()->getInstanceFromDefinition(EntityDefinitionUpdateManager::class)->applyUpdates();

      if (isset($outoutMessage) && is_string($outoutMessage)) {
        $this->logger->info($outoutMessage);
      }

      $this->logger()->success(dt('Applied Entity updates for mismatched entity and/or field definitions'));
    }
    catch (\Exception $e) {
      \Drupal::logger('Varbase')->critical('Error while drush varbase:entity-update. !code !exception', [
        '!code' => $e->getCode(),
        '!exception' => $e->getMessage(),
      ]);
    }
  }

}
