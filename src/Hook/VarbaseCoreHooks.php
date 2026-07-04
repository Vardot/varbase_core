<?php

declare(strict_types=1);

namespace Drupal\varbase_core\Hook;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\user\UserInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Vardot\Entity\EntityDefinitionUpdateManager;
use Vardot\Installer\ModuleInstallerFactory;

/**
 * Hook implementations for the Varbase Core module.
 */
class VarbaseCoreHooks {

  use StringTranslationTrait;

  /**
   * Constructs a VarbaseCoreHooks object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\Core\DependencyInjection\ClassResolverInterface $classResolver
   *   The class resolver.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $themeManager
   *   The theme manager.
   */
  public function __construct(
    protected ConfigFactoryInterface $configFactory,
    protected ClassResolverInterface $classResolver,
    protected RequestStack $requestStack,
    protected ThemeManagerInterface $themeManager,
  ) {}

  /**
   * Implements hook_form_BASE_FORM_ID_alter() for the node form.
   */
  #[Hook('form_node_form_alter')]
  public function formNodeFormAlter(&$form, FormStateInterface $form_state): void {
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
  public function formAlter(&$form, FormStateInterface $form_state, $form_id): void {
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
  public function templatePreprocessDefaultVariablesAlter(&$variables): void {
    $user_settings_config = $this->configFactory->get('user.settings');
    $variables['user_settings_register_admin_only'] = $user_settings_config->get('register') == 'admin_only' ? 1 : 0;
  }

  /**
   * Implements hook_modules_installed().
   */
  #[Hook('modules_installed')]
  public function modulesInstalled($modules, $is_syncing): void {
    if (in_array('automated_cron', $modules)) {
      // When the Automated Cron module is enabled, which not enabled by default.
      // with development and deployments to production sites.
      // But the need to have the basic extra change for the settings over
      // the Automated Cron default settings.
      // Managed configs for the Automated Cron module.
      $managed_configs = [
        'automated_cron.settings',
      ];
      ModuleInstallerFactory::importConfigsFromList('varbase_core', $managed_configs, 'config/managed/automated_cron');

      // Entity updates to clear up any mismatched entity and/or field
      // definitions and fix changes were detected in the entity type and field
      // definitions.
      $this->classResolver
        ->getInstanceFromDefinition(EntityDefinitionUpdateManager::class)
        ->applyUpdates();
    }

    if (in_array('editoria11y', $modules)) {
      // When the Editoria11y Accessibility Checker module is enabled,
      // which not enabled by default.
      // Add permissions for default user roles.
      ModuleInstallerFactory::addPermissions('varbase_core', 'config/managed/editoria11y/permissions');

      // Import the default configuration recipe for Editoria11y Settings as a
      // managed config.
      $editoria11y_managed_configs = [
        'editoria11y.settings',
      ];
      ModuleInstallerFactory::importConfigsFromList('varbase_core', $editoria11y_managed_configs, 'config/managed/editoria11y/recipes');

    }

    if (in_array('sitewide_alert', $modules)) {
      // When the Sitewide Alert module is enabled, which not enabled by default.
      // with development and deployments to production sites.
      // But the need to have the basic extra change for the settings over
      // the Sitewide Alert default settings.
      // Managed configs for the Sitewide Alert module.
      $managed_configs = [
        'sitewide_alert.settings',
      ];
      ModuleInstallerFactory::importConfigsFromList('varbase_core', $managed_configs, 'config/managed/sitewide_alert');

    }

    if (in_array('varbase_email', $modules)) {
      // When the Varbase Email module is enabled, which not enabled by default.
      // which could be enabled after Varbase Core in number of case.
      // Managed configs after enabling the Varbase Email module.
      $managed_configs = [
        'modeler_api.data_model.eca_bpmn_io_user_login',
        'eca.eca.user_login',
        'modeler_api.data_model.eca_bpmn_io_admin_change_role_notification',
        'eca.eca.admin_change_role_notification',
        'modeler_api.data_model.eca_bpmn_io_user_recertification',
        'eca.eca.user_recertification',
      ];
      ModuleInstallerFactory::importConfigsFromList('varbase_core', $managed_configs, 'config/managed/varbase_email');

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

  /**
   * Implements hook_token_info().
   */
  #[Hook('token_info')]
  public function tokenInfo(): array {
    // Default theme token.
    $info['types']['default-active-theme'] = [
      'name' => $this->t('Default theme'),
      'description' => $this->t('Tokens related to the Default theme.'),
    ];
    $info['tokens']['default-active-theme']['path'] = [
      'name' => $this->t('Path'),
      'description' => $this->t('The path of the Default theme.'),
    ];
    $info['tokens']['site']['origin-url'] = [
      'name' => $this->t("Origin URL"),
      'description' => $this->t("The Origin URL (scheme and HTTP host) of the site. No language prefix."),
    ];

    return $info;
  }

  /**
   * Implements hook_tokens().
   */
  #[Hook('tokens')]
  public function tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata): array {
    $replacements = [];

    if ($type == 'site') {
      foreach ($tokens as $name => $original) {
        switch ($name) {
          case 'origin-url':
            // Until #1088112: Introduce a token to get site's base URL is
            // committed,
            // https://www.drupal.org/project/drupal/issues/1088112
            // let's use a custom token. Let's call it: [site:origin-url].
            // No language prefix in the url.
            // https://www.drupal.org/project/varbase_core/issues/3106793
            $request = $this->requestStack->getCurrentRequest();
            $origin_url = $request->getSchemeAndHttpHost() . $request->getBaseUrl();
            $bubbleable_metadata->addCacheContexts(['url.site']);
            $replacements[$original] = $origin_url;
            break;
        }
      }
    }
    elseif ($type == 'default-active-theme') {
      foreach ($tokens as $name => $original) {
        switch ($name) {
          case 'path':
            $replacements[$original] = $this->themeManager->getActiveTheme()->getPath();
            break;
        }
      }
    }

    return $replacements;
  }

}
