<?php

namespace Drupal\migrate_drupal_d8\Plugin\migrate\source\d8;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;

/**
 * Drupal 8 user source from database.
 *
 * @MigrateSource(
 *   id = "d8_user",
 *   source_provider = "migrate_drupal_d8"
 * )
 */
class User extends ContentEntity {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, StateInterface $state, EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager) {
    $configuration['entity_type'] = 'user';
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $state, $entity_type_manager, $entity_field_manager);
  }
/**
   * {@inheritdoc}
   *
   * Define role field.
   */
  public function fields() {
    $fields = parent::fields();
    $fields['roles'] = $this->t('Roles');
    return $fields;
  }

  /**
   * {@inheritdoc}
   *
   * Add roles values.
   */
  public function prepareRow(Row $row) {
    $entityId = $row->getSourceProperty('uid');
    $roles = $this->getFieldValues('user', 'roles', $entityId);
    $row->setSourceProperty('roles', $roles);
    return parent::prepareRow($row);
  }

}
