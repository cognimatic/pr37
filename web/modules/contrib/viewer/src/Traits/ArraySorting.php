<?php

namespace Drupal\viewer\Traits;

/**
 * Array sorting class.
 */
trait ArraySorting {

  /**
   * Move operations link to the top.
   */
  protected function moveOperationUp($array, $index) {
    $result[$index] = $array[$index];
    unset($array[$index]);
    return array_merge($result, $array);
  }

  /**
   * Move operations link to the bottom.
   */
  protected function moveOperationBottom($array, $index) {
    $result[$index] = $array[$index];
    unset($array[$index]);
    return array_merge($array, $result);
  }

}
