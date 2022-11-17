<?php

namespace Drupal\migrate_conditions\Plugin\migrate_conditions\condition;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\migrate\MigrateException;
use Drupal\migrate\Row;
use Drupal\migrate_conditions\Plugin\ConditionBase;

/**
 * Provides an 'older_than' condition.
 *
 * The source must be a datetime string in the configured format. The date
 * we compare to (either value or property) can be either a datetime string
 * understood by strtotime() or a datetime string in the configured format.
 *
 * Available configuration keys:
 * - value: (one of value or property is required) A date string as accepted
 *   by strtotime() or a datetime string matching the configured format,
 *   against which the source value should be compared.
 * - property: (one of value or property is required) The source or destination
 *   property containing a date string as accepted by strtotime() against which
 *   the source value should be compared.
 * - format: The format of the source as accepted by
 *   DateTime::createFromFormat().
 * - negate: (optional) Whether the 'older_than' condition should be negated.
 *   Defaults to FALSE. You can also negate the 'older_than' plugin by using
 *   'not:older_than' as the plugin id.
 *
 * Examples:
 *
 * Skip the row if created_on holds a date more than a month old.
 * In the example, the created_on date has the form 'j M Y'.
 *
 * @code
 * process:
 *   skip_old_stuff:
 *     plugin: skip_on_condition
 *     source: created_on
 *     condition:
 *       plugin: older_than
 *       format: 'j M Y'
 *       value: '-1 month'
 *     method: row
 * @endcode
 *
 * Skip the row if field_timestamp is newer than 1642973915.
 * In the example, the field_timestamp date has the form 'U'.
 *
 * @code
 * process:
 *   skip_new_stuff:
 *     plugin: skip_on_condition
 *     source: field_timestamp
 *     condition:
 *       plugin: older_than
 *       negate: true
 *       format: 'U'
 *       value: '1642973915'
 *     method: row
 * @endcode
 *
 * Skip the row if field_updated is before field_created. Because
 * how was something updated before it was created? In the example,
 * field_created has the format 'Y-m-d H:i:s'.
 *
 * @code
 * process:
 *   skip_causality_violations:
 *     plugin: skip_on_condition
 *     source: field_created
 *     condition:
 *       plugin: older_than
 *       format: 'Y-m-d H:i:s'
 *       property: field_updated
 *     method: row
 * @endcode
 *
 * Please note that the older_than condition is not compatible
 * with parens syntax since it always requires multiple parameters.
 *
 * @MigrateConditionsConditionPlugin(
 *   id = "older_than",
 *   requires = {"format"}
 * )
 */
class OlderThan extends ConditionBase {

  /**
   * The static date used by all rows.
   *
   * @var Drupal\Component\Datetime\DateTimePlus
   */
  protected $valueDate;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    // Exactly one of value and property and must be set.
    if (array_key_exists('value', $configuration) && isset($configuration['property'])) {
      throw new \InvalidArgumentException("Exactly one of value and property must be set when using the $plugin_id condition.");
    }
    if (isset($configuration['property'])) {
      if (!is_string($configuration['property'])) {
        throw new \InvalidArgumentException("The property configuration must be a string when using the $plugin_id condition.");
      }
    }
    elseif (array_key_exists('value', $configuration)) {
      try {
        $this->valueDate = DateTimePlus::createFromTimestamp(strtotime($configuration['value']));
      }
      catch (\InvalidArgumentException $e) {
        try {
          $this->valueDate = DateTimePlus::createFromFormat($configuration['format'], $configuration['value']);
        }
        catch (\InvalidArgumentException $e) {
          throw new \InvalidArgumentException("The 'value' passed to older_than could not be converted into a datetime object.");
        }
      }
    }
    else {
      // This means that neither value nor property is set.
      throw new \InvalidArgumentException("Exactly one of value and property must be set when using the $plugin_id condition.");
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function doEvaluate($source, Row $row) {
    if ($this->valueDate) {
      $value_date = $this->valueDate;
    }
    else {
      try {
        $value_date = DateTimePlus::createFromTimestamp(strtotime($row->get($this->configuration['property'])));
      }
      catch (\InvalidArgumentException $e) {
        try {
          $value_date = DateTimePlus::createFromFormat($this->configuration['format'], $row->get($this->configuration['property']));
        }
        catch (\InvalidArgumentException $e) {
          throw new MigrateException("The 'property' passed to older_than could not be converted into a datetime object.");
        }
      }
    }

    try {
      $source_date = DateTimePlus::createFromFormat($this->configuration['format'], $source);
    }
    catch (\InvalidArgumentException $e) {
      throw new MigrateException($e->getMessage());
    }

    return (bool) $value_date->diff($source_date)->invert;
  }

}
