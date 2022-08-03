<?php

namespace Drupal\abc_migration\Plugin\migrate\process;

use Drupal\migrate\Row;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;

/**
 * Generates a media entity from a file and returns the media id.
 *
 * @MigrateProcessPlugin(
 *   id = "abc_migration_text_format_munge"
 * )
 */
class TextFormatMunge extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $new_value = $value;
    $map = array(
      2 => 'full_html',
      8 => 'plain_text',
      1 => 'basic_html',
      7 => 'xml',
      4 => 'javascript',
      3 => 'php',
      5 => 'styles_and_javascript',
      6 => 'url_corrector',
    );

    if (array_key_exists($value['format'], $map)) {
      $new_value['format'] = $map[$value['format']];
    }

    return $new_value;
  }


}
