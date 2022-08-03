<?php

namespace Drupal\media_table_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\file\Plugin\Field\FieldFormatter\FileFormatterBase;
use Drupal\media\Entity\MediaType;

/**
 * Plugin implementation of the 'media_file_table' formatter.
 *
 * @FieldFormatter(
 *   id = "media_file_table",
 *   label = @Translation("Table of media files"),
 *   weight = 3,
 *   field_types = {
 *     "entity_reference"
 *   },
 *   media_handlers = {
 *     "file"
 *   }
 * )
 */
class MediaTableFormatter extends FileFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    if (parent::isApplicable($field_definition) && $field_definition->getSetting('target_type') == 'media') {
      /** @var MediaType[] $bundles */
      $allowed_types = MediaType::loadMultiple($field_definition->getSetting('handler_settings')['target_bundles']);

      $media_handlers = [];
      $definitions = \Drupal::service('plugin.manager.field.formatter')
        ->getDefinitions();
      foreach ($definitions as $definition) {
        if ($definition['class'] == static::class) {
          $media_handlers = $definition['media_handlers'];
        }
      }

      foreach ($allowed_types as $type) {
        /** @var \Drupal\media\Entity\MediaType $type */
        if (in_array($type->getSource()->getPluginId(), $media_handlers)) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }


  protected function needsEntityLoad(EntityReferenceItem $item) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    if ($entities = $items->referencedEntities()) {

      $header = [t('Attachment'), t('Size')];
      $rows = [];
      foreach ($entities as $delta => $entity) {
        $source_field_name = $entity->getSource()
          ->getConfiguration()['source_field'];
        /** @var \Drupal\file\FileInterface $file */
        $file = $entity->get($source_field_name)->entity;

        $rows[] = [
          [
            'data' => [
              '#theme' => 'file_link',
              '#file' => $file,
              '#description' => $entity->get($source_field_name)->description,
              '#cache' => [
                'tags' => $file->getCacheTags(),
              ],
            ],
          ],
          ['data' => format_size($file->getSize())],
        ];
      }

      $elements[0] = [];
      if (!empty($rows)) {
        $elements[0] = [
          '#theme' => 'table__file_formatter_table',
          '#header' => $header,
          '#rows' => $rows,
        ];
      }
    }

    return $elements;
  }

}

