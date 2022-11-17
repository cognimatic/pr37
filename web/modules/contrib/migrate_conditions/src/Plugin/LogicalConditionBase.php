<?php

namespace Drupal\migrate_conditions\Plugin;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a base class for conditions that compare other conditions.
 *
 * Plugins extending this class allow logical combinations of multiple
 * conditions.
 *
 * Plugins extending this class require that configuration 'conditions' be an
 * array of arrays, each with the following available keys.
 * - plugin_id: The id of the condition.
 * - negate: (optional) A boolean flag that indicates whether condition result
 *   should be negated. Defaults to FALSE.
 * - Additional configuration keys such as 'strict' may be applicable for
 *   certain plugins.
 *
 * Plugins extending this class may also set an 'iterate' property. The default
 * value is FALSE. If 'iterate' is FALSE, Each condition is evaluated on the
 * source value. If 'iterate' is TRUE, then each condition is evaluated on the
 * element in the source that has the corresponding index/key. Thus, when
 * 'iterate' is TRUE, the source must be an array.
 */
abstract class LogicalConditionBase extends ConditionBase implements ContainerFactoryPluginInterface {

  /**
   * The condition plugin array.
   *
   * @var \Drupal\migrate_conditions\ConditionInterface[]
   */
  protected $conditions;

  /**
   * Set to true to iterate.
   *
   * @var bool
   */
  protected $iterate;

  /**
   * Constructs a Has object.
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
    if (!isset($configuration['conditions'])) {
      throw new \InvalidArgumentException("The $plugin_id condition requires 'conditions' be passed as configuration.");
    }
    if (!is_array($configuration['conditions'])) {
      throw new \InvalidArgumentException("The 'conditions' passed to the $plugin_id condition must be an array or arrays.");
    }
    foreach ($configuration['conditions'] as $index => $condition) {
      if (!is_array($condition)) {
        throw new \InvalidArgumentException("The 'conditions' passed to the $plugin_id condition must be an array or arrays.");
      }
      if (!isset($condition['plugin'])) {
        throw new \InvalidArgumentException("Each condition element passed to the $plugin_id condition must have the 'plugin' set.");
      }
      $sub_plugin_id = $condition['plugin'];
      unset($condition['plugin']);
      $this->conditions[$index] = $condition_manager->createInstance($sub_plugin_id, $condition);
    }
    $this->iterate = isset($this->configuration['iterate']) && $this->configuration['iterate'] === TRUE;
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

  /**
   * {@inheritdoc}
   */
  public function evaluate($source, Row $row) {
    if ($this->iterate && !is_array($source)) {
      throw new MigrateException("If the 'iterate' property is true, the source must be an array.");
    }
    return ($this->doEvaluate($source, $row) xor $this->negated);
  }

}
