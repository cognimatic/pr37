<?php

namespace Drupal\migrate_conditions\Plugin\migrate_conditions\condition;

use Drupal\migrate\Row;
use Drupal\migrate_conditions\Plugin\ConditionBase;

/**
 * Provides 'default' condition. The default condition returns TRUE.
 *
 * Available configuration keys:
 * - negate: (optional) Whether the 'default' condition should be negated.
 *   Defaults to FALSE. You can also negate the 'default' plugin by using
 *   'not:default' as the plugin id.
 *
 * Examples:
 *
 * This is always true:
 *
 * @code
 * process:
 *   always_true:
 *     plugin: evaluate_condition
 *     condition: default
 *     source: my_source
 * @endcode
 *
 * This is always false:
 *
 * @code
 * process:
 *   always_false:
 *     plugin: evaluate_condition
 *     condition: not:default
 *     source: my_source
 * @endcode
 *
 * This condition is most useful with the switch_on_condition process plugin.
 * The final case can use the default condition.
 *
 * Determine if a value is less then, equal to, or greater than 5.
 * Note that we use the `default` condition for our last case in
 * order to save some typing and to keep this semantic.
 *
 * @code
 * process:
 *   comparison_to_five:
 *     plugin: switch_on_condition
 *     source: my_source
 *     cases:
 *       -
 *         condition:
 *           plugin: less_than
 *           value: 5
 *         default_value: 'less than 5'
 *       -
 *         condition:
 *           plugin: equals
 *           value: 5
 *         default_value: 'equal to 5'
 *       -
 *         condition: default
 *         default_value: 'greater than 5'
 * @endcode
 *
 * @MigrateConditionsConditionPlugin(
 *   id = "default"
 * )
 */
class DefaultCondition extends ConditionBase {

  /**
   * {@inheritdoc}
   */
  protected function doEvaluate($source, Row $row) {
    return TRUE;
  }

}
