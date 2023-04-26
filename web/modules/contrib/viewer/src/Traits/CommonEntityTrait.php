<?php

namespace Drupal\viewer\Traits;

/**
 * Common for Viewer and Viewer Source entities.
 */
trait CommonEntityTrait {

  /**
   * Get entity name.
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * Set entity name.
   */
  public function setName($title) {
    $this->set('name', $title);
    return $this;
  }

  /**
   * Get entity created timestamp.
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * Set entity created timestamp.
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * Check if entity is published.
   */
  public function isPublished() {
    return !empty($this->get('status')->value);
  }

  /**
   * Set entity as active.
   */
  public function setActive() {
    $this->set('status', 1);
    return $this;
  }

  /**
   * Set entity as inactive.
   */
  public function setInactive() {
    $this->set('status', 0);
    return $this;
  }

  /**
   * Set entity settings.
   */
  public function setSettings($settings = []) {
    $this->set('settings', serialize($settings));
    return $this;
  }

  /**
   * Get entity settings.
   */
  public function getSettings() {
    $settings = !empty($this->get('settings')->value) ? unserialize($this->get('settings')->value, ['allowed_classes' => FALSE]) : [];
    return $settings;
  }

  /**
   * Get individual entity setting.
   */
  public function getSetting($setting) {
    $settings = $this->getSettings();
    return !empty($settings[$setting]) ? $settings[$setting] : [];
  }

  /**
   * Merge settings.
   */
  public function mergeIntoSettings($merge_settings) {
    $settings = array_merge($this->getSettings(), $merge_settings);
    $this->set('settings', serialize($settings));
    return $this;
  }

}
