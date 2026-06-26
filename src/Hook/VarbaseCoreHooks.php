<?php

namespace Drupal\varbase_core\Hook;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\user\UserInterface;
use Vardot\Entity\EntityDefinitionUpdateManager;
use Vardot\Installer\ModuleInstallerFactory;

/**
 * Object-oriented hook implementations for Varbase Core.
 *
 * Drupal 11 replaces procedural hooks with methods that carry the #[Hook]
 * attribute (https://www.drupal.org/node/3442349). The real logic lives here
 * and uses dependency injection; the procedural functions in varbase_core.module
 * are kept only as #[LegacyHook] shims delegating to this service.
 */
class VarbaseCoreHooks {

  /**
   * Constructs a VarbaseCoreHooks object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\Core\DependencyInjection\ClassResolverInterface $classResolver
   *   The class resolver.
   */
  public function __construct(
    protected ConfigFactoryInterface $configFactory,
    protected ClassResolverInterface $classResolver,
  ) {}

  /**
   * Implements hook_form_BASE_FORM_ID_alter() for node_form.
   */
  #[Hook('form_node_form_alter')]
  public function formNodeFormAlter(array &$form, FormStateInterface $form_state): void {
    // Override node form sidebar order.
    if (isset($form['author'])) {
      $form['author']['#weight'] = -6;
    }
    if (isset($form['field_comments'])) {
      $form['field_comments']['widget'][0]['#weight'] = -5;
    }
    if (isset($form['path_settings'])) {
      $form['path_settings']['#weight'] = -4;
    }
    if (isset($form['field_meta_tags'])) {
      $form['field_meta_tags']['widget'][0]['#weight'] = -3;
    }
    if (isset($form['simple_sitemap'])) {
      $form['simple_sitemap']['#weight'] = -2;
    }
    if (isset($form['ds_switch_view_mode'])) {
      $form['ds_switch_view_mode']['#weight'] = 101;
    }
  }

  /**
   * Implements hook_form_alter().
   */
  #[Hook('form_alter')]
  public function formAlter(array &$form, FormStateInterface $form_state, $form_id): void {
    // Disable the checkbox to delete referenced entity from entityqueues.
    $subqueue_base_form = $form_state->getBuildInfo();
    if (array_key_exists('base_form_id', $subqueue_base_form)
      && $subqueue_base_form['base_form_id'] == 'entity_subqueue_form'
      && isset($form['items']['widget']['entities'])
      && !empty($form['items']['widget']['entities'])) {

      foreach ($form['items']['widget']['entities'] as &$subqueue_entity) {
        if (is_array($subqueue_entity)) {
          if (array_key_exists('form', $subqueue_entity) && array_key_exists('delete', $subqueue_entity['form'])) {
            $subqueue_entity['form']['delete']['#access'] = FALSE;
          }
        }
      }
    }
  }

  /**
   * Implements hook_template_preprocess_default_variables_alter().
   */
  #[Hook('template_preprocess_default_variables_alter')]
  public function templatePreprocessDefaultVariablesAlter(array &$variables): void {
    $user_settings_config = $this->configFactory->get('user.settings');
    $variables['user_settings_register_admin_only'] = $user_settings_config->get('register') == 'admin_only' ? 1 : 0;
  }

  /**
   * Implements hook_modules_installed().
   */
  #[Hook('modules_installed')]
  public function modulesInstalled(array $modules, bool $is_syncing): void {
    if (in_array('automated_cron', $modules)) {
      // Managed configs for the Automated Cron module.
      $managed_configs = [
        'automated_cron.settings',
      ];
      ModuleInstallerFactory::importConfigsFromList('varbase_core', $managed_configs, 'config/managed/automated_cron');

      // Entity updates to clear up any mismatched entity and/or field
      // definitions detected in the entity type and field definitions.
      $this->classResolver
        ->getInstanceFromDefinition(EntityDefinitionUpdateManager::class)
        ->applyUpdates();
    }

    if (in_array('editoria11y', $modules)) {
      // Add permissions for default user roles.
      ModuleInstallerFactory::addPermissions('varbase_core', 'config/managed/editoria11y/permissions');

      // Import the default configuration recipe for Editoria11y Settings.
      $editoria11y_managed_configs = [
        'editoria11y.settings',
      ];
      ModuleInstallerFactory::importConfigsFromList('varbase_core', $editoria11y_managed_configs, 'config/managed/editoria11y/recipes');
    }
  }

  /**
   * Implements hook_email_registration_name_alter().
   */
  #[Hook('email_registration_name_alter')]
  public function emailRegistrationNameAlter(string &$accountName, UserInterface $account): void {
    $varbase_general_settings = $this->configFactory->getEditable('varbase_core.general_settings');
    $allow_custom_account_name = $varbase_general_settings->get('allow_custom_account_name');

    if (!isset($allow_custom_account_name)) {
      $allow_custom_account_name = TRUE;
    }

    $custom_account_name = $account->getAccountName();

    if ($allow_custom_account_name && $custom_account_name != '') {
      $accountName = email_registration_unique_username($custom_account_name, (int) $account->id());
    }
  }

}
