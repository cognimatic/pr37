<?php

namespace Drupal\migrate_conditions\Plugin\migrate_conditions\condition;

use Drupal\migrate\Row;
use Drupal\migrate_conditions\Plugin\ConditionBase;

/**
 * Returns true if the current row is a stub.
 *
 * Note that when used within the process of a sub_process configuration,
 * this will refer to the "dummy row" that is created and used by the
 * sub_process plugin. That row is never a stub.
 *
 * Available configuration keys:
 * - negate: (optional) Whether the 'is_stub' condition should be negated.
 *   Defaults to FALSE. You can also negate the 'is_stub' plugin by using
 *   'not:is_stub' as the plugin id.
 *
 * Examples:
 *
 * Set a flag if the row is a stub.
 *
 * @code
 * process:
 *   field_stub:
 *     plugin: evaluate_condition
 *     condition: is_stub
 * @endcode
 *
 * Set a meaningful title if the row is a stub.
 *
 * @code
 * process:
 *   title:
 *     plugin: if_condition
 *     condition: not:is_stub
 *     source: title
 *     else_process:
 *       plugin: concat
 *       source:
 *         - constants/fallback_title_prefix
 *         - nid
 * @endcode
 *
 * @MigrateConditionsConditionPlugin(
 *   id = "is_stub"
 * )
 */
class IsStub extends ConditionBase {

  /**
   * {@inheritdoc}
   */
  protected function doEvaluate($source, Row $row) {
    return $row->isStub();
  }

}
