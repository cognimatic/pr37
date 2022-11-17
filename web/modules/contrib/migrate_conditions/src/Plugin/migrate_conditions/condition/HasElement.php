<?php

namespace Drupal\migrate_conditions\Plugin\migrate_conditions\condition;

use Drupal\migrate\Row;
use Drupal\migrate_conditions\Plugin\ArrayConditionBase;

/**
 * Provides a 'has_element' condition.
 *
 * Evaluates the configured condition on each array element (or on a
 * single element with the specified index) and returns TRUE if at least
 * one element meets the condition.
 *
 * Available configuration keys:
 * - condition: The condition plugin to evaluate on each element.
 *   Can be either:
 *   - The id of the condition. This is possible if the condition does not
 *     require any configuration, such as the 'empty' condition.
 *   - An array with a 'plugin' key that is the id of the condition.
 *     Any additional properties will be used as configuration when
 *     creating an instance of the condition.
 * - index: (optional) If set, the condition will only be evaluated on the
 *   array element with this index/key.
 * - negate: (optional) Whether to negate the has_element condition.
 *   Defaults to FALSE. You can also negate the 'has_element' plugin by using
 *   'not:has_element' as the plugin id.
 *
 * Example:
 *
 * Skip a row if at least one date in the array source_dates is too old.
 *
 * @code
 * skip_old_stuff:
 *   plugin: skip_on_condition
 *   condition:
 *     plugin: has_element
 *     condition:
 *       plugin: older_than
 *       format: 'U'
 *       value: -1 week'
 *   method: row
 *   source: source_dates
 * @endcode
 *
 * Determine if an input array has the 'alt' key set.
 *
 * @code
 * has_alt_text:
 *   plugin: evaluate_condition
 *   source: my_source_array
 *   condition:
 *     plugin: has_element
 *     index: 'alt'
 *     condition: isset
 * @endcode
 *
 * @MigrateConditionsConditionPlugin(
 *   id = "has_element",
 *   requires = {"condition"}
 * )
 */
class HasElement extends ArrayConditionBase {

  /**
   * {@inheritdoc}
   */
  protected function doEvaluate($source, Row $row) {
    $source = (array) $source;
    if (isset($this->configuration['index'])) {
      $source_value = $source[$this->configuration['index']] ?? NULL;
      return $this->condition->evaluate($source_value, $row);
    }
    else {
      foreach ($source as $source_value) {
        if ($this->condition->evaluate($source_value, $row)) {
          return TRUE;
        }
      }
      return FALSE;
    }
  }

}
