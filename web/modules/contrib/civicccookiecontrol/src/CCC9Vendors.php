<?php

namespace Drupal\civiccookiecontrol;

/**
 * Enum class storing vendor options.
 */
abstract class CCC9Vendors {

  /**
   * Territorial Scope 'value' => 'title'.
   */
  const TERRITORIAL_SCOPE = [
    'BE' => 'BE - Belgium',
    'BG' => 'BG - Bulgaria',
    'CZ' => 'CZ - Czech Republic',
    'DK' => 'DK - Denmark',
    'DE' => 'DE - Germany',
    'EE' => 'EE - Estonia',
    'IE' => 'IE - Ireland',
    'GR' => 'GR - Greece',
    'ES' => 'ES - Spain',
    'FR' => 'FR - France',
    'HR' => 'HR - Croatia',
    'IS' => 'IS - Iceland',
    'IT' => 'IT - Italy',
    'CY' => 'CY - Cyprus',
    'LV' => 'LV - Latvia',
    'LI' => 'LI - Liechtenstein',
    'LT' => 'LT - Lithuania',
    'LU' => 'LU - Luxembourg',
    'HU' => 'HU - Hungary',
    'MT' => 'MT - Malta',
    'NL' => 'NL - The Netherlands',
    'NO' => 'NO - Norway',
    'AT' => 'AT - Austria',
    'PL' => 'PL - Poland',
    'PT' => 'PT - Portugal',
    'RO' => 'RO - Romania',
    'SI' => 'SI - Slovenia',
    'SK' => 'SK - Slovakia',
    'FI' => 'FI - Finland',
    'SE' => 'SE - Sweden',
    'CH' => 'CH - Switzerland',
    'GB' => 'GB - United Kingdom',
  ];

  /**
   * Environments in array.
   */
  const ENVIRONMENTS = [
    'Web',
    'Native App (Mobile)',
    'Native App (CTV)',
    'Other',
  ];

  /**
   * Service Types in array.
   */
  const SERVICE_TYPES = [
    'SSP',
    'DSP',
    'Verification',
    'Ad serving',
    'Header bidding',
    'DMP / Data provider',
    'Identity resolution services',
    'Content delivery network',
    'Recommendation service',
    'Website analytics',
    'Buyer',
    'Campaign Analytics',
    'Audience Analytics',
    'Other',
  ];

}
