<?php

declare(strict_types=1);

namespace Drupal\varbase_admin\Hook;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;

/**
 * Hook implementations for the Varbase Admin module.
 */
class VarbaseAdminHooks {

  use StringTranslationTrait;

  /**
   * Constructs a VarbaseAdminHooks object.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The current route match.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user.
   */
  public function __construct(
    protected LanguageManagerInterface $languageManager,
    protected ModuleHandlerInterface $moduleHandler,
    protected RouteMatchInterface $routeMatch,
    protected AccountProxyInterface $currentUser,
  ) {}

  /**
   * Implements hook_form_BASE_FORM_ID_alter() for the node type add form.
   */
  #[Hook('form_node_type_add_form_alter')]
  public function formNodeTypeAddFormAlter(array &$form, FormStateInterface $form_state): void {

    // Make "Promoted to front page" default is always off.
    if (isset($form['workflow'])
      && isset($form['workflow']['options'])
      && isset($form['workflow']['options']['#default_value'])
      && isset($form['workflow']['options']['#default_value']['promote'])) {

      // Remove promote from the default options.
      unset($form['workflow']['options']['#default_value']['promote']);
    }

    // Rabbit hole "Allow these settings to be overridden for individual
    // entities" to be default off.
    if (isset($form['rabbit_hole'])
      && isset($form['rabbit_hole']['rh_override'])
      && isset($form['rabbit_hole']['rh_override']['#default_value'])) {

      // Uncheck the default override for rabbit hole.
      $form['rabbit_hole']['rh_override']['#default_value'] = 0;
    }

    // Available in Menus default off.
    if (isset($form['menu'])
      && isset($form['menu']['menu_options'])
      && isset($form['menu']['menu_options']['#default_value'])) {

      // Have no default menu options.
      $form['menu']['menu_options']['#default_value'] = [];
    }

    // "Display author and date information" default off.
    if (isset($form['display'])
      && isset($form['display']['display_submitted'])
      && isset($form['display']['display_submitted']['#default_value'])) {

      // Uncheck the default display submitted author option.
      $form['display']['display_submitted']['#default_value'] = 0;
    }
  }

  /**
   * Implements hook_form_alter().
   */
  #[Hook('form_alter')]
  public function formAlter(array &$form, FormStateInterface $form_state, $form_id): void {
    if (preg_match('/^node_.*._form$/', $form_id)) {
      if (isset($form['actions']['unlock'])) {
        unset($form['actions']['unlock']);
      }
      if (isset($form['actions']['delete'])) {
        unset($form['actions']['delete']);
      }
    }
  }

  /**
   * Implements hook_library_info_alter().
   */
  #[Hook('library_info_alter')]
  public function libraryInfoAlter(&$libraries, $extension): void {

    if ($extension === 'gin' && isset($libraries['gin'])) {

      // Add the global Varbase Admin styling.
      $libraries['gin']['dependencies'][] = 'varbase_admin/admin';

      // Add the global Varbase Admin right to left (RTL) styling.
      if ($this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getDirection() === 'rtl') {
        $libraries['gin']['dependencies'][] = 'varbase_admin/admin-rtl';
      }
    }
  }

  /**
   * Implements hook_element_info_alter().
   */
  #[Hook('element_info_alter')]
  public function elementInfoAlter(array &$types): void {

    // Manage with Drupal Core Navigation Top Bar.
    if (isset($types['top_bar'])) {
      if ($this->moduleHandler->moduleExists('navigation')) {
        $types['top_bar']['#attached']['library'][] = 'varbase_admin/admin-navigation';
      }
    }

    // Manage only with the toolbar.
    if (isset($types['toolbar'])) {
      if ($this->moduleHandler->moduleExists('navigation')) {
        $types['toolbar']['#attached']['library'][] = 'varbase_admin/admin-navigation';
      }
      elseif ($this->moduleHandler->moduleExists('admin_toolbar')) {
        $types['toolbar']['#attached']['library'][] = 'varbase_admin/admin-toolbar';
      }
    }
  }

  /**
   * Implements hook_menu_local_tasks_alter().
   */
  #[Hook('menu_local_tasks_alter')]
  public function menuLocalTasksAlter(&$data, $route_name, RefinableCacheableDependencyInterface &$cacheability): void {
    // Check if we're on a node edit page.
    $current_route_name = $this->routeMatch->getRouteName();

    // Only proceed if we're on a node edit form.
    if (!$current_route_name || !preg_match('/^entity\.node\.edit_form$/', $current_route_name)) {
      return;
    }

    $entity = $this->routeMatch->getParameter('node');
    if (!$entity || !($entity instanceof ContentEntityInterface)) {
      return;
    }

    // Style existing Delete link and move it to last position.
    if (isset($data['tabs'][0])) {
      foreach ($data['tabs'][0] as &$tab) {
        if (isset($tab['#link']['url'])) {
          $route_name = $tab['#link']['url']->getRouteName();
          // Check if this is a delete route for the entity.
          if (strpos($route_name, '.delete') !== FALSE || strpos($route_name, '.delete_form') !== FALSE) {
            // Add danger styling to delete link.
            if (!isset($tab['#link']['localized_options']['attributes']['class'])) {
              $tab['#link']['localized_options']['attributes']['class'] = [];
            }
            $tab['#link']['localized_options']['attributes']['class'][] = 'button';

            // Move delete to last position (weight 200).
            $tab['#weight'] = 200;
          }
        }
      }
    }

    // Add unlock link if content_lock module is enabled.
    if (!$this->moduleHandler->moduleExists('content_lock')) {
      return;
    }

    // @phpstan-ignore-next-line
    $content_lock_service = \Drupal::service('content_lock');
    $current_user = $this->currentUser;

    // Check if the entity is lockable and locked.
    if (!$content_lock_service->isLockable($entity)) {
      return;
    }

    $lock = $content_lock_service->fetchLock($entity);
    if (!$lock) {
      return;
    }

    // Check if user has permission to break locks or owns the lock.
    if (!$current_user->hasPermission('break content lock') && $lock->uid != $current_user->id()) {
      return;
    }

    // Build unlock URL.
    $entity_type = $entity->getEntityTypeId();
    $route_parameters = [
      'entity' => $entity->id(),
      'langcode' => $content_lock_service->isTranslationLockEnabled($entity_type) ?
      $entity->language()->getId() : LanguageInterface::LANGCODE_NOT_SPECIFIED,
      'form_op' => '*',
    ];

    $unlock_url = Url::fromRoute('content_lock.break_lock.' . $entity_type, $route_parameters);

    // Add unlock local task with warning style.
    $data['tabs'][0]['varbase_admin.unlock'] = [
      '#theme' => 'menu_local_task',
      '#link' => [
        'title' => $this->t('Unlock'),
        'url' => $unlock_url,
        'localized_options' => [
          'attributes' => [
            'title' => $this->t('Unlock this content'),
            'class' => ['button'],
          ],
        ],
      ],
      '#access' => AccessResult::allowedIf($current_user->hasPermission('break content lock') || $lock->uid == $current_user->id())
        ->addCacheContexts(['user.permissions'])
        ->addCacheTags(['content_lock:' . $entity_type . ':' . $entity->id()]),
      '#weight' => 150,
    ];

    // Add cache contexts for user permissions and entity access.
    $cacheability->addCacheContexts(['user.permissions']);
    $cacheability->addCacheTags(['content_lock:' . $entity_type . ':' . $entity->id()]);
  }

  /**
   * Implements hook_top_bar_item_alter().
   */
  #[Hook('top_bar_item_alter')]
  public function topBarItemAlter(&$definitions): void {
    // Override the ResponsiveIcons plugin to add conditional logic.
    if (isset($definitions['responsive_icons'])) {
      // Replace the class with our custom implementation.
      $definitions['responsive_icons']['class'] = 'Drupal\varbase_admin\Plugin\TopBarItem\ConditionalResponsiveIcons';
    }
  }

}
