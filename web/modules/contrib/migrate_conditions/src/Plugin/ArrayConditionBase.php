<?php

namespace Drupal\migrate_conditions\Plugin;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a base class for conditions.
 *
 * Plugins extending this class evaluate conditions on elements of a
 * source array, rather than the source as a whole.
 *
 * Plugins extending this class require that configuration 'condition' be
 * either
 *   - a string that is the id of a condition requiring no configuration.
 *   - an array with where 'plugin' is id of a condtion and additional
 *     keys are configuration to be passed to the condition.
 */
abstract class ArrayConditionBase extends ConditionBase implements ContainerFactoryPluginInterface {

  /**
   * The condition plugin.
   *
   * @var \Drupal\migrate_conditions\ConditionInterface
   */
  protected $condition;

  /**
   * Constructs an ArrayConditionBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Component\Plugin\PluginManagerInterface $condition_manager
   *   The MigrateCondition plugin manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PluginManagerInterface $condition_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    if (!isset($configuration['condition'])) {
      throw new \InvalidArgumentException("The 'condition' must be set.");
    }
    if (is_string($configuration['condition'])) {
      $this->condition = $condition_manager->createInstance($configuration['condition'], []);
    }
    elseif (is_array($configuration['condition'])) {
      if (!isset($configuration['condition']['plugin'])) {
        throw new \InvalidArgumentException("The 'plugin' must be set for the condition.");
      }
      else {
        $plugin_id = $configuration['condition']['plugin'];
        unset($configuration['condition']['plugin']);
        $this->condition = $condition_manager->createInstance($plugin_id, $configuration['condition']);
      }
    }
    else {
      throw new \InvalidArgumentException("The 'condition' must be either a string or an array.");
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.migrate_conditions.condition')
    );
  }

}
