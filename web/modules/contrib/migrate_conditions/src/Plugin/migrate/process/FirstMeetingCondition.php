<?php

namespace Drupal\migrate_conditions\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate_conditions\Plugin\ProcessPluginWithConditionBase;

/**
 * Returns first value in array meeting condition.
 *
 * Available configuration keys:
 * - condition: The condition plugin to evaluate on each element.
 *   Can be either:
 *   - The id of the condition. This is possible if the condition does not
 *     require any configuration, such as the 'empty' condition.
 *   - An array with a 'plugin' key that is the id of the condition.
 *     Any additional properties will be used as configuration when
 *     creating an instance of the condition.
 * - default_value: (optional) The value to return if no values in the source
 *   meet the condition. This must be a string literal, not a source or
 *   destination property.
 *
 * Examples:
 *
 * Recreate the null_coalesce process plugin. That is, return
 * the first source value that is not null.
 *
 * @code
 * process:
 *   null_coalesce:
 *     plugin: first_meeting_condition
 *     condition:
 *       plugin: is_null
 *       negate: true
 *     source:
 *       - field_one
 *       - field_two
 *       - field_three
 *     default_value: 'My default literal'
 * @endcode
 *
 * or equivalently using 'not:' before the condition plugin id:
 *
 * @code
 * process:
 *   null_coalesce:
 *     plugin: first_meeting_condition
 *     condition: not:is_null
 *     source:
 *       - field_one
 *       - field_two
 *       - field_three
 *     default_value: 'My default literal'
 * @endcode
 *
 * Create an 'empty_coalesce' process plugin. That is, return
 * the first source value that is not empty.
 *
 * @code
 * process:
 *   empty_coalesce:
 *     plugin: first_meeting_condition
 *     condition:
 *       plugin: empty
 *       negate: true
 *     source:
 *       - field_one
 *       - field_two
 *       - field_three
 *     default_value: 'My default literal'
 * @endcode
 *
 * or equivalently using 'not:' before the condition plugin id:
 *
 * @code
 * process:
 *   null_coalesce:
 *     plugin: first_meeting_condition
 *     condition: not:empty
 *     source:
 *       - field_one
 *       - field_two
 *       - field_three
 *     default_value: 'My default literal'
 * @endcode
 *
 * @MigrateProcessPlugin(
 *   id = "first_meeting_condition",
 *   handle_multiples = TRUE
 * )
 */
class FirstMeetingCondition extends ProcessPluginWithConditionBase {

  /**
   * Flag indicating whether there are multiple values.
   *
   * @var bool
   */
  protected $multiple;

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $value = (array) $value;
    $return = NULL;
    foreach ($value as $val) {
      if ($this->condition->evaluate($val, $row)) {
        $return = $val;
        break;
      }
    }
    if (!isset($return) && isset($this->configuration['default_value'])) {
      $return = $this->configuration['default_value'];
    }
    $this->multiple = is_array($return);
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function multiple() {
    return $this->multiple;
  }

}
