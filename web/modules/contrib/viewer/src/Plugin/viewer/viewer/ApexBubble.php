<?php

namespace Drupal\viewer\Plugin\viewer\viewer;

/**
 * Viewer plugin.
 *
 * @Viewer(
 *   id = "apexchart_bubble",
 *   name = @Translation("ApexCharts.js: Bubble"),
 *   provider = "viewer",
 *   processor = "processor_csv",
 *   filters = true,
 *   viewer_types = {
 *     "csv"
 *   }
 * )
 */
class ApexBubble extends ApexScatter {

  /**
   * {@inheritdoc}
   */
  public function getRenderable() {
    $build = parent::getRenderable();
    $build['#wrapper'] = 'scatterbubble';
    $build['#type'] = 'bubble';
    return $build;
  }

}
