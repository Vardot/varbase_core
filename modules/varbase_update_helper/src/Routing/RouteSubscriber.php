<?php

namespace Drupal\varbase_update_helper\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('checklistapi.checklists.update_helper_checklist')) {
      $route->setDefault('_title', 'Varbase update instructions');
    }
  }

}
