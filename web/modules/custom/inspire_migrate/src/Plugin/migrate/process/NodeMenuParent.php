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
        7105 => 25, #Business Rates
        7387 => 26, #Trading standards
        6046 => 30, #Public transport
        8353 => 31, #Roads and maintenance
        7393 => 36, #Care and carers
        6187 => 37, #Help for adults
        6844 => 38, #Help for older and disabled people
        6184 => 39, #Help for young people
        7373 => 41, #Aids and adaptations
        7375 => 42, #Housing advice
        6223 => 43, #Private housing
        6379 => 45, #Planning
        7396 => 46, #Building standards
        6745 => 48, #Renewables and regeneration
        6592 => 50, #Schools
        6610 => 50,
        7378 => 50,
        6604 => 50,
        6619 => 50,
        7393 => 51, #Early learning and childcare
        6595 => 52, #Adult learning
        7384 => 52,
        7933 => 54, #Waste and recycling
        7993 => 56, #Bin collections
        5956 => 57, #Commercial waste
        7969 => 60, #Environmental health
        6997 => 62, #Rural
        7969 => 63, #Animals and animal welfare
        6115 => 65, #Elections
        6109 => 66, #Emergencies and civil contingencies
        8263 => 70 #Recreation and leisure
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
