<?php

namespace Drupal\viewer\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines ViewerSource annotation object.
 *
 * Plugin Namespace: Plugin\viewer\Plugin\ViewerSource.
 *
 * @see \Drupal\viewer\Plugin\ViewerSourceManager
 * @see plugin_api
 *
 * @Annotation
 */
class ViewerSource extends Plugin {

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
   * Plugin can run on cron.
   *
   * @var bool
   */
  public $cron;

}
