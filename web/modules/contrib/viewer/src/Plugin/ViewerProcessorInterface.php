<?php

namespace Drupal\viewer\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\viewer\Entity\ViewerInterface;

/**
 * Defines an interface ViewerProcessorInterface plugins.
 */
interface ViewerProcessorInterface extends PluginInspectionInterface {

  /**
   * Get data as array (for CSV, XLSX etc).
   */
  public function getDataAsArray(ViewerInterface $viewer, $split_headers);

}
