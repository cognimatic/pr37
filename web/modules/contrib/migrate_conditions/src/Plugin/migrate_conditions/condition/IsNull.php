<?php

namespace Drupal\migrate_conditions\Plugin\migrate_conditions\condition;

use Drupal\migrate\Row;
use Drupal\migrate_conditions\Plugin\ConditionBase;

/**
 * Provides 'is_null' condition.
 *
 * Available configuration keys:
 * - negate: (optional) Whether the 'is_null' condition should be negated.
 *   Defaults to FALSE. You can also negate the 'is_null' plugin by using
 *   'not:is_null' as the plugin id.
 * - source: (optional) Property or array of properties on which to evaluate
 *   the condition. If not set, the condition will be evaluated on the source
 *   passed to the ::evaluate() method, typically the source of the process
 *   plugin that is using this condition.
 *
 * Examples:
 *
 * Skip the row is the source_value is null.
 *
 * @code
 * process:
 *   skip_null:
 *     plugin: skip_on_condition
 *     condition: is_null
 *     source: source_value
 *     method: row
 * @endcode
 *
 * Skip the row is the source_value is not null.
 *
 * @code
 * process:
 *   skip_not_null:
 *     plugin: skip_on_condition
 *     condition:
 *       plugin: is_null
 *       negate: true
 *     source: source_value
 *     method: row
 * @endcode
 *
 * or equivalently using the 'not:' prefix on the condition plugin:
 *
 * @code
 * process:
 *   skip_not_null:
 *     plugin: skip_on_condition
 *     condition: not:is_null
 *     source: source_value
 *     method: row
 * @endcode
 *
 * @MigrateConditionsConditionPlugin(
 *   id = "is_null"
 * )
 */
class IsNull extends ConditionBase {

  /**
   * {@inheritdoc}
   */
  protected function doEvaluate($source, Row $row) {
    return is_null($source);
  }

}
