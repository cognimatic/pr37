<?php

namespace Drupal\viewer\Traits;

/**
 * Plugin batch trait.
 */
trait PluginBatchTrait {

  /**
   * Batch parameters.
   *
   * @var array
   */
  protected $batch = [];

  /**
   * Set import frequency.
   */
  public function setImportFrequency($import_frequency = 0) {
    $this->batch['import_frequency'] = (int) $import_frequency;
    return $this;
  }

  /**
   * Get import frequency.
   */
  public function getImportFrequency() {
    return $this->batch['import_frequency'] ?? 0;
  }

  /**
   * Set file source.
   */
  public function setBatchFileSource($file_source = 'file') {
    $this->batch['file_source'] = $file_source;
    return $this;
  }

  /**
   * Get file source.
   */
  public function getBatchFileSource() {
    return $this->batch['file_source'] ?? '';
  }

  /**
   * Set batch file.
   */
  public function setBatchFile($file_id) {
    $this->batch['file'] = $file_id;
    return $this;
  }

  /**
   * Get batch file.
   */
  public function getBatchFile() {
    return $this->batch['file'] ?? FALSE;
  }

  /**
   * Set batch settings.
   */
  public function setBatchSettings($settings) {
    $this->batch['settings'] = $settings;
    return $this;
  }

  /**
   * Get batch settings.
   */
  public function getBatchSettings() {
    return $this->batch['settings'] ?? [];
  }

  /**
   * Set batch viewer source entity.
   */
  public function setBatchViewerSourceEntity($viewer_source) {
    $this->batch['viewer_source'] = $viewer_source;
    return $this;
  }

  /**
   * Get batch viewer source entity.
   */
  public function getBatchViewerSourceEntity() {
    return $this->batch['viewer_source'] ?? NULL;
  }

  /**
   * Set addditional fields.
   */
  public function setAdditionalFields($additional_fields = []) {
    $this->batch['additional_fields'] = $additional_fields;
    return $this;
  }

  /**
   * Get additional fields.
   */
  public function getAdditionalFields() {
    return $this->batch['additional_fields'] ?? NULL;
  }

}
