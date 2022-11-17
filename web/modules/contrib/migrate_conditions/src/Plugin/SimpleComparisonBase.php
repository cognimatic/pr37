<?php

namespace Drupal\migrate_conditions\Plugin;

use Drupal\migrate\Row;

/**
 * A base class for simple comparisons.
 *
 * A simple comparison requires that exactly one of 'property' and 'value'
 * is set as configuration. If set, the value will be treated as static.
 * If set, the property will be used to 'get' a value from the row.
 *
 * Both the constructor and evaluate methods are final. Extensions of this
 * class will only declare a compare method. The compare method should not
 * throw exceptions. If a condition requires additional configuration validation
 * or input validation, it is not 'simple' and should not extend this class.
 *
 * @ingroup migration
 */
abstract class SimpleComparisonBase extends ConditionBase {

  /**
   * {@inheritdoc}
   */
  final public function __construct(array $configuration, $plugin_id, $plugin_definition) {
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
  final protected function doEvaluate($source, Row $row) {
    if (isset($this->configuration['property'])) {
      $value = $row->get($this->configuration['property']);
    }
    else {
      $value = $this->configuration['value'];
    }
    return $this->compare($source, $value);
  }

  /**
   * A simple comparison called from the evaluate function.
   *
   * This method should not throw exceptions or perform validation
   * of configuration or input. If a condition requires additional
   * configuration validation or input validation, it is not 'simple'
   * and should not extend this class.
   *
   * @param mixed $source
   *   The source passed to the condition.
   * @param mixed $value
   *   The value to which we compare the source.
   *
   * @return bool
   *   The result of the comparison.
   */
  abstract protected function compare($source, $value);

}
