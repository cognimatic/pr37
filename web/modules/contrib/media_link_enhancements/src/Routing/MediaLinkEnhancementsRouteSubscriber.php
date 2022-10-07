<?php

namespace Drupal\media_link_enhancements\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class MediaLinkEnhancementsRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // Override the default controller for media.
    if ($route = $collection->get('entity.media.canonical')) {
      $route->setDefaults([
        '_controller' => '\Drupal\media_link_enhancements\Controller\MediaLinkEnhancementsController::download',
        '_title_callback' => '\Drupal\media_link_enhancements\Controller\MediaLinkEnhancementsController::title',
      ]);
    }
  }

}
