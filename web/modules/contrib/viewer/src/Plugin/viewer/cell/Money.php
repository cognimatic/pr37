<?php

namespace Drupal\viewer\Plugin\viewer\cell;

use Drupal\viewer\Plugin\ViewerCellBase;

/**
 * Money ViewerCell cell plugin.
 *
 * @ViewerCell(
 *   id = "money",
 *   name = @Translation("Money (USD)"),
 *   viewers = {
 *     "table",
 *     "datatables",
 *     "gridjs",
 *     "footable",
 *     "spreadsheet",
 *   }
 * )
 */
class Money extends ViewerCellBase {

  /**
   * {@inheritdoc}
   */
  public function convert($value, $row) {
    return (is_float($value) || is_numeric($value)) ? '$' . number_format($value, 2) : $value;
  }

}
