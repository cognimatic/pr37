<?php

namespace Drupal\migrate_conditions\Plugin\migrate_conditions\condition;

use Drupal\migrate_conditions\Plugin\SimpleComparisonBase;

/**
 * Provides an 'equals' condition.
 *
 * Available configuration keys:
 * - value: (one of value or property is required) The literal value to which
 *   to compare the source.
 * - property: (one of value or property is required) The source or destination
 *   property key to 'get' and to compare the source.
 * - strict: (optional) Pass TRUE to compare with ===.
 * - negate: (optional) Whether to negate the 'equals' condition.
 *   Defaults to FALSE. You can also negate the 'equals' plugin by using
 *   'not:equals' as the plugin id.
 * - source: (optional) Property or array of properties on which to evaluate
 *   the condition. If not set, the condition will be evaluated on the source
 *   passed to the ::evaluate() method, typically the source of the process
 *   plugin that is using this condition.
 *
 * Examples:
 *
 * Skip row if source_field is exactly equal to 0.
 *
 * @code
 * process:
 *   skip_exactly_zero:
 *     plugin: skip_on_condition
 *     source: source_field
 *     condition:
 *       plugin: equals
 *       value: 0
 *       strict: TRUE
 *     method: row
 * @endcode
 *
 * If animal_family is 'bird', get feather_color. Otherwise, get
 * fur_color.
 *
 * @code
 * process:
 *   animal_color:
 *     plugin: if_condition
 *     source: animal_family
 *     condition:
 *       plugin: equals
 *       value: bird
 *     do_get: feather_color
 *     else_get: fur_color
 * @endcode
 *
 * Alternatively, the (string) value can be specified using the parens syntax.
 *
 * @code
 * process:
 *   animal_color:
 *     plugin: if_condition
 *     source: animal_family
 *     condition: equals(bird)
 *     do_get: feather_color
 *     else_get: fur_color
 * @endcode
 *
 * Assume '@field_main_tag' is a destination property holding a single tid.
 * Remove that tid from source_tags.
 *
 * @code
 * process:
 *   field_other_tags:
 *     plugin: filter_on_condition
 *     source: source_tags
 *     condition:
 *       plugin: equals
 *       negate: true
 *       property: '@field_main_tag'
 * @endcode
 *
 * or equivalently using the 'not:' prefix on the condition plugin:
 *
 * @code
 * process:
 *   field_other_tags:
 *     plugin: filter_on_condition
 *     source: source_tags
 *     condition:
 *       plugin: not:equals
 *       property: '@field_main_tag'
 * @endcode
 *
 * Please note that 'property' CANNOT be set using the parens notation.
 * Only 'value' may be set in this way.
 *
 * @MigrateConditionsConditionPlugin(
 *   id = "equals",
 *   parens = "value"
 * )
 */
class Equals extends SimpleComparisonBase {

  /**
   * {@inheritdoc}
   */
  public function compare($source, $value) {
    if (isset($this->configuration['strict']) && $this->configuration['strict']) {
      return $source === $value;
    }
    else {
      return $source == $value;
    }
  }

}
