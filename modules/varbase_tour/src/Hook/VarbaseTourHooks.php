<?php

namespace Drupal\varbase_tour\Hook;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Object-oriented hook implementations for Varbase Tour.
 */
class VarbaseTourHooks {

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
    if ($this->currentUser->isAuthenticated() && $this->pathMatcher->isFrontPage()) {
      $query_welcome = $this->requestStack->getCurrentRequest()->query->get('welcome');
      if (isset($query_welcome)) {
        $varbase_tour_config = $this->configFactory->getEditable('varbase_core.general_settings');
        $welcome_status = $varbase_tour_config->get('welcome_status');
        if ($query_welcome == 'done' && isset($welcome_status) && $welcome_status == 1) {
          $varbase_tour_config->set('welcome_status', 0);
          $varbase_tour_config->save();
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
    if ($this->currentUser->isAuthenticated() && $this->pathMatcher->isFrontPage()) {
      $query_welcome = $this->requestStack->getCurrentRequest()->query->get('welcome');
      if (isset($query_welcome) && $query_welcome != 'done') {
        $varbase_tour_config = $this->configFactory->getEditable('varbase_core.general_settings');
        $welcome_status = $varbase_tour_config->get('welcome_status');
        if (isset($welcome_status) && $welcome_status == 1) {
          $page_top['welcome_modal'] = [
            '#type' => 'container',
            '#theme' => 'welcome_modal',
            '#access' => $this->currentUser->isAuthenticated(),
            '#cache' => [
              'keys' => ['varbase_core'],
              'contexts' => ['user.permissions'],
            ],
          ];
        }
      }
    }
  }

}
