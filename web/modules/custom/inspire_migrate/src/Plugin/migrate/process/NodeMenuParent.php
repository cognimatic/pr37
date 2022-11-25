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
      $mlid = $result->p1;
    }
    $service_id = NULL;
    # ** NEEDS UPDATE OF SOURCE MLIDs PRIOR TO FINAL MIGRATION**

    switch ($mlid) {
      case '1127':
        $service_id = 40;
        # Housing
        break;
      case '1128':
        $service_id = 49;
        # Education and learning
        break;
      case '1129':
        $service_id = 64;
        #ABC
        break;
      case '1130':
        $service_id = 70;
        # Recreation and leisure
        break;
      case '1131':
        $service_id = 24;
        # Business
        break;
      case '1132':
        $service_id = 29;
        # Roads and travel
        break;
      case '1133':
        $service_id = 35;
        # Social care and health
        break;
      case '1134':
        $service_id = 44;
        # Planning and building
        break;
      case '1311':
        $service_id = 58;
        # Birth, death, marriage
        break;
      case '1313':
        $service_id = 54;
        # Waste and recycling
        break;
      case '1325':
        $service_id = 59;
        # Environment
        break;
      case '1350':
        $service_id = 53;
        # Council tax and benefits
        break;
      case '1399':
        $service_id = 67;
        # Community
        break;
      case '1409':
        $service_id = 28;
        # Law and licensing
        break;
    }
    
    return $service_id;
  }

}
