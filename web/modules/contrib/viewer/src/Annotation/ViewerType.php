<?php

namespace Drupal\viewer\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines ViewerType annotation object.
 *
 * Plugin Namespace: Plugin\viewer\Plugin\ViewerType.
 *
 * @see \Drupal\viewer\Plugin\ViewerTypeManager
 * @see plugin_api
 *
 * @Annotation
 */
class ViewerType extends Plugin {

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
   * Associative array of extensions => mime type.
   *
   * @var array
   */
  public $extensions = [];

  /**
   * Default viewer plugin ID.
   *
   * @var string
   */
  public $default_viewer;

}
