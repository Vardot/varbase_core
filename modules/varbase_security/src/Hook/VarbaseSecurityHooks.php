<?php

declare(strict_types=1);

namespace Drupal\varbase_security\Hook;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Vardot\Entity\EntityDefinitionUpdateManager;
use Vardot\Installer\ModuleInstallerFactory;

/**
 * Hook implementations for the Varbase Security module.
 */
class VarbaseSecurityHooks {

  use StringTranslationTrait;
  use DependencySerializationTrait;

  /**
   * Constructs a VarbaseSecurityHooks object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user.
   * @param \Drupal\Core\DependencyInjection\ClassResolverInterface $classResolver
   *   The class resolver.
   */
  public function __construct(
    protected ModuleHandlerInterface $moduleHandler,
    protected ConfigFactoryInterface $configFactory,
    protected AccountProxyInterface $currentUser,
    protected ClassResolverInterface $classResolver,
  ) {}

  /**
   * Implements hook_element_info_alter().
   */
  #[Hook('element_info_alter')]
  public function elementInfoAlter(array &$types): void {
    if ($this->moduleHandler->moduleExists('password_policy')) {
      if (isset($types['password_confirm'])) {
        if (isset($types['password_confirm']['#process'])) {
          // Hide the password confirm process.
          if ($key = array_search('password_policy_check_constraints_password_confirm_process', $types['password_confirm']['#process'])) {
            unset($types['password_confirm']['#process'][$key]);
          }
        }

        // Have the custom Varbase security user form process password confirm.
        $types['password_confirm']['#process'][] = [$this, 'userFormProcessPasswordConfirm'];
      }
    }
  }

  /**
   * Element process handler for client-side password validation.
   *
   * This #process handler is automatically invoked for 'password_confirm' form
   * elements to add the JavaScript and string translations for dynamic password
   * validation.
   */
  public function userFormProcessPasswordConfirm(array $element): array {
    $config = $this->configFactory
      ->getEditable('varbase_security.password_suggestions.settings')
      ->get();

    $password_minimal_length = $this->configFactory
      ->getEditable('password_policy.password_policy.default_policy')
      ->get('policy_constraints')[2]["character_length"];

    $password_settings = [
      'confirmTitle' => $config['confirmTitle'] ?? $this->t('Password match:'),
      'confirmSuccess' => $config['confirmSuccess'] ?? $this->t('yes'),
      'confirmFailure' => $config['confirmFailure'] ?? $this->t('no'),
      'strengthTitle' => $config['strengthTitle'] ?? $this->t('Password strength:'),
      'showStrengthIndicator' => TRUE,
      'hasWeaknesses' => $config['hasWeaknesses'] ?? $this->t('Recommendations to make your password stronger:'),
      'tooShort' => $config['tooShort'] ?? $this->t('Make it at least 8 characters'),
      'addLowerCase' => $config['addLowerCase'] ?? $this->t('Add lowercase letters'),
      'addUpperCase' => $config['addUpperCase'] ?? $this->t('Add uppercase letters'),
      'addNumbers' => $config['addNumbers'] ?? $this->t('Add numbers'),
      'addPunctuation' => $config['addPunctuation'] ?? $this->t('Add punctuation'),
      'sameAsUsername' => $config['sameAsUsername'] ?? $this->t('Make it different from your username'),
      'weak' => $config['weak'] ?? $this->t('Weak'),
      'fair' => $config['fair'] ?? $this->t('Fair'),
      'good' => $config['good'] ?? $this->t('Good'),
      'strong' => $config['strong'] ?? $this->t('Strong'),
      'username' => $this->currentUser->getAccountName(),
      'minimal_length' => $password_minimal_length ?? 12,
    ];

    $element['#attached']['library'][] = 'varbase_security/password-suggestions';
    $element['#attached']['drupalSettings']['password'] = $password_settings;

    return $element;
  }

  /**
   * Implements hook_modules_installed().
   */
  #[Hook('modules_installed')]
  public function modulesInstalled($modules, $is_syncing): void {
    if (in_array('security_review', $modules)) {
      // The module will be Enabled and Disabled many times
      // with development and deployments to production sites.
      // But the need to have the basic extra change for config over
      // the Security Review default configs.
      // Managed configs for the Security Review module.
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

      // Import managed configs to the site active configs.
      ModuleInstallerFactory::importConfigsFromList('varbase_security', $managed_configs, 'config/managed/security_review');

      // Entity updates to clear up any mismatched entity and/or field
      // definitions and fix changes were detected in the entity type and field
      // definitions.
      $this->classResolver
        ->getInstanceFromDefinition(EntityDefinitionUpdateManager::class)
        ->applyUpdates();
    }
  }

}
