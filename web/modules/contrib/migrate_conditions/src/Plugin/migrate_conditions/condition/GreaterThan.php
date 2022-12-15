<?php

namespace Drupal\migrate_conditions\Plugin\migrate_conditions\condition;

use Drupal\migrate_conditions\Plugin\SimpleComparisonBase;

/**
 * Provides a 'greater_than' condition.
 *
 * Available configuration keys:
 * - value: (one of value or property is required) The literal value to which
 *   to compare the source.
 * - property: (one of value or property is required) The source or destination
 *   property key to 'get' and to compare the source.
 * - negate: (optional) Whether the 'greater_than' condition should be negated.
 *   Defaults to FALSE. You can also negate the 'greater_than' plugin by using
 *   'not:greater_than' as the plugin id.
 * - source: (optional) Property or array of properties on which to evaluate
 *   the condition. If not set, the condition will be evaluated on the source
 *   passed to the ::evaluate() method, typically the source of the process
 *   plugin that is using this condition.
 *
 * Examples:
 *
 * Skip the row if source_field is less than or equal to 5.
 *
 * @code
 * process:
 *   skip_five_and_under:
 *     plugin: skip_on_condition
 *     source: source_field
 *     condition:
 *       plugin: greater_than
 *       negate: true
 *       value: 5
 *     method: row
 * @endcode
 *
 * or equivalently using the 'not:' prefix on the condition plugin:
 *
 * @code
 * process:
 *   skip_five_and_under:
 *     plugin: skip_on_condition
 *     source: source_field
 *     condition:
 *       plugin: not:greater_than
 *       value: 5
 *     method: row
 * @endcode
 *
 * The (string) value can be specified using the parens notation as well.
 *
 * @code
 * process:
 *   skip_five_and_under:
 *     plugin: skip_on_condition
 *     source: source_field
 *     condition: not:greater_than(5)
 *     method: row
 * @endcode
 *
 * Return true if source_mind is greater than source_matter.
 *
 * @code
 * process:
 *   mind_over_matter:
 *     plugin: evaluate_condition
 *     source: source_mind
 *     condition:
 *       plugin: greater_than
 *       property: source_matter
 * @endcode
 *
 * @MigrateConditionsConditionPlugin(
 *   id = "greater_than",
 *   parens = "value"
 * )
 */
class GreaterThan extends SimpleComparisonBase {

  /**
   * {@inheritdoc}
   */
  public function compare($source, $value) {
    return $source > $value;
  }

}
