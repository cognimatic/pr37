<?php

namespace Drupal\viewer;

/**
 * Provides an interface for helpers that operate on uploads/imports.
 *
 * @ingroup viewer
 */
interface ViewerInterface {

  /**
   * File upload directory (public).
   */
  const PUBLIC_URI = 'public://viewer';

  /**
   * File upload directory (private).
   */
  const PRIVATE_URI = 'private://viewer';

}
