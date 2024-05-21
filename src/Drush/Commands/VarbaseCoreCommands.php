<?php

namespace Drupal\varbase_core\Drush\Commands;

use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

use Vardot\Installer\ModuleInstallerFactory;
use Vardot\Entity\EntityDefinitionUpdateManager;
use Drupal\Core\File\FileSystemInterface;

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
   * Entity updates to clear up any mismatched entity and/or field definitions.
   *
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

  /**
   * Update composer.json replace any merge_request patch to local patch.
   */
  #[CLI\Command(name: 'varbase:composer-clean-up', aliases: ['var-ccu'])]
  #[CLI\Usage(name: 'varbase:composer-clean-up', description: 'Detect any merge request patch and download it to local and update composer.json file.')]
  public function mergeRequestPatchesCleanup() {
    $root_directory = $this->getConfig()->get('runtime.project');
    $json = file_get_contents($root_directory . '/composer.json');

    $composer = json_decode($json, true);
    $pattern = '/Issue #(\d+)/';
    foreach ($composer['extra']['patches'] as $key => &$value) {
      foreach ($value as $k => $v) {
        if (str_contains($v, 'merge_requests')) {
          try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $v);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
            $patch = curl_exec($ch);
            curl_close($ch);
          }
          catch (\Exception $e) {
            \Drupal::logger('Varbase')->info("Unable to retrieve patch $V");
          }

          preg_match($pattern, $k, $matches);

          if (!empty($matches[1])) {
            $issue_id = $matches[1];

            $directory = $root_directory . "/patches/";
            /** @var \Drupal\Core\File\FileSystemInterface $file_system */
            $file_system = \Drupal::service('file_system');
            $file_system->prepareDirectory($directory, FileSystemInterface:: CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);

            try {
              $patch_file = fopen($root_directory . "/patches/$issue_id.patch", "w");
              fwrite($patch_file, $patch);
              fclose($patch_file);
              $value[$k] = "./patches/$issue_id.patch";
            }
            catch (\Exception $e) {
              \Drupal::logger('Varbase')->info("Unable to save patch file $issue_id.patch");
            }
          }
          else {
            \Drupal::logger('Varbase')->info("Unable to retrieve the issue ID from \"$k\" from $key module patches list.");
          }
        }
      }
    }

    try {
      file_put_contents($root_directory . '/composer.json', json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
    catch (\Exception $e) {
      \Drupal::logger('Varbase')->info("Unable to save composer.json file");
    }
  }

}
