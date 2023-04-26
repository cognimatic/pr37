<?php

namespace Drupal\viewer\Plugin\viewer\cell;

use Drupal\viewer\Plugin\ViewerCellBase;

/**
 * Link ViewerCell cell plugin.
 *
 * @ViewerCell(
 *   id = "img",
 *   name = @Translation("Img"),
 *   viewers = {
 *     "table",
 *     "datatables",
 *     "spreadsheet",
 *   }
 * )
 */
class Img extends ViewerCellBase {

  /**
   * {@inheritdoc}
   */
  public function convert($value, $row) {
    return (filter_var($value, FILTER_VALIDATE_URL))
      ? '<img src="' . $value . '" class="viewer-converter-img" style="max-width: 150px"/>'
      : $value;
  }

}
