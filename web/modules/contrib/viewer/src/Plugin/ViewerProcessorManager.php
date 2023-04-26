<?php

namespace Drupal\viewer\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * ViewerProcessor plugin manager.
 *
 * @package viewer
 */
class ViewerProcessorManager extends DefaultPluginManager {

  /**
   * Constructs an ViewerProcessorManager object.
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
    parent::__construct('Plugin/viewer/processor', $namespaces, $module_handler, 'Drupal\viewer\Plugin\ViewerProcessorInterface', 'Drupal\viewer\Annotation\ViewerProcessor');
    $this->alterInfo('viewer_processor_info');
    $this->setCacheBackend($cache_backend, 'viewer_processor');
  }

}
