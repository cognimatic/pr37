<?php

namespace Drupal\viewer\Plugin\viewer\cell;

use Drupal\viewer\Plugin\ViewerCellBase;

/**
 * Peity ViewerCell cell plugin.
 *
 * Https://github.com/benpickles/peity
 * Display little charts
 * Cell source required format: line:5,2,3,2,1,0,1
 *
 * @ViewerCell(
 *   id = "peity",
 *   name = @Translation("Peity (chart)"),
 *   viewers = {
 *     "table",
 *     "datatables",
 *     "spreadsheet",
 *   }
 * )
 */
class Peity extends ViewerCellBase {

  /**
   * {@inheritdoc}
   */
  public function convert($value, $row) {
    if (strstr($value, ':')) {
      [$type, $values] = explode(':', $value);
      if (in_array($type, ['pie', 'donut', 'line', 'bar'])) {
        return '<div class="peity-chart-wrapper"><span class="peity-cell-chart" data-type="' . $type . '">' . $values . '</span></div>';
      }
    }
    return $value;
  }

}
