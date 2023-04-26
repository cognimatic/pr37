<?php

namespace Drupal\viewer\Traits;

/**
 * Temporary storage class.
 */
trait TempKeyValTrait {

  /**
   * Set value.
   */
  protected function setKeyVal($key, $val) {
    $keys = $this->store->get('keystodelete') ?? [];
    $keys[$key] = 1;
    $this->store->set('keystodelete', $keys);
    $this->store->set($key, $val);
    return $this;
  }

  /**
   * Get value.
   */
  protected function getKeyVal($key, $default = NULL) {
    return !empty($this->store->get($key)) ? $this->store->get($key) : $default;
  }

  /**
   * Delete data from temporary storage.
   */
  protected function deleteKeyVal() {
    $keys = !empty($this->store->get('keystodelete')) ? array_keys($this->store->get('keystodelete')) : [];
    foreach ($keys as $key) {
      $this->store->delete($key);
    }
  }

}
