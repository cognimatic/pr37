<?php

namespace Drupal\viewer\Plugin\viewer\cell;

use Drupal\viewer\Plugin\ViewerCellBase;

/**
 * Number ViewerCell cell plugin.
 *
 * @ViewerCell(
 *   id = "number",
 *   name = @Translation("Number"),
 *   viewers = {
 *     "table",
 *     "datatables",
 *     "gridjs",
 *     "footable",
 *     "spreadsheet",
 *   }
 * )
 */
class Number extends ViewerCellBase {

  /**
   * {@inheritdoc}
   */
  public function convert($value, $row) {
    return (is_float($value) || is_numeric($value)) ? number_format($value, 2) : $value;
  }

}
