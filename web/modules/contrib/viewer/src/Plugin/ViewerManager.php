<?php

namespace Drupal\viewer\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Viewer plugin manager.
 *
 * @package viewer
 */
class ViewerManager extends DefaultPluginManager {

  /**
   * Constructs an Viewer object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations,.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/viewer/viewer', $namespaces, $module_handler, 'Drupal\viewer\Plugin\ViewerInterface', 'Drupal\viewer\Annotation\Viewer');
    $this->alterInfo('viewer_info');
    $this->setCacheBackend($cache_backend, 'viewer');
  }

}
