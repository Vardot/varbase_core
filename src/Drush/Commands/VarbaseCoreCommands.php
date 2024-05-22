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
   * Detect any merge request patch and download it to the local patches folder with a timestamp date and update the root composer.json file to use the timestamped local patch file.
   */
  #[CLI\Command(name: 'varbase:composer:cleanup:patches', aliases: ['var-ccup'])]
  #[CLI\Usage(name: 'varbase:composer:cleanup:patches', description: 'Detect any merge request patch and download it to the local patches folder with a timestamp date and update the root composer.json file to use the timestamped local patch file.')]
  public function mergeRequestPatchesCleanup() {
    $root_directory = $this->getConfig()->get('runtime.project');
    $root_composer_json = file_get_contents($root_directory . '/composer.json');

    $not_found_merge_request_patches = FALSE;

    $root_composer = json_decode($root_composer_json, TRUE);

    foreach ($root_composer['extra']['patches'] as $package_name => &$package_patches) {
      foreach ($package_patches as $package_patch_title => $package_patch_remote_link) {
        if (str_contains($package_patch_remote_link, "/-/merge_requests/")) {
          $not_found_merge_request_patches = TRUE;

          try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $package_patch_remote_link);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
            $patch = curl_exec($ch);
            curl_close($ch);
          }
          catch (\Exception $e) {
            \Drupal::logger('Varbase')->info("Unable to retrieve patch $package_patch_remote_link");
            $this->output()->writeln(dt("Unable to retrieve patch $package_patch_remote_link"));
          }

          // Get the processed package name.
          $processed_package_name = '';
          if ($package_name == 'drupal/core') {
            $processed_package_name = 'drupal-core--';
          }
          else {
            $processed_package_name = str_replace('drupal/', '', $package_name) . '--';
          }

          // The date of fetching or cleaning MR patches to local patches.
          $fetch_date = date('Y-m-d') . "--";

          // Get the issue id from the title of the patch. If it was listed.
          preg_match('/#(\d+)/', $package_patch_title, $issue_id_matches);
          $issue_id = '';
          if (isset($issue_id_matches[1])) {
            $issue_id = (string) $issue_id_matches[1] . "--";
          }
          else {
            \Drupal::logger('Varbase')->info("Unable to retrieve the issue ID from \"$package_patch_title\" from $package_name package patches list.");
            $this->output()->writeln(dt("Unable to retrieve the issue ID from \"$package_patch_title\" from $package_name package patches list."));
          }

          // Get the merge request id.
          $mr_id = "mr";
          $package_mr_patch = explode("/-/merge_requests/", $package_patch_remote_link);
          preg_match('/(\d+)/', $package_mr_patch[1], $mr_id_matches);
          if (isset($mr_id_matches[0])) {
            $mr_id = "mr-" . (string) $mr_id_matches[0];
          }
          else {
            \Drupal::logger('Varbase')->info("Unable to retrieve the merge request ID from \"$package_patch_remote_link\" for $package_name package.");
            $this->output()->writeln(dt("Unable to retrieve the merge request ID from \"$package_patch_remote_link\" for $package_name package."));
          }

          $patch_file_name = $processed_package_name . $fetch_date . $issue_id . $mr_id . ".patch";

          $patches_directory = $root_directory . "/patches/";
          /** @var \Drupal\Core\File\FileSystemInterface $file_system */
          $file_system = \Drupal::service('file_system');
          $file_system->prepareDirectory($patches_directory, FileSystemInterface:: CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);

          try {
            $full_patch_file_name = $patches_directory . "/" . $patch_file_name;

            $patch_file = fopen($full_patch_file_name, "w");
            fwrite($patch_file, $patch);
            fclose($patch_file);
            $package_patches[$package_patch_title] = "./patches/" . $patch_file_name;

            \Drupal::logger('Varbase')->info("Processed the patch for \"$package_patch_title\" for the $package_name package. <br /> From: $package_patch_remote_link <br /> To: ./patches/$patch_file_name");
            $this->output()->writeln(dt("Processed the patch for \"$package_patch_title\" for the $package_name package"));
            $this->output()->writeln(dt("From: $package_patch_remote_link"));
            $this->output()->writeln(dt("To: ./patches/$patch_file_name"));
          }
          catch (\Exception $e) {
            \Drupal::logger('Varbase')->info("Unable to save patch file $patch_file_name");
            $this->output()->writeln(dt("Unable to save patch file $patch_file_name"));
          }

          $this->output()->writeln(dt("--------------------------------------------"));
        }
      }
    }

    if ($not_found_merge_request_patches) {
      try {
        file_put_contents($root_directory . '/composer.json', json_encode($root_composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
      }
      catch (\Exception $e) {
        \Drupal::logger('Varbase')->info("Unable to save composer.json file");
        $this->output()->writeln(dt("Unable to save composer.json file"));
      }

      $this->output()->writeln(dt("Competed the composer clean up of merge request patches in the root composer.json file."));
    }
    else {
      $this->output()->writeln(dt("No merge request patches were found in the root composer.json file."));
    }

  }

  /**
   * Detect any merge request patch and download it to the local patches folder with a timestamp date and update patches-file json file to use the timestamped local patch file.
   */
  #[CLI\Command(name: 'varbase:composer:cleanup:patches-file', aliases: ['var-ccupf'])]
  #[CLI\Usage(name: 'varbase:composer:cleanup:patches-file', description: 'Detect any merge request patch and download it to the local patches folder with a timestamp date and update patches-file json file to use the timestamped local patch file.')]
  public function mergeRequestPatchesFileCleanup() {
    $root_directory = $this->getConfig()->get('runtime.project');

    $root_composer_json = file_get_contents($root_directory . '/composer.json');

    $root_composer = json_decode($root_composer_json, TRUE);

    if (isset($root_composer['extra']['patches-file'])) {
      $patches_file_json = file_get_contents($root_directory . '/' . $root_composer['extra']['patches-file']);

      $not_found_merge_request_patches = FALSE;

      $patches_file_composer = json_decode($patches_file_json, TRUE);

      foreach ($patches_file_composer['patches'] as $package_name => &$package_patches) {
        foreach ($package_patches as $package_patch_title => $package_patch_remote_link) {
          if (str_contains($package_patch_remote_link, "/-/merge_requests/")) {
            $not_found_merge_request_patches = TRUE;

            try {
              $ch = curl_init();
              curl_setopt($ch, CURLOPT_URL, $package_patch_remote_link);
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
              curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
              $patch = curl_exec($ch);
              curl_close($ch);
            }
            catch (\Exception $e) {
              \Drupal::logger('Varbase')->info("Unable to retrieve patch $package_patch_remote_link");
              $this->output()->writeln(dt("Unable to retrieve patch $package_patch_remote_link"));
            }

            // Get the processed package name.
            $processed_package_name = '';
            if ($package_name == 'drupal/core') {
              $processed_package_name = 'drupal-core--';
            }
            else {
              $processed_package_name = str_replace('drupal/', '', $package_name) . '--';
            }

            // The date of fetching or cleaning MR patches to local patches.
            $fetch_date = date('Y-m-d') . "--";

            // Get the issue id from the title of the patch. If it was listed.
            preg_match('/#(\d+)/', $package_patch_title, $issue_id_matches);
            $issue_id = '';
            if (isset($issue_id_matches[1])) {
              $issue_id = (string) $issue_id_matches[1] . "--";
            }
            else {
              \Drupal::logger('Varbase')->info("Unable to retrieve the issue ID from \"$package_patch_title\" from $package_name package patches list.");
              $this->output()->writeln(dt("Unable to retrieve the issue ID from \"$package_patch_title\" from $package_name package patches list."));
            }

            // Get the merge request id.
            $mr_id = "mr";
            $package_mr_patch = explode("/-/merge_requests/", $package_patch_remote_link);
            preg_match('/(\d+)/', $package_mr_patch[1], $mr_id_matches);
            if (isset($mr_id_matches[0])) {
              $mr_id = "mr-" . (string) $mr_id_matches[0];
            }
            else {
              \Drupal::logger('Varbase')->info("Unable to retrieve the merge request ID from \"$package_patch_remote_link\" for $package_name package.");
              $this->output()->writeln(dt("Unable to retrieve the merge request ID from \"$package_patch_remote_link\" for $package_name package."));
            }

            $patch_file_name = $processed_package_name . $fetch_date . $issue_id . $mr_id . ".patch";

            $patches_directory = $root_directory . "/patches/";
            /** @var \Drupal\Core\File\FileSystemInterface $file_system */
            $file_system = \Drupal::service('file_system');
            $file_system->prepareDirectory($patches_directory, FileSystemInterface:: CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);

            try {
              $full_patch_file_name = $patches_directory . "/" . $patch_file_name;

              $patch_file = fopen($full_patch_file_name, "w");
              fwrite($patch_file, $patch);
              fclose($patch_file);
              $package_patches[$package_patch_title] = "./patches/" . $patch_file_name;

              \Drupal::logger('Varbase')->info("Processed the patch for \"$package_patch_title\" for the $package_name package. <br /> From: $package_patch_remote_link <br /> To: ./patches/$patch_file_name");
              $this->output()->writeln(dt("Processed the patch for \"$package_patch_title\" for the $package_name package"));
              $this->output()->writeln(dt("From: $package_patch_remote_link"));
              $this->output()->writeln(dt("To: ./patches/$patch_file_name"));
            }
            catch (\Exception $e) {
              \Drupal::logger('Varbase')->info("Unable to save patch file $patch_file_name");
              $this->output()->writeln(dt("Unable to save patch file $patch_file_name"));
            }

            $this->output()->writeln(dt("--------------------------------------------"));
          }
        }
      }

      if ($not_found_merge_request_patches) {
        try {
          file_put_contents($root_directory . '/' . $root_composer['extra']['patches-file'], json_encode($patches_file_composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
        catch (\Exception $e) {
          \Drupal::logger('Varbase')->info("Unable to save " . $root_composer['extra']['patches-file'] . " file");
          $this->output()->writeln(dt("Unable to save " . $root_composer['extra']['patches-file'] . " file"));
        }

        $this->output()->writeln(dt("Competed the composer clean up of merge request patches in the " . $root_composer['extra']['patches-file'] . " file."));
      }
      else {
        $this->output()->writeln(dt("No merge request patches were found in the " . $root_composer['extra']['patches-file'] . " file."));
      }

    }
    else {
      $this->output()->writeln(dt("No patches-file in the root composer.json file."));
    }

  }

}
