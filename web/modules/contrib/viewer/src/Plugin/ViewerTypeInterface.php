<?php

namespace Drupal\viewer\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface ViewerTypeInterface plugins.
 */
interface ViewerTypeInterface extends PluginInspectionInterface {

  /**
   * Return the name of the ViewerTypeInterface plugin.
   */
  public function getName();

  /**
   * Return list of extensions.
   */
  public function getExtensions();

  /**
   * Return list of extensions in a single array element.
   */
  public function getExtensionsAsValidator();

}
