<?php

namespace Drupal\migrate_conditions\Plugin\migrate_conditions\condition;

use Drupal\migrate_conditions\Plugin\SimpleComparisonBase;

/**
 * Provides 'in_array' condition.
 *
 * Available configuration keys:
 * - value: (one of value or property is required) The literal array in which
 *   to search for the source.
 * - property: (one of value or property is required) The source or destination
 *   property key to 'get' and then search for the source.
 * - strict: (optional) 'strict' parameter for in_array(). Defaults to FALSE.
 * - negate: (optional) Whether the 'in_array' condition should be negated.
 *   Defaults to FALSE. You can also negate the 'in_array' plugin by using
 *   'not:in_array' as the plugin id.
 *
 * Examples:
 *
 * Skip row if the source_field is 'one', 'two', or 'three'.
 *
 * @code
 * process:
 *   skip_one_two_three:
 *     plugin: skip_on_condition
 *     source: source_field
 *     condition:
 *       plugin: in_array
 *       value:
 *         - one
 *         - two
 *         - three
 *       strict: TRUE
 *    method: row
 * @endcode
 *
 * Skip process if the value of source_value is found within the
 * source_array.
 *
 * @code
 * process:
 *   array_contains_value:
 *     plugin: evaluate_condition
 *     source: source_field
 *     condition:
 *       plugin: in_array
 *       property: source_array
 * @endcode
 *
 * Please note that the in_array condition is not compatible
 * with parens syntax since only a string can be passes through
 * parens notation.
 *
 * @MigrateConditionsConditionPlugin(
 *   id = "in_array"
 * )
 */
class InArray extends SimpleComparisonBase {

  /**
   * {@inheritdoc}
   */
  public function compare($source, $value) {
    return in_array($source, (array) $value, $this->configuration['strict'] ?? FALSE);
  }

}
