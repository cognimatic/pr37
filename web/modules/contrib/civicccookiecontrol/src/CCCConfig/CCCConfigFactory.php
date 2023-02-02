<?php

namespace Drupal\civiccookiecontrol\CCCConfig;

/**
 * Cookie control configuration object factory.
 */
class CCCConfigFactory {

  /**
   * Get cookie control config object.
   *
   * @param int $version
   *   Cookie control api key version.
   *
   * @return \Drupal\civiccookiecontrol\CCCConfig\CCC8Config|\Drupal\civiccookiecontrol\CCCConfig\CCC9Config
   *   Cookie control configuration object.
   */
  public static function getCccConfig($version) {
    if ($version == 8) {
      return \Drupal::service('civiccookiecontrol.CCC8Config');
    }
    elseif ($version == 9) {
      return \Drupal::service('civiccookiecontrol.CCC9Config');
    }
  }

}
