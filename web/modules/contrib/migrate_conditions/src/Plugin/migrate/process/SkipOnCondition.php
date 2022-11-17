<?php

namespace Drupal\migrate_conditions\Plugin\migrate\process;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\migrate\MigrateSkipProcessException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate_conditions\Plugin\ProcessPluginWithConditionBase;

/**
 * Skips processing when the input value matches a condition.
 *
 * Available configuration keys:
 * - method: What to do if the condition is met. Possible values:
 *   - row: Skips the entire row when an empty value is encountered.
 *   - process: Prevents further processing of the input property when the value
 *     is empty.
 * - condition: The condition plugin to evaluate on each element.
 *   Can be either:
 *   - The id of the condition. This is possible if the condition does not
 *     require any configuration, such as the 'empty' condition.
 *   - An array with a 'plugin' key that is the id of the condition.
 *     Any additional properties will be used as configuration when
 *     creating an instance of the condition.
 * - message: (optional) A message to be logged in the {migrate_message_*} table
 *   for this row. Messages are only logged for the 'row' method. If not set,
 *   nothing is logged in the message table.
 * - message_context: (optional) One or more source or destination properties to
 *   be used in the message. If message_context is set, the message will be
 *   passed as the first argument to sprintf, with the values of the properties
 *   specified in message_context passed as the subsequent arguments to sprintf.
 *
 * Examples:
 *
 * Skip the row if created_on holds a date more than a month old.
 * In the example, the created_on date has the form 'j M Y'.
 *
 * @code
 * process:
 *   skip_old_stuff:
 *     plugin: skip_on_condition
 *     source: created_on
 *     condition:
 *       plugin: older_than
 *       format: 'j M Y'
 *       value: '-1 month'
 *     method: row
 *     message: 'That content was created on %s. It is sooooo last month.'
 *     message_context:
 *       - created_on
 * @endcode
 *
 * @MigrateProcessPlugin(
 *   id = "skip_on_condition",
 *   handle_multiples = TRUE
 * )
 */
class SkipOnCondition extends ProcessPluginWithConditionBase {

  /**
   * Flag indicating whether there are multiple values.
   *
   * @var bool
   */
  protected $multiple;

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
    parent::__construct($configuration, $plugin_id, $plugin_definition, $condition_manager);
    $methods = ['row', 'process'];
    if (!isset($configuration['method']) || !in_array($configuration['method'], $methods, TRUE)) {
      throw new \InvalidArgumentException('The "method" must be set to either "row" or "process".');
    }
    // Test that the message syntax is valid, if present.
    if (isset($configuration['message']) && isset($configuration['message_context'])) {
      try {
        $testing = sprintf($configuration['message'], ...(array) $configuration['message_context']);
      }
      catch (\Throwable $e) {
        throw new \InvalidArgumentException('The message and/or message_context configuration are invalid: ' . $e->getMessage());
      }
    }
  }

  /**
   * Skips the current row when value is not set.
   *
   * @param mixed $value
   *   The input value.
   * @param \Drupal\migrate\MigrateExecutableInterface $migrate_executable
   *   The migration in which this process is being executed.
   * @param \Drupal\migrate\Row $row
   *   The row from the source to process.
   * @param string $destination_property
   *   The destination property currently worked on. This is only used together
   *   with the $row above.
   *
   * @return mixed
   *   The input value, $value, if it is not empty.
   *
   * @throws \Drupal\migrate\MigrateSkipRowException
   *   Thrown if the source property is not set and the row should be skipped,
   *   records with STATUS_IGNORED status in the map.
   */
  public function row($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if ($this->condition->evaluate($value, $row)) {
      $message = !empty($this->configuration['message']) ? $this->configuration['message'] : '';
      $message_context = isset($this->configuration['message_context']) ? (array) $this->configuration['message_context'] : NULL;
      if ($message && $message_context) {
        $values = [];
        foreach ($message_context as $property) {
          $values[] = $row->get($property);
        }
        throw new MigrateSkipRowException(sprintf($message, ...$values));
      }
      else {
        throw new MigrateSkipRowException($message);
      }
    }
    $this->multiple = is_array($value);
    return $value;
  }

  /**
   * Stops processing the current property when value is not set.
   *
   * @param mixed $value
   *   The input value.
   * @param \Drupal\migrate\MigrateExecutableInterface $migrate_executable
   *   The migration in which this process is being executed.
   * @param \Drupal\migrate\Row $row
   *   The row from the source to process.
   * @param string $destination_property
   *   The destination property currently worked on. This is only used together
   *   with the $row above.
   *
   * @return mixed
   *   The input value, $value, if it is not empty.
   *
   * @throws \Drupal\migrate\MigrateSkipProcessException
   *   Thrown if the source property is not set and rest of the process should
   *   be skipped.
   */
  public function process($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if ($this->condition->evaluate($value, $row)) {
      throw new MigrateSkipProcessException();
    }
    $this->multiple = is_array($value);
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function multiple() {
    return $this->multiple;
  }

}
