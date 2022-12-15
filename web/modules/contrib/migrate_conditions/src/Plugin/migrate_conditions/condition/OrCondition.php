<?php

namespace Drupal\migrate_conditions\Plugin\migrate_conditions\condition;

use Drupal\migrate\Row;
use Drupal\migrate_conditions\Plugin\LogicalConditionBase;

/**
 * Provides an 'or' condition.
 *
 * This condition allows logical combinations of other conditions.
 *
 * Available configuration keys:
 * - conditions: An array of array. Each element must have a 'plugin' key
 *   that is the id of the condition. Any additional properties will be
 *   used as configuration when creating an instance of the condition.
 * - iterate: (optional) The default value is FALSE. If 'iterate' is FALSE,
 *   Each condition is evaluated on the source value. If 'iterate' is TRUE,
 *   then each condition is evaluated on the element in the source that has
 *   the corresponding index/key. Thus, when 'iterate' is TRUE, the source
 *   must be an array.
 * - negate: (optional) Whether the 'or' condition should be negated.
 *   Defaults to FALSE. You can also negate the 'or' plugin by using
 *   'not:or' as the plugin id.
 * - source: (optional) Property or array of properties on which to evaluate
 *   the condition. If not set, the condition will be evaluated on the source
 *   passed to the ::evaluate() method, typically the source of the process
 *   plugin that is using this condition.
 *
 * Examples:
 *
 * Set destination_field to TRUE if source_value is less than 5
 * or greater than 5. That is, set to FALSE if source_value is 5.
 *
 * @code
 * process:
 *   is_not_five:
 *     plugin: evaluate_condition
 *     source: source_value
 *     condition:
 *       plugin: or
 *       conditions:
 *         -
 *           plugin: less_than
 *           value: 5
 *         -
 *           plugin: greater_than
 *           value: 5
 * @endcode
 *
 * Obviously this can be done more clearly using the 'equals' condition.
 * It's just an example.
 *
 * By using the 'iterate' property, you can evaluate separate conditions
 * on separate elements of the source value.
 *
 * Set a boolean if the source image is missing 'alt' or 'title'.
 * Assume field_image is a single array with properties like 'alt',
 * 'title', filename', url', etc.
 *
 * @code
 * process:
 *   missing_data:
 *     plugin: evaluate_condition
 *     source: field_image
 *     condition:
 *       plugin: or
 *       iterate: true
 *       conditions:
 *         alt:
 *           plugin: empty
 *         title
 *           plugin: empty
 * @endcode
 *
 * The previous example can alternatively be written by configuring
 * the source separately for the OR-ed conditions.
 *
 * @code
 * process:
 *   missing_data:
 *     plugin: evaluate_condition
 *     condition:
 *       plugin: or
 *       conditions:
 *         missing_alt:
 *           plugin: empty
 *           source: field_image/alt
 *         missing_title:
 *           plugin: empty
 *           source: field_image/title
 * @endcode
 *
 * Note the array keys missing_alt and missing_title are not important in
 * this case. The array keys are very important when iterate is TRUE. This
 * example just aims to demonstrate how the syntax allows semantic code.
 *
 * @MigrateConditionsConditionPlugin(
 *   id = "or"
 * )
 */
class OrCondition extends LogicalConditionBase {

  /**
   * {@inheritdoc}
   */
  protected function doEvaluate($source, Row $row) {
    foreach ($this->conditions as $index => $condition) {
      if ($this->iterate) {
        $source_value = $source[$index] ?? NULL;
      }
      else {
        $source_value = $source;
      }
      if ($condition->evaluate($source_value, $row)) {
        return TRUE;
      }
    }
    return FALSE;
  }

}
