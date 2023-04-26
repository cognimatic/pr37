<?php

namespace Drupal\viewer\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * ViewerCell plugin manager.
 *
 * @package viewer
 */
class ViewerCellManager extends DefaultPluginManager {

  /**
   * Constructs an ViewerCellManager object.
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
    parent::__construct('Plugin/viewer/cell', $namespaces, $module_handler, 'Drupal\viewer\Plugin\ViewerCellInterface', 'Drupal\viewer\Annotation\ViewerCell');
    $this->alterInfo('viewer_cell_info');
    $this->setCacheBackend($cache_backend, 'viewer_cell');
  }

}
