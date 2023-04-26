<?php

namespace Drupal\viewer\Plugin\viewer\cell;

use Drupal\viewer\Plugin\ViewerCellBase;

/**
 * Link ViewerCell cell plugin.
 *
 * @ViewerCell(
 *   id = "link",
 *   name = @Translation("Link"),
 *   viewers = {
 *     "table",
 *     "datatables",
 *     "spreadsheet",
 *   }
 * )
 */
class Link extends ViewerCellBase {

  /**
   * {@inheritdoc}
   */
  public function convert($value, $row) {
    $tvalue = trim($value);
    return (filter_var($tvalue, FILTER_VALIDATE_URL))
      ? '<a href="' . $tvalue . '" class="viewer-converter-link">' . $tvalue . '</a>'
      : $value;
  }

}
