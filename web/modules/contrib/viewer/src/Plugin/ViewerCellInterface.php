<?php

namespace Drupal\viewer\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface ViewerCell plugins.
 */
interface ViewerCellInterface extends PluginInspectionInterface {

  /**
   * Return the name of the ViewerCell plugin.
   */
  public function getName();

  /**
   * Convert Viewer Cell value before output.
   */
  public function convert($value, $row);

}
