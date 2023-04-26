<?php

namespace Drupal\viewer\Traits;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\file\Entity\File;

/**
 * Viewer Source entity trait.
 */
trait ViewerSourceTrait {

  /**
   * Get source plugin ID.
   */
  public function getSourcePluginId() {
    return $this->get('source_plugin')->value;
  }

  /**
   * Set source plugin ID.
   */
  public function setSourcePluginId($plugin_id) {
    $this->set('source_plugin', $plugin_id);
    return $this;
  }

  /**
   * Get type plugin ID.
   */
  public function getTypePluginId() {
    return $this->get('type_plugin')->value;
  }

  /**
   * Set type plugin ID.
   */
  public function setTypePluginId($plugin_id) {
    $this->set('type_plugin', $plugin_id);
    return $this;
  }

  /**
   * Get source plugin.
   */
  public function getSourcePlugin() {
    try {
      return \Drupal::service('plugin.manager.viewer_source')->createInstance($this->getSourcePluginId());
    }
    catch (\Exception $e) {
      \Drupal::logger('viewer')->error($e->getMessage());
      return FALSE;
    }
  }

  /**
   * Get type plugin.
   */
  public function getTypePlugin() {
    try {
      return \Drupal::service('plugin.manager.viewer_type')->createInstance($this->getTypePluginId());
    }
    catch (\Exception $e) {
      \Drupal::logger('viewer')->error($e->getMessage());
      return FALSE;
    }
  }

  /**
   * Get file size.
   */
  public function getReadableFileSize($dec = 2) {
    if ($file = $this->getFile()) {
      $bytes  = $file->getSize();
      $size   = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
      $factor = floor((strlen($bytes) - 1) / 3);
      return sprintf("%.{$dec}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }
  }

  /**
   * Get frequency labels.
   */
  public function getReadableFrequency() {
    $frequencies = viewer_import_frequencies();
    $import_frequency = $this->get('import_frequency')->value;
    return !empty($frequencies[$import_frequency]) ? $frequencies[$import_frequency] : $import_frequency;
  }

  /**
   * Get frequency value.
   */
  public function getFrequency() {
    return (int) $this->get('import_frequency')->value;
  }

  /**
   * Set frequency value.
   */
  public function setFrequency($import_frequency = 0) {
    $this->set('import_frequency', (int) $import_frequency);
    return $this;
  }

  /**
   * Get last import timestamp.
   */
  public function getLastImportRaw() {
    return (int) $this->get('last_import')->value;
  }

  /**
   * Get last import datetime.
   */
  public function getLastImport() {
    return !empty($this->getLastImportRaw()) ? \Drupal::service('date.formatter')->format($this->getLastImportRaw()) : '';
  }

  /**
   * Get next import datetime.
   */
  public function getNextImport() {
    return !empty($this->get('next_import')->value) ? \Drupal::service('date.formatter')->format($this->get('next_import')->value) : '';
  }

  /**
   * Set last import timestamp.
   */
  public function setLastImport() {
    $this->set('last_import', \Drupal::time()->getCurrentTime());
    return $this;
  }

  /**
   * Set next import timestamp.
   */
  public function setNextImport($edited_frequency = FALSE) {
    $current_timestamp = \Drupal::time()->getCurrentTime();
    if ($edited_frequency) {
      if ($last_import = $this->get('last_import')->value) {
        if ($current_timestamp < $last_import + $edited_frequency) {
          $this->set('next_import', $last_import + $edited_frequency);
        }
        else {
          $this->set('next_import', $current_timestamp + $edited_frequency);
        }
      }
    }
    else {
      if ($frequency = $this->getFrequency()) {
        $this->set('next_import', $current_timestamp + $frequency);
      }
      else {
        $this->set('next_import', NULL);
      }
    }
    return $this;
  }

  /**
   * Set metadata.
   */
  public function setMetadata($metadata) {
    return $this->set('metadata', serialize($metadata));
  }

  /**
   * Get metadata.
   */
  public function getMetadata() {
    $metadata = !empty($this->get('metadata')->value) ? unserialize($this->get('metadata')->value, ['allowed_classes' => FALSE]) : [];
    return $metadata;
  }

  /**
   * Get file contents.
   */
  public function getContents() {
    if ($file = $this->getFile()) {
      return file_get_contents($file->getFileUri());
    }
  }

  /**
   * Set file.
   */
  public function setFile($file) {
    // We need to make sure older file is set as temporary so it gets removed.
    if ($previous_file = $this->getFile()) {
      $previous_file->setTemporary();
      $previous_file->save();
    }
    if ($file) {
      $file->setPermanent();
      $file->save();
      $this->set('file_id', $file->id());
    }
    else {
      $this->set('file_id', NULL);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    if ($file_id = $this->get('file_id')->target_id) {
      return File::load($file_id);
    }
  }

  /**
   * Get contents as array.
   */
  public function getContentAsArray() {
    $array = [];
    $cid = 'viewer_source:' . $this->id();
    if ($cache = \Drupal::cache('data')->get($cid)) {
      $array = $cache->data;
    }
    else {
      if ($file = $this->getFile()) {
        if ($array = $this->getTypePlugin()->getContentAsArray($file, $this->getSettings())) {
          \Drupal::cache('data')->set($cid, $array, CacheBackendInterface::CACHE_PERMANENT, [
            'viewer_source',
            'viewer_source:' . $this->id(),
          ]);
        }
      }
    }
    return $array;
  }

}
