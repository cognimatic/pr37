<?php

namespace Drupal\viewer\Services;

use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Viewer Source cron imports.
 *
 * @ingroup viewer
 */
class Cron {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The queue object.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queue;

  /**
   * Initiates Cron service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, QueueFactory $queue) {
    $this->entityTypeManager = $entity_type_manager;
    $this->queue = $queue;
  }

  /**
   * Create and process Viewer Source import queues.
   */
  public function processQueue() {
    $current_timestamp = \Drupal::time()->getCurrentTime();
    $import_queue = $this->queue->get('viewer_sources');
    $ids = $this->entityTypeManager->getStorage('viewer_source')
      ->getQuery()
      ->condition('status', 1)
      ->condition('import_frequency', 0, '!=')
      ->condition('next_import', $current_timestamp, '<=')
      ->accessCheck(TRUE)
      ->execute();
    $entities = $this->entityTypeManager->getStorage('viewer_source')->loadMultiple($ids);
    foreach ($entities as $viewer_source) {
      $plugin = $viewer_source->getSourcePlugin();
      $settings = $viewer_source->getSettings();
      $plugin->setImportFrequency($viewer_source->getFrequency())
        ->setBatchSettings($settings)
        ->setBatchViewerSourceEntity($viewer_source)
        ->setBatchFile($settings['path'])
        ->setBatchFileSource($viewer_source->getSourcePluginId());
      $import_queue->createItem($plugin);
    }
  }

}
