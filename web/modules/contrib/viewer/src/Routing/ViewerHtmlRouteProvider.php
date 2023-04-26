<?php

namespace Drupal\viewer\Routing;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;
use Symfony\Component\Routing\Route;

/**
 * Provides routes for Viewer entities.
 *
 * @see \Drupal\Core\Entity\Routing\AdminHtmlRouteProvider
 * @see \Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider
 *
 * @ingroup viewer
 */
class ViewerHtmlRouteProvider extends AdminHtmlRouteProvider {

  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type) {
    $collection = parent::getRoutes($entity_type);
    $routes = [
      'new' => [
        'defaults' => [
          '_title' => 'New Viewer',
          '_form' => '\Drupal\viewer\Form\Viewer\NewForm',
        ],
        'requirements' => [
          '_permission' => 'add viewer',
        ],
        'skip_options' => TRUE,
      ],
      'iframe_preview_src' => [
        'defaults' => [
          '_title' => 'Preview',
          '_controller' => '\Drupal\viewer\Controller\ViewerController::preview',
        ],
        'requirements' => [
          '_permission' => 'add viewer',
        ],
      ],
      'iframe_preview' => [
        'defaults' => [
          '_title_callback' => '\Drupal\viewer\Controller\ViewerController::setPreviewerTitle',
          '_entity_form' => 'viewer.iframe_preview',
        ],
        'requirements' => [
          '_permission' => 'add viewer',
        ],
      ],
      'settings' => [
        'defaults' => [
          '_title_callback' => '\Drupal\viewer\Controller\ViewerController::setSettingsTitle',
          '_entity_form' => 'viewer.settings',
        ],
      ],
      'configuration' => [
        'defaults' => [
          '_title_callback' => '\Drupal\viewer\Controller\ViewerController::setConfigurationTitle',
          '_entity_form' => 'viewer.configuration',
        ],
      ],
      'endpoint' => [
        'defaults' => [
          '_title_callback' => '\Drupal\viewer\Controller\ViewerController::setEndpointTitle',
          '_entity_form' => 'viewer.endpoint',
        ],
      ],
      'filters' => [
        'defaults' => [
          '_title_callback' => '\Drupal\viewer\Controller\ViewerController::setFiltersTitle',
          '_entity_form' => 'viewer.filters',
        ],
      ],
      'enable' => [
        'defaults' => [
          '_title' => 'Enable',
          '_controller' => '\Drupal\viewer\Controller\ViewerController::setActive',
        ],
      ],
      'disable' => [
        'defaults' => [
          '_title' => 'Disable',
          '_controller' => '\Drupal\viewer\Controller\ViewerController::setInactive',
        ],
      ],
    ];
    foreach ($routes as $template => $route_info) {
      if ($route_object = $this->getFormRoutes($template, $route_info, $entity_type)) {
        $collection->add('entity.viewer.' . $template, $route_object);
      }
    }
    return $collection;
  }

  /**
   * {@inheritdoc}
   */
  protected function getCollectionRoute(EntityTypeInterface $entity_type) {
    // Overriding entity list builder's page title.
    if ($entity_type->hasLinkTemplate('collection') && $entity_type->hasListBuilderClass() && ($admin_permission = $entity_type->getAdminPermission())) {
      $route = new Route($entity_type->getLinkTemplate('collection'));
      $route->addDefaults([
        '_entity_list' => $entity_type->id(),
        '_title' => 'Viewers',
      ])
        ->setRequirement('_permission', $admin_permission);
      return $route;
    }
  }

  /**
   * Build entity specific routes.
   */
  protected function getFormRoutes($template, $route_info, EntityTypeInterface $entity_type) {
    if (!$entity_type->hasLinkTemplate($template)) {
      return NULL;
    }
    $route = new Route($entity_type->getLinkTemplate($template));
    foreach ($route_info['defaults'] as $key => $val) {
      $route->setDefault($key, $val);
    }
    if (!empty($route_info['requirements'])) {
      foreach ($route_info['requirements'] as $key => $val) {
        $route->setRequirement($key, $val);
      }
    }
    else {
      $route->setRequirement('_permission', $entity_type->getAdminPermission());
    }
    if (empty($route_info['skip_options'])) {
      $route->setOption('parameters', [
        'viewer' => [
          'type' => 'entity:viewer',
        ],
      ]);
    }
    return $route;
  }

}
