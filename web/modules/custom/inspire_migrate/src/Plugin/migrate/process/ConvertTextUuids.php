<?php

namespace Drupal\inspire_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\Core\Database\Database;

/**
 * @MigrateProcessPlugin(
 *   id = "convert_text_uuids"
 * )
 */
class ConvertTextUuids extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($html, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    $document = new \DOMDocument;
    libxml_use_internal_errors(true);
    $document->loadHTML($html);
    // Suppress errors with HTML5 elements (this is a PHP bug)
    libxml_clear_errors();
    $xpath = new \DOMXPath($document);

    // Find Source DB
    // Find relevant migrations
    $tables = ['im_media_doc', 'im_media_image'];

    // Append SQL table prefix
    foreach ($tables AS $key => $table) {
      $tables[$key] = 'migrate_map_' . $table;
    }

    // Parse source HTML and replace UUIDs
    foreach ($xpath->query('//drupal-media') as $html_node) {
      $source_uuid = $html_node->getAttribute('data-entity-uuid');
      $dest_uuid = '';
      if (!empty($source_uuid)) {
        // Lookup source Media ID from UUID on source DB
        $source_mid = NULL;
        $source_db = Database::getConnection('default', 'migrate');
        $source_results = $source_db->query("select mid from media where uuid = :id", array(':id' => $source_uuid));
        foreach ($source_results AS $result) {
          $source_mid = $result->mid;
        }

        // Lookup MID on destination (default) DB via migration maps
        if (isset($source_mid)) {
          $dest_mid = NULL;
          foreach ($tables AS $table) {

            $dest_db = \Drupal::database();
            // Can't use table name as parameterised query/bound variable FML
            $query = "select destid1 from " . $table . " where sourceid1 = " . $source_mid;
            $dest_results = $dest_db->query($query);
            foreach ($dest_results AS $dest_result) {
              $mid_result = $dest_result->destid1;
              if (!empty($mid_result)) {
                $dest_mid = $mid_result;
              }
            }
          }
        }
        
        // Use destination MID to lookup UUID in destination (default) DB
        if (isset($dest_mid)) {      
          $final_db = \Drupal::database();
          $dest_uuid_results = $final_db->query("select uuid from media where mid = :mid", array(':mid' => $dest_mid));
          foreach ($dest_uuid_results as $dest_uuid_result) {
            $dest_uuid = $dest_uuid_result->uuid;
          }
        } else {
          \Drupal::logger('inspire_migrate')->notice('Media not available in destination. Source UUID: ' . $source_uuid . ' Source MID: ' . $source_mid);
        }
      }
      $html = str_replace($source_uuid, $dest_uuid, $html);
    }



    return $html;
  }

}
