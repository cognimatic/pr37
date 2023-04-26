<?php

namespace Drupal\viewer\ParamConverter;

use Drupal\Core\ParamConverter\EntityConverter;
use Drupal\Core\ParamConverter\ParamConverterInterface;
use Symfony\Component\Routing\Route;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Http\Exception\CacheableNotFoundHttpException;

/**
 * Parameter converter for upcasting viewer_source entity.
 *
 * @ingroup viewer
 */
class ViewerSourceConverter extends EntityConverter implements ParamConverterInterface {

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults) {
    $entity_type_id = $this->getEntityTypeFromDefaults($definition, $name, $defaults);

    // Get the viewer_source ID.
    $entity_id = $defaults['viewer_source'] ?? FALSE;

    // Load the viewer_source entity.
    if (!$entity_id || !($entity = $this->entityTypeManager->getStorage($entity_type_id)->load($entity_id, $value))) {
      $cache_metadata = new CacheableMetadata();
      throw new CacheableNotFoundHttpException($cache_metadata->setCacheContexts(['url']), 'Unable to load Viewer Source entity.');
    }

    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function applies($definition, $name, Route $route) {
    return (parent::applies($definition, $name, $route) && $definition['type'] === 'entity:viewer_source');
  }

}
