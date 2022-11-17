<?php

namespace Drupal\migrate_conditions\Plugin\migrate\process;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate_conditions\Plugin\ProcessPluginWithConditionBase;

/**
 * Get configured properties based on result of a condition.
 *
 * Available configuration keys:
 * - condition: The condition plugin to evaluate on each element.
 *   Can be either:
 *   - The id of the condition. This is possible if the condition does not
 *     require any configuration, such as the 'empty' condition.
 *   - An array with a 'plugin' key that is the id of the condition.
 *     Any additional properties will be used as configuration when
 *     creating an instance of the condition.
 * - do_get: (optional) property to get and return if condition is met. If not
 *   set, the source will be used.
 * - else_get: (optional) property to get and return if condition is not met. If
 *   not set, NULL will be returned.
 * - do_process: (optional) a process pipeline to run if the condition is met.
 *   Only one of do_get and do_process may be set.
 * - else_process: (optional) a process pipeline to run if the condition is not met.
 *   Only one of else_get and else_process may be set.
 *
 * Examples:
 *
 * If animal_family is 'bird', get feather_color. Otherwise, get
 * fur_color.
 *
 * @code
 * process:
 *   animal_color:
 *     plugin: if_condition
 *     source: animal_family
 *     condition:
 *       plugin: equals
 *       value: bird
 *     do_get: feather_color
 *     else_get: fur_color
 * @endcode
 *
 * The if_condition process plugin can work like the default_value
 * process plugin but with a source value as the default. For example,
 * use field_backup if field_i_want_to_use is empty.
 *
 * @code
 * process:
 *   destination_value:
 *     plugin: if_condition
 *     source: field_i_want_to_use
 *     condition:
 *       plugin: empty
 *       negate: true
 *     else_get: field_backup
 * @endcode
 *
 * or equivalently:
 *
 * @code
 * process:
 *   destination_value:
 *     plugin: if_condition
 *     source: field_i_want_to_use
 *     condition: empty
 *     do_get: field_backup
 *     else_get: field_i_want_to_use
 * @endcode
 *
 * Do a migrate lookup if a source value is netiher 0 nor 1.
 *
 * @code
 * process:
 *   uid:
 *     plugin: if_condition
 *     source: uid
 *     condition:
 *       plugin: in_array
 *       value:
 *         - 0
 *         - 1
 *     else_process:
 *       plugin: migrate_lookup
 *       migration: users
 * @endcode
 *
 * Note that if you do not manually define the source for the else_process
 * of do_process pipeline we automatically use the source from the if_condition
 * plugin.
 *
 * @MigrateProcessPlugin(
 *   id = "if_condition",
 *   handle_multiples = TRUE
 * )
 */
class IfCondition extends ProcessPluginWithConditionBase {

  /**
   * Flag indicating whether there are multiple values.
   *
   * @var bool
   */
  protected $multiple;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PluginManagerInterface $condition_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $condition_manager);
    if (isset($configuration['do_get']) && isset($configuration['do_process'])) {
      throw new \InvalidArgumentException("You may only set one of 'do_get' and 'do_process'.");
    }
    if (isset($configuration['else_get']) && isset($configuration['else_process'])) {
      throw new \InvalidArgumentException("You may only set one of 'else_get' and 'else_process'.");
    }
    if (isset($configuration['do_process']) && !is_array($configuration['do_process'])) {
      throw new \InvalidArgumentException("The 'do_process' configuration must be an array.");
    }
    if (isset($configuration['else_process']) && !is_array($configuration['else_process'])) {
      throw new \InvalidArgumentException("The 'else_process' configuration must be an array.");
    }
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $return = NULL;
    if ($this->condition->evaluate($value, $row)) {
      if (isset($this->configuration['do_get'])) {
        $return = $row->get($this->configuration['do_get']);
      }
      elseif (isset($this->configuration['do_process'])) {
        $return = $this->doConditionalProcess('do_process', $value, $migrate_executable, $row, $destination_property);
      }
      else {
        $return = $value;
      }
    }
    else {
      if (isset($this->configuration['else_get'])) {
        $return = $row->get($this->configuration['else_get']);
      }
      elseif (isset($this->configuration['else_process'])) {
        $return = $this->doConditionalProcess('else_process', $value, $migrate_executable, $row, $destination_property);
      }
      else {
        $return = NULL;
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
   * Runs the do_process or else_process pipeline.
   *
   * @param string $config_key
   *   Either do_process or else_process.
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
  protected function doConditionalProcess($config_key, $value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $conditional_process[$destination_property] = $this->configuration[$config_key];
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
      $dummy_row->setDestinationProperty('_if_condition_temp', $value);
      $first_process['source'] = '@_if_condition_temp';
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
