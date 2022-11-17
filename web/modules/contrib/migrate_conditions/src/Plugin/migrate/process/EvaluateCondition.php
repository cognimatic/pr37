<?php

namespace Drupal\migrate_conditions\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate_conditions\Plugin\ProcessPluginWithConditionBase;

/**
 * Evaluates a condition.
 *
 * Available configuration keys:
 * - condition: The condition plugin to evaluate on each element.
 *   Can be either:
 *   - The id of the condition. This is possible if the condition does not
 *     require any configuration, such as the 'empty' condition.
 *   - An array with a 'plugin' key that is the id of the condition.
 *     Any additional properties will be used as configuration when
 *     creating an instance of the condition.
 *
 * Examples:
 *
 * Determine if source_value is null.
 *
 * @code
 * process:
 *   is_null:
 *     plugin: evaluate_condition
 *     condition: is_null
 *     source: source_value
 * @endcode
 *
 * Set as unpublished if event_date is in the past.
 *
 * @code
 * process:
 *   status:
 *     plugin: evaluate_condition
 *     condition:
 *       plugin: older_than
 *       negate: true
 *       format: 'U'
 *       value: 'now'
 *     source: event_date
 * @endcode
 *
 * @MigrateProcessPlugin(
 *   id = "evaluate_condition",
 *   handle_multiples = TRUE
 * )
 */
class EvaluateCondition extends ProcessPluginWithConditionBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    return $this->condition->evaluate($value, $row);
  }

}
