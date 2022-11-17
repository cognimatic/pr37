<?php

namespace Drupal\migrate_conditions\Plugin\migrate_conditions\condition;

use Drupal\migrate\Row;
use Drupal\migrate_conditions\Plugin\ConditionBase;

/**
 * Provides 'empty' condition.
 *
 * Available configuration keys:
 * - negate: (optional) Whether the 'empty' condition should be negated.
 *   Defaults to FALSE. You can also negate the 'empty' plugin by using
 *   'not:empty' as the plugin id.
 *
 * Examples:
 *
 * Skip the row is the source_value is empty.
 *
 * @code
 * process:
 *   skip_empty:
 *     plugin: skip_on_condition
 *     condition: empty
 *     source: source_value
 *     method: row
 * @endcode
 *
 * Skip the row is the source_value is not empty.
 *
 * @code
 * process:
 *   skip_not_empty:
 *     plugin: skip_on_condition
 *     condition:
 *       plugin: empty
 *       negate: true
 *     source: source_value
 *     method: row
 * @endcode
 *
 * @see \Drupal\migrate_conditions\ConditionInterface
 *
 * @MigrateConditionsConditionPlugin(
 *   id = "empty"
 * )
 */
class EmptyCondition extends ConditionBase {

  /**
   * {@inheritdoc}
   */
  protected function doEvaluate($source, Row $row) {
    return empty($source);
  }

}
