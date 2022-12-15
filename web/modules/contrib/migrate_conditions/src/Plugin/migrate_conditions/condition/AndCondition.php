<?php

namespace Drupal\migrate_conditions\Plugin\migrate_conditions\condition;

use Drupal\migrate\Row;
use Drupal\migrate_conditions\Plugin\LogicalConditionBase;

/**
 * Provides an 'and' condition.
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
 * - negate: (optional) Whether the 'and' condition should be negated.
 *   Defaults to FALSE. You can also negate the 'and' plugin by using
 *   'not:and' as the plugin id.
 * - source: (optional) Property or array of properties on which to evaluate
 *   the condition. If not set, the condition will be evaluated on the source
 *   passed to the ::evaluate() method, typically the source of the process
 *   plugin that is using this condition.
 *
 * Examples:
 *
 * Set is_a_teenager to TRUE if 13 <= source_age < 20.
 *
 * @code
 * process:
 *   is_a_teenager:
 *     plugin: evaluate_condition
 *     source: source_age
 *     condition:
 *       plugin: and
 *       conditions:
 *         -
 *           plugin: less_than
 *           negate: true
 *           value: 13
 *         -
 *           plugin: less_than
 *           value: 20
 * @endcode
 *
 * Set source_is_five to TRUE if source_value is 5.
 *
 * @code
 * process:
 *   source_is_five:
 *     plugin: evaluate_condition
 *     source: source_value
 *     condition:
 *       plugin: and
 *       conditions:
 *         -
 *           plugin: greater_than
 *           value: 4
 *         -
 *           plugin: less_than
 *           value: 6
 *         -
 *           plugin: callback
 *           callable: is_int
 * @endcode
 *
 * Obviously this can be done more clearly using the 'equals' condition.
 * It's just an example.
 *
 * By using the 'iterate' property, you can evaluate separate conditions
 * on sparate source values very easily.
 *
 * Set a boolean if the source is large and in charge.
 *
 * @code
 * process:
 *   large_and_in_charge:
 *     plugin: evaluate_condition
 *     source:
 *       - source_size
 *       - source_comportment
 *     condition:
 *       plugin: and
 *       iterate: true
 *       conditions:
 *         -
 *           plugin: equals
 *           value: 'large'
 *         -
 *           plugin: equals
 *           value: 'in charge'
 * @endcode
 *
 * The previous example is likely clearer if we configure the source
 * of each condition separately, rather than relying on the source
 * passed to the process plugin.
 *
 * @code
 * process:
 *   large_and_in_charge:
 *     plugin: evaluate_condition
 *     condition:
 *       plugin: and
 *       conditions:
 *         -
 *           plugin: equals
 *           value: 'large'
 *           source: source_size
 *         -
 *           plugin: equals
 *           value: 'in charge'
 *           source: source_comportment
 * @endcode
 *
 * @MigrateConditionsConditionPlugin(
 *   id = "and",
 *   required = {"conditions"}
 * )
 */
class AndCondition extends LogicalConditionBase {

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
      if (!($condition->evaluate($source_value, $row))) {
        return FALSE;
      }
    }
    return TRUE;
  }

}
