<?php

namespace Drupal\abc_migration\Plugin\migrate\process;

use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\migrate_file_to_media\Plugin\migrate\process\FileIdLookup;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Throw a MigrateSkipRowException if file field is hidden, otherwise lookup media from file.
 *
 * @MigrateProcessPlugin(
 *   id = "file_id_lookup_skip_hidden"
 * )
 */
class FileIdLookupSkipHidden extends FileIdLookup {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    if (array_key_exists('display', $value) && empty($value['display'])) {
      throw new MigrateSkipProcessException("File is hidden.");
    }

    return parent::transform($value, $migrate_executable, $row, $destination_property);
  }

}
