<?php

namespace Drupal\viewer\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;

/**
 * Process Viewer Sources import tasks.
 *
 * @QueueWorker(
 *   id = "viewer_sources",
 *   title = @Translation("Viewer Soruces"),
 *   cron = {"time" = 180}
 * )
 */
class ViewerSourcesQueueProcessor extends QueueWorkerBase {

  /**
   * {@inheritdoc}
   */
  public function processItem($plugin) {
    $context = [];
    \Drupal::service('viewer.batch')->upload($plugin, $context);
  }

}
