<?php

namespace Drupal\viewer\Plugin\viewer\type;

use Drupal\viewer\Plugin\ViewerTypeBase;
use Drupal\file\Entity\File;

/**
 * Viewer Type plugin.
 *
 * @ViewerType(
 *   id = "pdf",
 *   name = @Translation("PDF"),
 *   default_viewer = "pdfjs",
 *   extensions = {
 *     "application/pdf" = "pdf",
 *   },
 * )
 */
class Pdf extends ViewerTypeBase {

  /**
   * {@inheritdoc}
   */
  public function getContentAsArray(File $file, $settings = []) {
    return [];
  }

}
