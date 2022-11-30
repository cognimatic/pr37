<?php

namespace Drupal\inspire_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\Core\Database\Database;
/**
 * Uses either str_replace() or preg_replace() function on a source string.
 *
 * Available configuration keys:
 * - insert: The text to be added to the source string. 
 * - place: Whether the inserted string is "before" or "after" - default is 
 *   "after" 
 *
  *
 * To do a simple hardcoded string replace, use the following:
 * @code
 * field_text:
 *   plugin: add_string
 *   source: text
 *   insert: "My text string"
 *   place: before
 * @endcode
 */



/**
 * @MigrateProcessPlugin(
 *   id = "add_string"
 * )
 */
class AddString extends ProcessPluginBase {
  
  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    if (!isset($configuration['insert'])) {
      throw new \InvalidArgumentException('The "insert" must be set.');
    }
    if (!isset($configuration['place'])) {
      throw new \InvalidArgumentException('The "place" must be set.');
    }

    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $insert = $this->configuration['insert'];
    $end = $this->configuration['place'];
    if($end == 'before'){
    $return = $insert . $value;
    } else {
      $return = $value .  $insert;
    }
    return $return;
  }

  

}
