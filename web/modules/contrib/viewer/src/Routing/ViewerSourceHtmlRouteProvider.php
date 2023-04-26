<?php

namespace Drupal\viewer\Routing;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;
use Symfony\Component\Routing\Route;

/**
 * Provides routes for ViewerSource entities.
 *
 * @see \Drupal\Core\Entity\Routing\AdminHtmlRouteProvider
 * @see \Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider
 *
 * @ingroup viewer
 */
class ViewerSourceHtmlRouteProvider extends AdminHtmlRouteProvider {

  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type) {
    $collection = parent::getRoutes($entity_type);
    $routes = [
      'new' => [
        'defaults' => [
          '_title' => 'New Source',
          '_form' => '\Drupal\viewer\Form\Source\NewForm',
        ],
        'requirements' => [
          '_permission' => 'add viewer source',
        ],
        'skip_options' => TRUE,
      ],
      'bulk_import' => [
        'defaults' => [
          '_title' => 'Bulk Import',
          '_form' => '\Drupal\viewer\Form\Source\BulkImportForm',
        ],
        'requirements' => [
          '_permission' => 'bulk import viewer source',
        ],
        'skip_options' => TRUE,
      ],
      'settings' => [
        'defaults' => [
          '_title_callback' => '\Drupal\viewer\Controller\ViewerSourceController::setSettingsTitle',
          '_form' => '\Drupal\viewer\Form\Source\SettingsForm',
        ],
      ],
      'schedule' => [
        'defaults' => [
          '_title_callback' => '\Drupal\viewer\Controller\ViewerSourceController::setNextImportTitle',
          '_entity_form' => 'viewer_source.schedule',
        ],
      ],
      'notifications' => [
        'defaults' => [
          '_title_callback' => '\Drupal\viewer\Controller\ViewerSourceController::setNotificationsTitle',
          '_entity_form' => 'viewer_source.notifications',
        ],
      ],
      'enable' => [
        'defaults' => [
          '_title' => 'Enable',
          '_controller' => '\Drupal\viewer\Controller\ViewerSourceController::setActive',
        ],
      ],
      'disable' => [
        'defaults' => [
          '_title' => 'Disable',
          '_controller' => '\Drupal\viewer\Controller\ViewerSourceController::setInactive',
        ],
      ],
      'download' => [
        'defaults' => [
          '_title' => 'Download',
          '_controller' => '\Drupal\viewer\Controller\ViewerSourceController::download',
        ],
        'requirements' => [
          '_permission' => 'add viewer source',
        ],
      ],
      'import' => [
        'defaults' => [
          '_title_callback' => '\Drupal\viewer\Form\Source\ImportForm::getTitle',
          '_form' => '\Drupal\viewer\Form\Source\ImportForm',
        ],
        'requirements' => [
          '_permission' => 'add viewer source',
        ],
      ],
    ];
    foreach ($routes as $template => $route_info) {
      if ($route_object = $this->getFormRoutes($template, $route_info, $entity_type)) {
        $collection->add('entity.viewer_source.' . $template, $route_object);
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
        '_title' => 'Sources',
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
        'viewer_source' => [
          'type' => 'entity:viewer_source',
        ],
      ]);
    }
    return $route;
  }

}
