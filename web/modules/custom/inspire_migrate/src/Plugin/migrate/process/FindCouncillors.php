<?php

namespace Drupal\inspire_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\Core\Database\Database;

/**
 * @MigrateProcessPlugin(
 *   id = "find_councillors"
 * )
 */
class FindCouncillors extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($location, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $councillors = (array) null;
    $dest_db = \Drupal::database();
    $results = $dest_db->query("SELECT entity_id, revision_id FROM paragraph__field_location_temp WHERE field_location_temp_value = :location", [':location' => $location]);    
    foreach ($results AS $result) {
      $councillors[] = [$result->entity_id, $result->revision_id];     
    }
    return $councillors;
  }

}
