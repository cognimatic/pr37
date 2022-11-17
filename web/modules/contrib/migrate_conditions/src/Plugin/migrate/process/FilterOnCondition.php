<?php

namespace Drupal\migrate_conditions\Plugin\migrate\process;

use Drupal\migrate\MigrateException;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate_conditions\Plugin\ProcessPluginWithConditionBase;

/**
 * Filters an input array on a condition.
 *
 * This works like php's array_filter function. We keep the values that meet the
 * condition and throw away the values that do not meet the condition.
 *
 * Available configuration keys:
 * - condition: The condition plugin to evaluate on each element.
 *   Can be either:
 *   - The id of the condition. This is possible if the condition does not
 *     require any configuration, such as the 'empty' condition.
 *   - An array with a 'plugin' key that is the id of the condition.
 *     Any additional properties will be used as configuration when
 *     creating an instance of the condition.
 * - preserve_keys: (optional) Set to TRUE to preserve array keys. Defaults to
 *   FALSE.
 *
 * Examples:
 *
 * Filter field_timestamps so that we keep upcoming dates and remove
 * dates that are in the past.
 *
 * @code
 * process:
 *   upcoming_dates:
 *     plugin: filter_on_condition
 *     source: field_timestamps
 *     condition:
 *       plugin: not:older_than
 *       format: 'U'
 *       value: 'now'
 * @endcode
 *
 * @MigrateProcessPlugin(
 *   id = "filter_on_condition",
 *   handle_multiples = TRUE
 * )
 */
class FilterOnCondition extends ProcessPluginWithConditionBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (!is_array($value)) {
      throw new MigrateException("The input value should be an array.");
    }
    $return = [];
    foreach ($value as $key => $val) {
      if ($this->condition->evaluate($val, $row)) {
        $return[$key] = $val;
      }
    }
    if (isset($this->configuration['preserve_keys']) && $this->configuration['preserve_keys']) {
      return $return;
    }
    else {
      return array_values($return);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function multiple() {
    return TRUE;
  }

}
