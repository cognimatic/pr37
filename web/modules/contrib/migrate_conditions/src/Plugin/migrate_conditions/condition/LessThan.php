<?php

namespace Drupal\migrate_conditions\Plugin\migrate_conditions\condition;

use Drupal\migrate_conditions\Plugin\SimpleComparisonBase;

/**
 * Provides a 'less_than' condition.
 *
 * Available configuration keys:
 * - value: (one of value or property is required) The literal value to which
 *   to compare the source.
 * - property: (one of value or property is required) The source or destination
 *   property key to 'get' and to compare the source.
 * - negate: (optional) Whether the 'less_than' condition should be negated.
 *   Defaults to FALSE. You can also negate the 'less_than' plugin by using
 *   'not:less_than' as the plugin id.
 * - source: (optional) Property or array of properties on which to evaluate
 *   the condition. If not set, the condition will be evaluated on the source
 *   passed to the ::evaluate() method, typically the source of the process
 *   plugin that is using this condition.
 *
 * Examples:
 *
 * Skip the row if source_field is less than 5.
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: skip_on_condition
 *     source: source_field
 *     condition:
 *       plugin: less_than
 *       value: 5
 *     method: row
 * @endcode
 *
 * The (string) value can be specified using the parens syntax too.
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: skip_on_condition
 *     source: source_field
 *     condition: less_than(5)
 *     method: row
 * @endcode
 *
 * Remove values from source_array that are less than destination
 * property @field_whatever.
 *
 * @code
 * process:
 *   big_numbers_only:
 *     plugin: filter_on_condition
 *     source: source_array
 *     condition:
 *       plugin: greater_than
 *       property: '@field_whatever'
 * @endcode
 *
 * @MigrateConditionsConditionPlugin(
 *   id = "less_than",
 *   parens = "value"
 * )
 */
class LessThan extends SimpleComparisonBase {

  /**
   * {@inheritdoc}
   */
  public function compare($source, $value) {
    return $source < $value;
  }

}
