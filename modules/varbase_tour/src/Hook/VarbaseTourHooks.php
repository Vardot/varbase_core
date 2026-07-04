<?php

declare(strict_types=1);

namespace Drupal\varbase_tour\Hook;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Hook implementations for the Varbase Tour module.
 */
class VarbaseTourHooks {

  /**
   * Constructs a VarbaseTourHooks object.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user.
   * @param \Drupal\Core\Path\PathMatcherInterface $pathMatcher
   *   The path matcher.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   */
  public function __construct(
    protected AccountProxyInterface $currentUser,
    protected PathMatcherInterface $pathMatcher,
    protected RequestStack $requestStack,
    protected ConfigFactoryInterface $configFactory,
  ) {}

  /**
   * Implements hook_page_attachments().
   */
  #[Hook('page_attachments')]
  public function pageAttachments(array &$page): void {
    // Given that the current user is a logged in user.
    if ($this->currentUser->isAuthenticated()) {

      // When the current page is the front page.
      if ($this->pathMatcher->isFrontPage()) {
        $query_welcome = $this->requestStack->getCurrentRequest()->query->get('welcome');
        if (isset($query_welcome)) {
          $varbase_tour_config = $this->configFactory->getEditable('varbase_core.general_settings');
          $welcome_status = $varbase_tour_config->get('welcome_status');

          // When we do have "/?tour=1&welcome=done" is in the URL address.
          // for the front page.
          if ($query_welcome == 'done'
              && isset($welcome_status)
              && $welcome_status == 1) {
            // Then update the "welcome status" checkbox config to unchecked.
            $varbase_tour_config->set('welcome_status', 0);
            $varbase_tour_config->save();
          }
        }
      }
    }
  }

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme($existing, $type, $theme, $path): array {
    return [
      'welcome_modal' => [
        'variables' => [
          'items' => [],
        ],
      ],
    ];
  }

  /**
   * Implements hook_page_top().
   */
  #[Hook('page_top')]
  public function pageTop(array &$page_top): void {
    $current_user = $this->currentUser;

    // Only proceed if user is authenticated.
    if (!$current_user->isAuthenticated()) {
      return;
    }

    // Only proceed if we're on the front page.
    if (!$this->pathMatcher->isFrontPage()) {
      return;
    }

    $request = $this->requestStack->getCurrentRequest();
    $query_welcome = $request->query->get('welcome');

    // Only proceed if welcome parameter exists and is not 'done'.
    if ($query_welcome === NULL || $query_welcome === 'done') {
      return;
    }

    $config = $this->configFactory->get('varbase_core.general_settings');
    $welcome_status = $config->get('welcome_status');

    // Only show modal if welcome status is enabled.
    if (empty($welcome_status) || $welcome_status == FALSE) {
      return;
    }

    $page_top['welcome_modal'] = [
      '#type' => 'container',
      '#theme' => 'welcome_modal',
      // We already checked authentication above.
      '#access' => TRUE,
      '#cache' => [
        'keys' => ['varbase_core', 'welcome_modal'],
        'contexts' => [
          'user.permissions',
          'url.query_args:welcome',
          'route.name',
        ],
        'tags' => ['config:varbase_core.general_settings'],
      ],
    ];
  }

}
