<?php

namespace Drupal\migrate_conditions\Plugin\migrate_conditions\condition;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate\Row;
use Drupal\migrate_conditions\Plugin\ConditionBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an 'entity_exists' condition.
 *
 * Available configuration keys:
 * - entity_type: The machine name of the entity type.
 * - negate: (optional) Whether the 'entity_exists' condition should be negated.
 *   Defaults to FALSE. You can also negate the 'entity_exists' plugin by using
 *   'not:entity_exists' as the plugin id.
 * - source: (optional) Property or array of properties on which to evaluate
 *   the condition. If not set, the condition will be evaluated on the source
 *   passed to the ::evaluate() method, typically the source of the process
 *   plugin that is using this condition.
 *
 * Examples:
 *
 * Skip a process if field_tid does not correspond to an existing term.
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: skip_on_condition
 *     source: field_tid
 *     condition:
 *       plugin: entity_exists
 *       negate: true
 *       entity_type: taxonomy_term
 *     method: process
 * @endcode
 *
 * Using "not:" notation and parens notation this could be written as:
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: skip_on_condition
 *     source: field_tid
 *     condition: not:entity_exists(taxonomy_term)
 *     method: process
 * @endcode
 *
 * @MigrateConditionsConditionPlugin(
 *   id = "entity_exists",
 *   requires = {"entity_type"},
 *   parens = "entity_type"
 * )
 */
class EntityExists extends ConditionBase implements ContainerFactoryPluginInterface {

  /**
   * The entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * EntityExists constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    try {
      $this->storage = $entity_type_manager->getStorage($configuration['entity_type']);
    }
    catch (\Exception $e) {
      throw new \InvalidArgumentException('The entity_type configured for entity_exists could not be loaded.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function doEvaluate($source, Row $row) {
    if (!is_int($source) && !is_string($source)) {
      throw new MigrateException("The source value for entity_exists must be an integer or a string.");
    }
    $entity = $this->storage->load($source);
    if ($entity instanceof EntityInterface) {
      return TRUE;
    }
    return FALSE;
  }

}
