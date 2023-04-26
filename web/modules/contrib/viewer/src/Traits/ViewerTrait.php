<?php

namespace Drupal\viewer\Traits;

/**
 * Viewer entity trait.
 */
trait ViewerTrait {

  /**
   * {@inheritdoc}
   */
  public function getViewerPluginId() {
    return $this->get('viewer_plugin')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setViewerPluginId($plugin_id) {
    $this->set('viewer_plugin', $plugin_id);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getViewerSource() {
    if (!$this->get('viewer_source')->isEmpty()) {
      if ($entity = $this->get('viewer_source')->first()->get('entity')->getTarget()) {
        return $entity->getValue();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setViewerSource($viewer_source) {
    $this->set('viewer_source', $viewer_source);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getViewerPlugin() {
    try {
      return \Drupal::service('plugin.manager.viewer')->createInstance($this->getViewerPluginId());
    }
    catch (\Exception $e) {
      \Drupal::logger('viewer')->error($e->getMessage());
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCellPlugin($cell_plugin_id) {
    try {
      return \Drupal::service('plugin.manager.viewer_cell')->createInstance($cell_plugin_id);
    }
    catch (\Exception $e) {
      \Drupal::logger('viewer')->error($e->getMessage());
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration($configuration = []) {
    $this->set('configuration', serialize($configuration));
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return !empty($this->get('configuration')->value) ? unserialize($this->get('configuration')->value, ['allowed_classes' => FALSE]) : [];
  }

  /**
   * {@inheritdoc}
   */
  public function setFilters($filters = []) {
    $this->set('filters', serialize($filters));
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getFilters() {
    return !empty($this->get('filters')->value) ? unserialize($this->get('filters')->value, ['allowed_classes' => FALSE]) : [];
  }

  /**
   * {@inheritdoc}
   */
  public function getDataAsArray($split_headers = TRUE) {
    return $this->getViewerPlugin()->getProcessPlugin()->getDataAsArray($this, $split_headers);
  }

}
