<?php

namespace Drupal\inspire_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\Core\Database\Database;

/**
 * @MigrateProcessPlugin(
 *   id = "geo_code_address"
 * )
 */
class GeoCodeAddress extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($address, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $provider_ids = [ 'localgov_os_places', 'localgov_default_osm'];
    if (!empty($address)) {
      // $providers =  \Drupal::entityTypeManager()->getStorage('geocoder_provider')->loadMultiple();
      // temporary fudge
      //$provider_ids = ['localgov_default_osm'];
      //$provider_ids = ['localgov_os_places'];
      $providers = \Drupal::entityTypeManager()->getStorage('geocoder_provider')->loadMultiple($provider_ids);

      try {
        $addressCollection = \Drupal::service('geocoder')->geocode($address, $providers);
      } catch (RequestException $e) {
        watchdog_exception('geolocation', $e);
        return NULL;
      }
      if (isset($addressCollection)) {
        $location = [];

        $lat = $addressCollection->first()->getCoordinates()->getLatitude();
        $lon = $addressCollection->first()->getCoordinates()->getLongitude();

        $plat = number_format((float) $lat, 5, '.');
        $plon = number_format((float) $lon, 5, '.');

        $location[0]['value'] = "POINT (" . $plon . " " . $plat . ")";
        $location[0]['geo_type'] = "Point";
        $location[0]['lat'] = $lat;
        $location[0]['top'] = $lat;
        $location[0]['bottom'] = $lat;
        $location[0]['lon'] = $lon;
        $location[0]['left'] = $lon;
        $location[0]['right'] = $lon;

        // \Drupal::logger('inspire_migrate')->notice('Lat: ' . $lat . ' Longitude: ' . $lon);

        return $location;
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
