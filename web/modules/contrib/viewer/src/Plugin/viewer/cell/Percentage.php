<?php

namespace Drupal\viewer\Plugin\viewer\cell;

use Drupal\viewer\Plugin\ViewerCellBase;

/**
 * Percentage ViewerCell cell plugin.
 *
 * @ViewerCell(
 *   id = "percentage",
 *   name = @Translation("Percentage"),
 *   viewers = {
 *     "table",
 *     "datatables",
 *     "gridjs",
 *     "footable",
 *     "spreadsheet",
 *   }
 * )
 */
class Percentage extends ViewerCellBase {

  /**
   * {@inheritdoc}
   */
  public function convert($value, $row) {
    return !empty($value) ? $value . '%' : '0%';
  }

}
