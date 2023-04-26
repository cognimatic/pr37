<?php

namespace Drupal\viewer\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines ViewerCell annotation object.
 *
 * Plugin Namespace: Plugin\viewer\Plugin\ViewerCell.
 *
 * @see \Drupal\viewer\Plugin\ViewerCellManager
 * @see plugin_api
 *
 * @Annotation
 */
class ViewerCell extends Plugin {

  /**
   * Plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * Plugin name.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $name;

  /**
   * Applicable list of viewers.
   *
   * @var array
   */
  public $viewers = [];

}
