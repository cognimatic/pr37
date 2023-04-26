<?php

namespace Drupal\viewer\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines Viewer annotation object.
 *
 * Plugin Namespace: Plugin\viewer\Plugin\Viewer.
 *
 * @see \Drupal\viewer\Plugin\ViewerManager
 * @see plugin_api
 *
 * @Annotation
 */
class Viewer extends Plugin {

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
   * Does this plugin work without the viewer source.
   *
   * @var bool
   */
  public $empty_viewer_source;

  /**
   * Array of supported Viewer Types.
   *
   * @var array
   */
  public $viewer_types = [];

  /**
   * Data processor plugin ID.
   *
   * @var string
   */
  public $processor;

  /**
   * Does this plugin support filters.
   *
   * @var bool
   */
  public $filters;

}
