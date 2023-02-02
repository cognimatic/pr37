<?php

namespace Drupal\civiccookiecontrol\Form;

use Drupal\civiccookiecontrol\CCCConfigNames;
use Drupal\Component\Serialization\Json;
use GuzzleHttp\Exception\RequestException;

/**
 * Utility class for various helper functions.
 */
class CCCFormHelper {

  /**
   * Calidate cookie control api key.
   *
   * @param string $apiKey
   *   The api key.
   * @param string $productType
   *   Product type.
   *
   * @return bool|int|string
   *   Result.
   */
  public static function validateApiKey($apiKey, $productType = NULL) {
    $ccc_licenses_type = [
      'COMMUNITY' => 'CookieControl%20Free',
      'PRO' => 'CookieControl%20Single-Site',
      'PRO_MULTISITE' => 'CookieControl%20Multi-Site',
    ];

    if (!empty($productType)) {
      if (self::checkValidity($apiKey, $ccc_licenses_type[$productType])) {
        return $productType;
      }
    }
    else {
      foreach ($ccc_licenses_type as $key => $licenseType) {
        if (self::checkValidity($apiKey, $licenseType)) {
          return $key;
        }
      }
    }

    return FALSE;
  }

  /**
   * Check api key validity.
   *
   * @param string $apiKey
   *   The apikey.
   * @param string $licenseType
   *   The license type.
   *
   * @return bool
   *   Return value.
   */
  protected static function checkValidity($apiKey, $licenseType) {
    $domain = \Drupal::request()->getHost();

    $client = \Drupal::httpClient();

    $queryString = '?d=' . $domain . '&p=' . $licenseType . '&v=' . \Drupal::config(CCCConfigNames::COOKIECONTROL)->get('civiccookiecontrol_api_key_version') . '&format=json&k=' . $apiKey;
    try {
      $request = $client->get("https://apikeys.civiccomputing.com/c/v" . $queryString);
      $respArray = Json::decode($request->getBody()->getContents());

      if ($respArray['valid'] == 1) {
        return TRUE;
      }
    }
    catch (RequestException $ex) {
      \Drupal::logger('civiccookiecontrol')->notice($ex->getMessage());
      return FALSE;
    }
    return FALSE;
  }

}
