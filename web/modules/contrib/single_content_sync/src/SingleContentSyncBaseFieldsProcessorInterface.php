<?php

namespace Drupal\single_content_sync;

use Drupal\Core\Entity\FieldableEntityInterface;

/**
 * Interface for single_content_sync_base_fieldss_processor plugins.
 */
interface SingleContentSyncBaseFieldsProcessorInterface {

  /**
   * Returns the translated plugin label.
   *
   * @return string
   *   The translated title.
   */
  public function label(): string;

  /**
   * Exports the base values to a serializable format.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The entity to export.
   *
   * @return array
   *   The exported field value.
   */
  public function exportBaseValues(FieldableEntityInterface $entity): array;

  /**
   * Do a mapping between entity base fields and exported content.
   *
   * @param array $values
   *   Original exported values of base fields.
   *
   * @return array
   *   Correct field mapping with exported values.
   */
  public function mapBaseFieldsValues(array $values): array;

}
