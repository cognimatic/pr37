<?php

namespace Drupal\migrate_conditions;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\migrate\Row;

/**
 * An interface for migrate condition plugins.
 *
 * @ingroup migration
 */
interface ConditionInterface extends PluginInspectionInterface {

  /**
   * Evaluate the condition.
   *
   * @param mixed $source
   *   Source values passed from process plugin.
   * @param \Drupal\migrate\Row $row
   *   The current row.
   *
   * @return bool
   *   TRUE if the condition evaluates as TRUE.
   */
  public function evaluate($source, Row $row);

}
