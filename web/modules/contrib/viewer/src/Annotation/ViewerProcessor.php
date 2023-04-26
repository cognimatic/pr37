<?php

namespace Drupal\viewer\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines ViewerProcessor annotation object.
 *
 * Plugin Namespace: Plugin\viewer\Plugin\ViewerProcessor.
 *
 * @see \Drupal\viewer\Plugin\ViewerProcessorManager
 * @see plugin_api
 *
 * @Annotation
 */
class ViewerProcessor extends Plugin {

  /**
   * Plugin ID.
   *
   * @var string
   */
  public $id;

}
