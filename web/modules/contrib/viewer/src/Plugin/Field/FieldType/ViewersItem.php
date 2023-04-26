<?php

namespace Drupal\viewer\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'viewer' field type.
 *
 * @FieldType(
 *   id = "viewer",
 *   label = @Translation("Viewer"),
 *   default_widget = "viewer",
 *   default_formatter = "viewer"
 * )
 */
class ViewersItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['target_id'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Viewer'));
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function mainPropertyName() {
    return 'target_id';
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema['columns'] = [
      'target_id' => [
        'type' => 'int',
        'not null' => FALSE,
      ],
    ];
    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $target_id = $this->get('target_id')->getValue();
    return $target_id === NULL || $target_id === '';
  }

}
