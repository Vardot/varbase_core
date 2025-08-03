<?php

declare(strict_types=1);

namespace Drupal\varbase_admin\Plugin\TopBarItem;

use Drupal\responsive_preview_navigation\Plugin\TopBarItem\ResponsiveIcons;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\responsive_preview\ResponsivePreview;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Extends ResponsiveIcons to conditionally hide on node edit pages.
 */
class ConditionalResponsiveIcons extends ResponsiveIcons {

  /**
   * The route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected RouteMatchInterface $routeMatch;

  /**
   * Constructs a new ConditionalResponsiveIcons instance.
   *
   * @param array $configuration
   *   A configuration array containing plugin instance information.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\responsive_preview\ResponsivePreview $responsivePreview
   *   The ResponsivePreview.
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   *   The current user.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The route match service.
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    $plugin_definition,
    ResponsivePreview $responsivePreview,
    AccountProxyInterface $currentUser,
    RouteMatchInterface $routeMatch,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $responsivePreview, $currentUser);
    $this->routeMatch = $routeMatch;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('responsive_preview'),
      $container->get('current_user'),
      $container->get('current_route_match'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    // Hide responsive preview when editing or creating nodes.
    $current_route_name = $this->routeMatch->getRouteName();

    // Check for both node edit and node add forms.
    if ($current_route_name && (
        preg_match('/^entity\.node\.edit_form$/', $current_route_name) ||
        preg_match('/^node\.add$/', $current_route_name) ||
        preg_match('/^node\.add_page$/', $current_route_name)
      )) {
      // Return empty build array with cache contexts for proper caching.
      return [
        '#cache' => [
          'contexts' => [
            'user.permissions',
            'route',
          ],
        ],
      ];
    }

    // Otherwise, use the parent build method.
    return parent::build();
  }

}
