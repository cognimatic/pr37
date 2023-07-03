<?php

namespace Drupal\civiccookiecontrol\Form\Steps;

/**
 * Form steps enum.
 */
abstract class CCCStepsEnum {

  const CCC_LICENSE_INFO = 1;
  const CCC_SETTINGS = 2;

  /**
   * Key steps array.
   *
   * @return string[]
   *   Steps array.
   */
  public static function toArray() {
    return [
      self::CCC_LICENSE_INFO => 'ccc_license_info',
      self::CCC_SETTINGS => 'ccc_settings',
    ];
  }

  /**
   * Map step enum to step class.
   *
   * @param string $step
   *   Step enum value.
   *
   * @return bool|string
   *   Step key of false.
   */
  public static function map($step) {
    $map = [
      self::CCC_LICENSE_INFO => 'civiccookiecontrol.CCCLicenseInfo',
      self::CCC_SETTINGS => 'civiccookiecontrol.CCCSettings',
    ];

    return $map[$step] ?? FALSE;
  }

}
