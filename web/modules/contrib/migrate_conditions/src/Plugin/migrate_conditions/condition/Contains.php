<?php

namespace Drupal\migrate_conditions\Plugin\migrate_conditions\condition;

use Drupal\migrate\MigrateException;
use Drupal\migrate\Row;
use Drupal\migrate_conditions\Plugin\ConditionBase;

/**
 * Provides a 'contains' condition.
 *
 * This condition can be used on a source array or a source string. If the
 * source is a string, the value/property must also be a string.
 *
 * Available configuration keys:
 * - value: (one of value or property is required) The literal value we search
 *   for in the source.
 * - property: (one of value or property is required) The source or destination
 *   property key to 'get' and then search for in the source.
 * - negate: (optional) Whether the 'contains' condition should be negated.
 *   Defaults to FALSE.
 * - source: (optional) Property or array of properties on which to evaluate
 *   the condition. If not set, the condition will be evaluated on the source
 *   passed to the ::evaluate() method, typically the source of the process
 *   plugin that is using this condition.
 *
 * Examples:
 *
 * Search for 'Dr.' in a name and set a boolean.
 *
 * @code
 * process:
 *   is_a_doctor:
 *     plugin: evaluate_condition
 *     condition:
 *       plugin: contains
 *       value: 'Dr.'
 *     source: source_name
 * @endcode
 *
 * The (string) value can be specified using the parens syntax.
 *
 * @code
 * process:
 *   is_a_doctor:
 *     plugin: evaluate_condition
 *     condition: contains(Dr.)
 *     source: source_name
 * @endcode
 *
 * Skip process if source_array does not contain source_value.
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: skip_on_condition
 *     source: source_array
 *     condition:
 *       plugin: contains
 *       negate: true
 *       property: source_value
 *     method: process
 * @endcode
 *
 * or equivalently using the 'not:' prefix on the condition plugin:
 *
 * @code
 * process:
 *   destination_field:
 *     plugin: skip_on_condition
 *     source: source_array
 *     condition:
 *       plugin: not:contains
 *       property: source_value
 *     method: process
 * @endcode
 *
 * @MigrateConditionsConditionPlugin(
 *   id = "contains",
 *   parens = "value"
 * )
 */
class Contains extends ConditionBase {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    // Exactly one of value property and must be set.
    if (array_key_exists('value', $configuration) && isset($configuration['property'])) {
      throw new \InvalidArgumentException("Exactly one of value and property must be set when using the $plugin_id condition.");
    }
    if (isset($configuration['property'])) {
      if (!is_string($configuration['property'])) {
        throw new \InvalidArgumentException("The property configuration must be a string when using the $plugin_id condition.");
      }
    }
    elseif (!array_key_exists('value', $configuration)) {
      // This means that neither value nor property is set.
      throw new \InvalidArgumentException("Exactly one of value and property must be set when using the $plugin_id condition.");
    }
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  protected function doEvaluate($source, Row $row) {
    if (isset($this->configuration['property'])) {
      $value = $row->get($this->configuration['property']);
    }
    else {
      $value = $this->configuration['value'];
    }

    if (is_array($source)) {
      return in_array($value, $source, TRUE);
    }
    elseif (is_string($source)) {
      if (is_string($value)) {
        return str_contains($source, $value);
      }
      else {
        throw new MigrateException('When using the contains condition with a string source, the value/property must be a string.');
      }
    }
    else {
      throw new MigrateException('When using the contains condition the source must be an array or a string.');
    }
  }

}
