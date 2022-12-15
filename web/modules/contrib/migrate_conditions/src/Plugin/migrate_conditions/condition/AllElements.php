<?php

namespace Drupal\migrate_conditions\Plugin\migrate_conditions\condition;

use Drupal\migrate\Row;
use Drupal\migrate_conditions\Plugin\ArrayConditionBase;

/**
 * Provides an 'all_elements' condition.
 *
 * Evaluates the configured condition on each array element and returns
 * TRUE if every element meets the condition.
 *
 * Available configuration keys:
 * - condition: The condition plugin to evaluate on each element.
 *   Can be either:
 *   - The id of the condition. This is possible if the condition does not
 *     require any configuration, such as the 'empty' condition.
 *   - An array with a 'plugin' key that is the id of the condition.
 *     Any additional properties will be used as configuration when
 *     creating an instance of the condition.
 * - negate: (optional) Whether to negate the all_elements condition.
 *   Defaults to FALSE. You can also negate the 'all_elements' plugin by using
 *   'not:all_elements' as the plugin id.
 * - source: (optional) Property or array of properties on which to evaluate
 *   the condition. If not set, the condition will be evaluated on the source
 *   passed to the ::evaluate() method, typically the source of the process
 *   plugin that is using this condition.
 *
 * Example:
 *
 * Skip a row if every date in the array source_dates is too old.
 *
 * @code
 * skip_old_stuff:
 *   plugin: skip_on_condition
 *   condition:
 *     plugin: all_elements
 *     condition:
 *       plugin: older_than
 *       format: 'U'
 *       value: -1 week'
 *   method: row
 *   source: source_dates
 * @endcode
 *
 * @MigrateConditionsConditionPlugin(
 *   id = "all_elements",
 *   requires = {"condition"}
 * )
 */
class AllElements extends ArrayConditionBase {

  /**
   * {@inheritdoc}
   */
  protected function doEvaluate($source, Row $row) {
    $source = (array) $source;
    if (empty($source)) {
      return FALSE;
    }
    foreach ($source as $source_value) {
      if (!($this->condition->evaluate($source_value, $row))) {
        return FALSE;
      }
    }
    return TRUE;
  }

}
