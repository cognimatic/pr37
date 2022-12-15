<?php

namespace Drupal\migrate_conditions\Plugin\migrate_conditions\condition;

use Drupal\migrate\Row;
use Drupal\migrate_conditions\Plugin\ConditionBase;

/**
 * Provides 'isset' condition.
 *
 * Available configuration keys:
 * - negate: (optional) Whether the 'isset' condition should be negated.
 *   Defaults to FALSE. You can also negate the 'isset' plugin by using
 *   'not:isset' as the plugin id.
 * - source: (optional) Property or array of properties on which to evaluate
 *   the condition. If not set, the condition will be evaluated on the source
 *   passed to the ::evaluate() method, typically the source of the process
 *   plugin that is using this condition.
 *
 * Examples:
 *
 * Skip a row if the source_value is not set.
 *
 * @code
 * process:
 *   skip_not_set:
 *     plugin: skip_on_condition
 *     condition:
 *       plugin: isset
 *       negate: true
 *     source: source_value
 *     method: row
 * @endcode
 *
 * or equivalently using the 'not:' prefix on the condition plugin:
 *
 * @code
 * process:
 *   skip_not_set:
 *     plugin: skip_on_condition
 *     condition: not:isset
 *     source: source_value
 *     method: row
 * @endcode
 *
 * Skip a row if the skip_me_please is set.
 *
 * @code
 * process:
 *   skip_requested:
 *     plugin: skip_on_condition
 *     condition: isset
 *     source: skip_me_please
 *     method: row
 * @endcode
 *
 * @MigrateConditionsConditionPlugin(
 *   id = "isset"
 * )
 */
class IssetCondition extends ConditionBase {

  /**
   * {@inheritdoc}
   */
  protected function doEvaluate($source, Row $row) {
    return isset($source);
  }

}
