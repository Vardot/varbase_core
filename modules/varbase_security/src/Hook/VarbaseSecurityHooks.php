<?php

namespace Drupal\varbase_security\Hook;

use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Vardot\Entity\EntityDefinitionUpdateManager;
use Vardot\Installer\ModuleInstallerFactory;

/**
 * Object-oriented hook implementations for Varbase Security.
 */
class VarbaseSecurityHooks {

  public function __construct(
    protected ModuleHandlerInterface $moduleHandler,
    protected ClassResolverInterface $classResolver,
  ) {}

  /**
   * Implements hook_element_info_alter().
   */
  #[Hook('element_info_alter')]
  public function elementInfoAlter(array &$types): void {
    if ($this->moduleHandler->moduleExists('password_policy')) {
      if (isset($types['password_confirm']['#process'])) {
        if ($key = array_search('password_policy_check_constraints_password_confirm_process', $types['password_confirm']['#process'])) {
          unset($types['password_confirm']['#process'][$key]);
        }
      }
      // The #process callback stays a procedural function (referenced by name).
      $types['password_confirm']['#process'][] = 'varbase_security_user_form_process_password_confirm';
    }
  }

  /**
   * Implements hook_modules_installed().
   */
  #[Hook('modules_installed')]
  public function modulesInstalled(array $modules, bool $is_syncing): void {
    if (in_array('security_review', $modules)) {
      $managed_configs = [
        'security_review.settings',
        'security_review.check.security_review-admin_permissions',
        'security_review.check.security_review-error_reporting',
        'security_review.check.security_review-executable_php',
        'security_review.check.security_review-failed_logins',
        'security_review.check.security_review-field',
        'security_review.check.security_review-file_perms',
        'security_review.check.security_review-input_formats',
        'security_review.check.security_review-private_files',
        'security_review.check.security_review-query_errors',
        'security_review.check.security_review-temporary_files',
        'security_review.check.security_review-trusted_hosts',
        'security_review.check.security_review-upload_extensions',
        'security_review.check.security_review-views_access',
      ];
      ModuleInstallerFactory::importConfigsFromList('varbase_security', $managed_configs, 'config/managed/security_review');

      $this->classResolver
        ->getInstanceFromDefinition(EntityDefinitionUpdateManager::class)
        ->applyUpdates();
    }
  }

}
