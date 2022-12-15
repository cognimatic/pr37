<?php

namespace Drupal\migrate_conditions\Plugin\migrate_conditions\condition;

use Drupal\migrate\MigrateException;
use Drupal\migrate\Row;
use Drupal\migrate_conditions\Plugin\ConditionBase;

/**
 * Provides a 'callback' condition.
 *
 * This condition accepts arguments and the source very much like the callback
 * process plugin provided by the core migrate module.
 *
 * Available configuration keys:
 * - callable: The name of the callable method.
 * - unpack_source: (optional) Whether to interpret the source as an array of
 *   arguments.
 * - strict: (optional) If set to TRUE, the callback is considered false only
 *   if it is identically equal to false. Defaults to FALSE. This is useful for
 *   callbacks like strpos, which may return a 0 that does not indicate FALSE.
 * - negate: (optional) Whether the result of the callable function should be
 *   negated. Defaults to FALSE. You can also negate the result of the
 *   callable function by using 'not:callback' as the plugin id.
 * - source: (optional) Property or array of properties on which to evaluate
 *   the condition. If not set, the condition will be evaluated on the source
 *   passed to the ::evaluate() method, typically the source of the process
 *   plugin that is using this condition.
 *
 * Examples:
 *
 * Determine if a source_number is an integer.
 *
 * @code
 * process:
 *   is_integer:
 *     plugin: skip_on_condition
 *     source: source_number
 *     condition:
 *       plugin: callback
 *       callable: is_int
 * @endcode
 *
 * The callback can be specified using the parens syntax as well.
 *
 * @code
 * process:
 *   is_integer:
 *     plugin: skip_on_condition
 *     source: source_number
 *     condition: callback(is_int)
 * @endcode
 *
 * Skip rows where the phone number uses a 900 number using regex.
 *
 * @code
 * constants:
 *   my_regex: '/\(9\d\d\)/'
 * process:
 *   skip_900_numbers:
 *     plugin: skip_on_condition
 *     method: row
 *     condition:
 *       plugin: callback
 *       callable: preg_match
 *       unpack_source: true
 *     source:
 *       - 'constants/my_regex'
 *       - source_phone_number
 * @endcode
 *
 * @MigrateConditionsConditionPlugin(
 *   id = "callback",
 *   requires = {"callable"},
 *   parens = "callable"
 * )
 */
class Callback extends ConditionBase {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    if (!is_callable($configuration['callable'])) {
      throw new \InvalidArgumentException('The "callable" must be a valid function or method.');
    }
    $this->configuration['strict'] = $configuration['strict'] ?? FALSE;
  }

  /**
   * {@inheritdoc}
   */
  protected function doEvaluate($source, Row $row) {
    if (!empty($this->configuration['unpack_source'])) {
      if (!is_array($source)) {
        throw new MigrateException(sprintf("When 'unpack_source' is set, the source must be an array. Instead it was of type '%s'", gettype($source)));
      }
      if ($this->configuration['strict']) {
        return call_user_func($this->configuration['callable'], ...$source) !== FALSE;
      }
      else {
        return (bool) call_user_func($this->configuration['callable'], ...$source);
      }
    }
    if ($this->configuration['strict']) {
      return call_user_func($this->configuration['callable'], $source) !== FALSE;
    }
    else {
      return (bool) call_user_func($this->configuration['callable'], $source);
    }
  }

}
