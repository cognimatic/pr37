<?php

namespace Drupal\viewer\Services;

use Drupal\viewer\Event\ViewerEventType;
use Drupal\viewer\Event\ViewerEvent;
use Drupal\viewer\Entity\ViewerSource;

/**
 * Viewer Source Batch API.
 *
 * @ingroup viewer
 */
class Batch {

  /**
   * Upload CSV Data Viewer data.
   */
  public static function upload($plugin, &$context) {
    $viewer_source = $plugin->getBatchViewerSourceEntity();
    $settings = $plugin->getBatchSettings();
    $additional_fields = $plugin->getAdditionalFields();
    $dispatcher = \Drupal::service('event_dispatcher');

    if (empty($viewer_source)) {
      $viewer_source = ViewerSource::create([]);
      $viewer_source->setName($additional_fields['name']);
      $viewer_source->setSourcePluginId($additional_fields['source']);
      $viewer_source->setTypePluginId($additional_fields['type']);
    }

    try {
      $type_plugin = $viewer_source->getTypePlugin();

      if ($file = $plugin->getFile($plugin->getBatchFile(), $settings, $type_plugin, $plugin->getBatchFileSource())) {
        // Dispatching an event.
        $event_type = ViewerEventType::IMPORT_SUCCESS;
        $dispatcher->dispatch(new ViewerEvent($event_type, $viewer_source), $event_type);

        $viewer_source->setFrequency($plugin->getImportFrequency());
        $viewer_source->setSettings($settings);
        $viewer_source->setMetadata($type_plugin->getMetadata($file, $settings));
        $viewer_source->setFile($file);
        $viewer_source->setLastImport();
        $viewer_source->setNextImport();
        $viewer_source->save();
        $context['results'][] = $viewer_source->id();
        $context['message'] = t('Processing %name.', ['%name' => $viewer_source->label()]);
      }
      else {
        $event_type = ViewerEventType::IMPORT_FAILED;
        $dispatcher->dispatch(new ViewerEvent($event_type, $viewer_source), $event_type);
        $error_message = t('Unable to process %name.', ['%name' => $viewer_source->label()]);
        $context['message'] = $error_message;
        $context['results']['error'][] = $error_message;
      }
    }
    catch (\Exception $e) {
      $event_type = ViewerEventType::IMPORT_FAILED;
      $dispatcher->dispatch(new ViewerEvent($event_type, $viewer_source), $event_type);
      $context['message'] = $e->getMessage();
      $context['results']['error'][] = $e->getMessage();
    }
  }

  /**
   * BatchAPI complete callback for data import.
   */
  public static function completeImportCallback($success, $results, $operations) {
    if (!empty($results['error'])) {
      foreach ($results['error'] as $error_message) {
        self::complete($error_message, $error_message, FALSE, $results);
      }
    }
    else {
      self::complete('1 import processed.', '@count imports processed.', $success, $results);
    }
  }

  /**
   * Helper method for complete callbacks.
   */
  protected static function complete($singular, $plural, $success, $results) {
    if ($success) {
      $message = \Drupal::translation()->formatPlural(count($results), $singular, $plural);
      \Drupal::messenger()->addMessage($message, 'status', FALSE);
      \Drupal::logger('viewer')->notice($message);
    }
    else {
      \Drupal::messenger()->addWarning($singular, 'warning', FALSE);
      \Drupal::logger('viewer')->warning($singular);
    }
  }

  /**
   * Get file contents.
   */
  protected static function getFileContents($file) {
    return file_get_contents($file->getFileUri());
  }

}
