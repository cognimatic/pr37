<?php

namespace Drupal\abc_migration\Plugin\migrate\process;

use Drupal\migrate\Row;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;

/**
 * Returns latitude/longitude pair given a d7 location field value.
 *
 * @MigrateProcessPlugin(
 *   id = "abc_migration_extract_location_latlon"
 * )
 */
class ExtractLocationLatLon extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    $lid = $value['lid'];
    // Connect to the database defined by key 'migrate'.
    $db = \Drupal\Core\Database\Database::getConnection('default', 'migrate');
    $location = $db->select('location', 'l')
      ->where('l.lid = ' . $lid)
      ->fields('l', array(
        'latitude',
        'longitude'
      ))
      ->execute()
      ->fetchAssoc();

    return array_values($location);
  }


}
