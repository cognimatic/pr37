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
    $parent_id = NULL;

    // Find Source DB
    $source_db = Database::getConnection('default', 'migrate');

    // Check if node has a p2 (sub parent)
    $query2 = $source_db->select('menu_tree', 'm')
        ->fields('m', ['p2'])
        ->condition('route_param_key', "node=" . $nid);
    $p2_results = $query2->execute();
    foreach ($p2_results AS $result) {
      // If multiple menu links for a node, only last hit used
      $mlid = $result->p2;
    }

    // If MLID set, then find LGD Services ID
    if (isset($mlid)) {
      $map = array(
        1420 => 25, #Business Rates
        1421 => 26, #Trading standards
        1454 => 30, #Public transport
        1453 => 31, #Roads and maintenance
        1448 => 36, #Care and carers
        1449 => 37, #Help for adults
        1452 => 38, #Help for older and disabled people
        1447 => 39, #Help for young people
        1436 => 41, #Aids and adaptations
        1433 => 42, #Housing advice
        1434 => 43, #Private housing
        1431 => 45, #Planning
        1457 => 46, #Building standards
        1459 => 48, #Renewables and regeneration
        1477 => 50, #Schools
        1521 => 50,
        1522 => 50,
        1443 => 50,
        1519 => 50,
        1481 => 51, #Early learning and childcare
        1445 => 52, #Adult learning
        1427 => 52,
        1518 => 56, #Bin collections
        1441 => 57, #Commercial waste
        1438 => 60, #Environmental health
        1492 => 62, #Rural
        1440 => 63, #Animals and animal welfare
        1496 => 65, #Elections
        1487 => 66, #Emergencies and civil contingencies
        1423 => 70 #Recreation and leisure
      );

      $parent_id = (isset($map[$mlid])) ? $map[$mlid] : NULL;
    }



    // If no valid sub-parent (p2) found, then find p1 (top level parent)
    if (is_null($parent_id)) {
      $query1 = $source_db->select('menu_tree', 'm')
          ->fields('m', ['p1'])
          ->condition('route_param_key', "node=" . $nid);
      $p1_results = $query1->execute();
      foreach ($p1_results AS $result) {
        // If multiple menu links for a node, only last hit used
        $mlid = $result->p1;
      }
      
      # ** NEEDS UPDATE OF SOURCE MLIDs PRIOR TO FINAL MIGRATION**
      #  TOP LEVEL SERVICES
      #  40 Housing 
      #  49 Education and learning
      #  64 ABC
      #  70 Recreation and leisure
      #  24 Business
      #  29 Roads and travel
      #  35 Social care and health
      #  44 Planning and building
      #  58 Birth, death, marriage
      #  54 Waste and recycling
      #  59 Environment
      #  53 Council tax and benefits
      #  67 Community
      #  28 Law and licensing 

      // If MLID set, then find LGD Services ID
      if (isset($mlid)) {
        $map = array(
          1127 => 40,
          1128 => 49,
          1129 => 64,
          1130 => 70,
          1131 => 24,
          1132 => 29,
          1133 => 35,
          1134 => 44,
          1311 => 58,
          1313 => 54,
          1325 => 59,
          1350 => 53,
          1399 => 67,
          1409 => 28
        );
        
        $parent_id = (isset($map[$mlid])) ? $map[$mlid] : NULL;
      }
    }

    return $parent_id;
  }

}
