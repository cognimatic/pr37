<?php

namespace Drupal\viewer\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\viewer\Entity\Viewer;

/**
 * Defines an interface Viewer plugins.
 */
interface ViewerInterface extends PluginInspectionInterface {

  /**
   * Return the name of the Viewer plugin.
   */
  public function getName();

  /**
   * Sets the Viewer entity.
   *
   * @param \Drupal\viewer\Entity\Viewer $viewer
   *   The Viewer entity.
   *
   * @return \Drupal\viewer\Plugin\ViewerInterface
   *   The called Viewer plugin.
   */
  public function setViewer(Viewer $viewer);

  /**
   * Gets the Viewer entity.
   *
   * @return \Drupal\viewer\Plugin\ViewerInterface
   *   The called Viewer plugin.
   */
  public function getViewer();

}
