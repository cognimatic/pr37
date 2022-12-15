<?php

namespace Drupal\migrate_conditions\Plugin\migrate_conditions\condition;

use Drupal\migrate\MigrateException;
use Drupal\migrate\Row;
use Drupal\migrate_conditions\Plugin\ConditionBase;

/**
 * Provides a 'matches' condition.
 *
 * This condition can be used on a source string.
 *
 * Available configuration keys:
 * - regex: The regex to match against.
 * - negate: (optional) Whether the 'matches' condition should be negated.
 *   Defaults to FALSE.
 * - source: (optional) Property or array of properties on which to evaluate
 *   the condition. If not set, the condition will be evaluated on the source
 *   passed to the ::evaluate() method, typically the source of the process
 *   plugin that is using this condition.
 *
 * Examples:
 *
 * Search for any digit in a string
 *
 * @code
 * process:
 *   has_a_digit:
 *     plugin: evaluate_condition
 *     condition:
 *       plugin: matches
 *       regex: '/\d+/'
 *     source: source_string
 * @endcode
 *
 * The regex pattern can be passed using parens syntax as well.
 *
 * @code
 * process:
 *   has_a_digit:
 *     plugin: evaluate_condition
 *     condition: matches(/\d+/)
 *     source: source_string
 * @endcode
 *
 * @MigrateConditionsConditionPlugin(
 *   id = "matches",
 *   requires = {"regex"},
 *   parens = "regex"
 * )
 */
class Matches extends ConditionBase {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    // Check if the regex is valid.
    try {
      preg_match($configuration['regex'], '');
    }
    catch (\Throwable $e) {
      throw new \InvalidArgumentException("The regex {$configuration['regex']} is invalid.");
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function doEvaluate($source, Row $row) {
    if (is_string($source)) {
      return (bool) preg_match($this->configuration['regex'], $source);
    }
    else {
      throw new MigrateException('When using the matches condition, the source must be a string.');
    }
  }

}
