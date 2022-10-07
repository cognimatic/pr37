<?php

namespace Drupal\media_link_enhancements;

/**
 * Interface for the class that provides helper functions.
 */
interface MediaLinkEnhancementsHelperInterface {

  /**
   * Determines if a bundle is allowed.
   *
   * @param string $bundle
   *   The bundle being checked.
   * @param string $config_key
   *   The config key for the allowed bundle list.
   *
   * @return bool
   *   If the bundle is in the allowed list, TRUE.
   *   Otherwise, FALSE.
   */
  public function checkBundle($bundle, $config_key);

  /**
   * Determines if an extension is allowed.
   *
   * @param string $ext
   *   The extension being checked.
   * @param string $config_key
   *   The config key for the allowed extensions list.
   *
   * @return bool
   *   If the extension is in the allowed list, TRUE.
   *   Otherwise, FALSE.
   */
  public function checkExtension($ext, $config_key);

}
