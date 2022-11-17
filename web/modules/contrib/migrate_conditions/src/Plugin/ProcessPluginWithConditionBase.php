<?php

namespace Drupal\migrate_conditions\Plugin;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\ProcessPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class to be used by MigrateProcess plugins that rely on a condition.
 *
 * Available configuration keys:
 * - condition: The condition plugin to evaluate on each element.
 *   Can be either:
 *   - The id of the condition. This is possible if the condition does not
 *     require any configuration, such as the 'empty' condition.
 *   - An array with a 'plugin' key that is the id of the condition.
 *     Any additional properties will be used as configuration when
 *     creating an instance of the condition.
 */
abstract class ProcessPluginWithConditionBase extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The condition plugin.
   *
   * @var \Drupal\migrate_conditions\ConditionInterface
   */
  protected $condition;

  /**
   * Constructs a ProcessPluginWithConditionBase object.
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
