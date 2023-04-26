<?php

namespace Drupal\viewer\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller for Viewer module.
 */
class SupportController extends ControllerBase {

  /**
   * Prints help page.
   */
  public function page() {
    $build = [
      '#theme' => 'viewer_support',
    ];
    return $build;
  }

}
