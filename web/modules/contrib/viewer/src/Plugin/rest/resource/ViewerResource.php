<?php

namespace Drupal\viewer\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;

/**
 * Provides data for Viewer plugin.
 *
 * @RestResource(
 *   id = "viewer",
 *   label = @Translation("Viewer"),
 *   uri_paths = {
 *     "canonical" = "/get/viewer/{uuid}"
 *   }
 * )
 */
class ViewerResource extends ResourceBase {

  /**
   * Get method to pull viewer plugin data.
   */
  public function get($uuid = NULL) {
    $entities = \Drupal::entityTypeManager()->getStorage('viewer')
      ->loadByProperties(['uuid' => $uuid, 'status' => 1]);
    $response = [
      'error'   => 'No viewer found',
      'data' => [],
    ];
    $tags = ['viewer_source', 'viewer', 'viewer:' . $uuid];
    if ($entity = reset($entities)) {
      $viewer = $entity->getViewerPlugin()->setViewer($entity);
      $response = [
        'data' => $viewer->getResponse(),
        'filters' => $entity->getFilters(),
        'configuration' => $entity->getConfiguration(),
        'settings' => $entity->getSettings(),
      ];
      if ($viewer_source = $entity->getViewerSource()) {
        array_push($tags, 'viewer_source:' . $viewer_source->id());
      }
    }
    $res = new ResourceResponse($response);
    $res->getCacheableMetadata()->addCacheContexts(['user.permissions', 'url']);
    $res->getCacheableMetadata()->addCacheTags($tags);
    if ($entity) {
      $res->addCacheableDependency($entity);
    }
    return $res;
  }

}
