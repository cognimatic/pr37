<?php

namespace Drupal\camera_image_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * 
 * @FieldFormatter(
 *   id = "camera_image_formatter_filename_to_image",
 *   label = @Translation("Render camera filename as image"),
 *   field_types = {
 *     "text", 
 *     "string"
 *   }
 * )
 */
class FilenameToImageFormatter extends FormatterBase {

  /**
   * The viewElements function is where we're able to make modifications to
   * the FieldItemListInterface variable $items.
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    // Initialize an array to store our processed items.
    $elements = [];

    // In this for loop we'll take the value of each item, and then reverse the string.
    foreach ($items as $delta => $item) {

      $filename = $item->value;
      $image_path = '/sites/default/files/roadcams/' . $filename;
      $markup = '<img src="' . $image_path . '" alt="Recent image">';
      $elements[$delta] = ['#markup' => $markup];

    }

    // Lastly, we need to return the $elements array so it gets output for rendering.
    
    // ** option ** Consider outputting as image in render array which would
    // allow custom styles
    return $elements;
  }

}