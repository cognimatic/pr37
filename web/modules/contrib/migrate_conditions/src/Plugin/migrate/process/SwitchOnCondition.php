<?php

namespace Drupal\migrate_conditions\Plugin\migrate\process;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add a switch statement to the process pipeline.
 *
 * The syntax is intended to invoke the php switch statement,
 * but each case here evaluates a condition, which can be
 * more complicated than a static value. Only the first condition
 * that is met will be used. It is as if there is a "break" included
 * with each case by default.
 *
 * Available configuration keys:
 * - cases: An array of arrays. Each case should contain the
 *   following properties:
 *   - condition: The condition plugin to evaluate for the case.
 *     Can be either:
 *     - The id of the condition. This is possible if the condition does not
 *       require any configuration, such as the 'empty' condition.
 *     - An array with a 'plugin' key that is the id of the condition.
 *       Any additional properties will be used as configuration when
 *       creating an instance of the condition.
 *   Exactly one of the following must be specified for each case:
 *   - default_value: A string literal to return if the case is met
 *   - get: A source or destination property to get and return if
 *     the case is met.
 *   - process: A process pipeline to run if the case is met. If the
 *     source is not specified, then the source of switch_on_condition
 *     will be used.
 *
 * Examples:
 *
 * Determine if a value is less then, equal to, or greater than 5.
 * Note that we use the `default` condition for our last case in
 * order to save some typing and to keep this semantic.
 *
 * @code
 * process:
 *   comparison_to_five:
 *     plugin: switch_on_condition
 *     source: my_source
 *     cases:
 *       -
 *         condition:
 *           plugin: less_than
 *           value: 5
 *         default_value: 'less than 5'
 *       -
 *         condition:
 *           plugin: equals
 *           value: 5
 *         default_value: 'equal to 5'
 *       -
 *         condition: default
 *         default_value: 'greater than 5'
 * @endcode
 *
 * If the source is an array, use return the first value. If the
 * source is empty, return a different source property. Otherwise,
 * return the source without modification.
 *
 * Note how we can give the cases meaningful array keys to make
 * the pipeline more readable.
 *
 * @code
 * process:
 *   destination_value:
 *     plugin: switch_on_condition
 *     source: my_source
 *     cases:
 *       'source is an array':
 *         condition:
 *           plugin: callback
 *           callable: is_array
 *         # return the first value in the array
 *         process:
 *           plugin: extract
 *           index:
 *             - 0
 *       'source is empty':
 *         condition: empty
 *         # return a default value
 *         get: different_source_value
 *       'use the source':
 *         condition: default
 *         # return the source
 *         get: my_source
 * @endcode
 *
 * @MigrateProcessPlugin(
 *   id = "switch_on_condition",
 *   handle_multiples = TRUE
 * )
 */
class SwitchOnCondition extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Flag indicating whether there are multiple values.
   *
   * @var bool
   */
  protected $multiple;

  /**
   * Array of cases which include a condition and something to do.
   *
   * @var array[]
   */
  protected $cases = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PluginManagerInterface $condition_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    if (!isset($configuration['cases'])) {
      throw new \InvalidArgumentException("The 'cases' configuration is required.");
    }
    if (!is_array($configuration['cases'])) {
      throw new \InvalidArgumentException("The 'cases' configuration must be an array.");
    }
    foreach ($configuration['cases'] as $case) {
      if (!is_array($case)) {
        throw new \InvalidArgumentException("Each item in the 'cases' array must be an array");
      }
      if (!isset($case['condition'])) {
        throw new \InvalidArgumentException("Each item in the 'cases' array must have a 'condition' configured.");
      }
      if (is_string($case['condition'])) {
        $case['condition'] = $condition_manager->createInstance($case['condition'], []);
      }
      elseif (is_array($case['condition'])) {
        if (!isset($case['condition']['plugin'])) {
          throw new \InvalidArgumentException("The 'plugin' must be set for the condition.");
        }
        else {
          $plugin_id = $case['condition']['plugin'];
          unset($case['condition']['plugin']);
          $case['condition'] = $condition_manager->createInstance($plugin_id, $case['condition']);
        }
      }
      $keys = [
        'get',
        'process',
        'default_value',
      ];
      $count = 0;
      foreach ($keys as $key) {
        if (array_key_exists($key, $case)) {
          $count++;
        }
      }
      if ($count !== 1) {
        throw new \InvalidArgumentException("Each item in the 'cases' must configure exactly one of 'get', 'process', and 'default_value'.");
      }
      if (isset($case['get']) && !is_string($case['get'])) {
        throw new \InvalidArgumentException("The value of a case's 'get' property must be a string.");
      }
      if (isset($case['process']) && !is_array($case['process'])) {
        throw new \InvalidArgumentException("The value of a case's 'process' property must be an array.");
      }
      $this->cases[] = $case;
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

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $return = NULL;
    foreach ($this->cases as $case) {
      if ($case['condition']->evaluate($value, $row)) {
        if (isset($case['get'])) {
          $return = $row->get($case['get']);
        }
        elseif (isset($case['process'])) {
          $return = $this->doConditionalProcess($case['process'], $value, $migrate_executable, $row, $destination_property);
        }
        else {
          $return = $case['default_value'];
        }
        break;
      }
    }
    // Ideally, if a conditional process is run, we would set multiple
    // based on the final plugin in the conditional process pipeline.
    // We don't have access to that value, however, so we naively set
    // multiple based on whether we are returning an array. Most plugins
    // that return an array set multiple to true anyway, but there are
    // at least some (see array_build) that return an array that is
    // supposed to be considered a single value. In a situation such as
    // that, the if_condition plugin should be followed by a single_value
    // plugin provided by migrate_plus.
    $this->multiple = is_array($return);
    return $return;
  }

  /**
   * Runs the process pipeline for a case.
   *
   * @param string|array $process_config
   *   The process pipeline for the case.
   * @param mixed $value
   *   The value to be transformed.
   * @param \Drupal\migrate\MigrateExecutableInterface $migrate_executable
   *   The migration in which this process is being executed.
   * @param \Drupal\migrate\Row $row
   *   The row from the source to process. Normally, just transforming the value
   *   is adequate but very rarely you might need to change two columns at the
   *   same time or something like that.
   * @param string $destination_property
   *   The destination property currently worked on. This is only used together
   *   with the $row above.
   *
   * @return mixed
   *   Whatever the result is.
   */
  protected function doConditionalProcess($process_config, $value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $conditional_process[$destination_property] = $process_config;
    // Clone the row so we can use ::processRow without affecting it.
    $dummy_row = clone($row);

    // Determine which notation for the process pipleine is being used.
    if (array_key_exists(0, $conditional_process[$destination_property])) {
      // Numerical keys. Likely multiple process plugins in the pipeline.
      $first_process =& $conditional_process[$destination_property][0];
    }
    else {
      $first_process =& $conditional_process[$destination_property];
    }
    // If the source is not set, we make a temp value on the dummy row.
    if (!isset($first_process['source'])) {
      $dummy_row->setDestinationProperty('_switch_temp', $value);
      $first_process['source'] = '@_switch_temp';
    }
    $migrate_executable->processRow($dummy_row, $conditional_process);
    return $dummy_row->getDestinationProperty($destination_property);
  }

  /**
   * {@inheritdoc}
   */
  public function multiple() {
    return $this->multiple;
  }

}
