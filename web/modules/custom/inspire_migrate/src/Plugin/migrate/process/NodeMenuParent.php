<?php

namespace Drupal\inspire_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\Core\Database\Database;

/**
 * @MigrateProcessPlugin(
 *   id = "node_menu_parent"
 * )
 */
class NodeMenuParent extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($nid, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    $mlid = NULL;

    // Find Source DB
    $source_db = Database::getConnection('default', 'migrate');

    $query = $source_db->select('menu_tree', 'm')
        ->fields('m', ['p1'])
        ->condition('route_param_key', "node=" . $nid);
    $source_results = $query->execute();
    foreach ($source_results AS $result) {
      // If multiple menu links for a node, only last hit used
      $mlid = $result->p1;
    }
       
    return $mlid;
  }

}
