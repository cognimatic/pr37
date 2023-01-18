<?php

namespace Drupal\inspire_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\Core\Database\Database;

/**
 * @MigrateProcessPlugin(
 *   id = "rev_geocode_address"
 * )
 */
class ReverseCodeAddress extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($address, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $provider_ids = ['localgov_os_places', 'localgov_default_osm'];
    if (!empty($address)) {
      // $providers =  \Drupal::entityTypeManager()->getStorage('geocoder_provider')->loadMultiple();
      // temporary fudge
      // $provider_ids = ['localgov_default_osm'];
      $providers = \Drupal::entityTypeManager()->getStorage('geocoder_provider')->loadMultiple($provider_ids);

      try {
        $addressCollection = \Drupal::service('geocoder')->geocode($address, $providers);
      } catch (RequestException $e) {
        watchdog_exception('geolocation', $e);
        return NULL;
      }

      if (isset($addressCollection)) {
        $postal_address = [];
        $postal_address[0]['address_line1'] = $addressCollection->first()->getStreetName();
        $postal_address[0]['address_line2'] = $addressCollection->first()->getSubLocality();
        $postal_address[0]['locality'] = $addressCollection->first()->getLocality();
        $postal_address[0]['postal_code'] = $addressCollection->first()->getPostalCode();
        $postal_address[0]['country_code'] = $addressCollection->first()->getCountry()->getCode();

        // \Drupal::logger('inspire_migrate')->notice('Lat: ' . $lat . ' Longitude: ' . $lon);

        return $postal_address;
      }
      else {
        return NULL;
      }
    }
    else {
      return NULL;
    }
  }

}
