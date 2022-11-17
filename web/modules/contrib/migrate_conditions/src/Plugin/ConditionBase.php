<?php

namespace Drupal\migrate_conditions\Plugin;

use Drupal\Core\Plugin\PluginBase;
use Drupal\migrate\Row;
use Drupal\migrate_conditions\ConditionInterface;

/**
 * The base class for all migrate condition plugins.
 *
 * @ingroup migration
 */
abstract class ConditionBase extends PluginBase implements ConditionInterface {

  /**
   * TRUE if the normal operation of the condition is negated.
   *
   * @var bool
   */
  protected $negated;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    if (!empty($plugin_definition['requires'])) {
      foreach ($plugin_definition['requires'] as $key) {
        if (!isset($configuration[$key])) {
          throw new \InvalidArgumentException("The $key configuration is required when using the $plugin_id condition.");
        }
      }
    }
    $this->negated = $configuration['negate'] ?? FALSE;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate($source, Row $row) {
    return ($this->doEvaluate($source, $row) xor $this->negated);
  }

  /**
   * Evaluate the condition without regard for negation.
   *
   * @param mixed $source
   *   Source values passed from process plugin.
   * @param \Drupal\migrate\Row $row
   *   The current row.
   *
   * @return bool
   *   TRUE if the condition evaluates as TRUE.
   */
  abstract protected function doEvaluate($source, Row $row);

}
