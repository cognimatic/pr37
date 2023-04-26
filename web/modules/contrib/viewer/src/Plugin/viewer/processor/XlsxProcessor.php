<?php

namespace Drupal\viewer\Plugin\viewer\processor;

use Drupal\viewer\Entity\ViewerInterface;

/**
 * Default ViewerProcessor plugin.
 *
 * @ViewerProcessor(
 *   id = "processor_xlsx"
 * )
 */
class XlsxProcessor extends CsvProcessor {

  /**
   * {@inheritdoc}
   */
  public function getDataAsArray(ViewerInterface $viewer, $split_headers = TRUE) {
    $grouped = [];
    $result = [];
    if ($viewer_source = $viewer->getViewerSource()) {
      $array = $viewer_source->getContentAsArray();
      $configuration = $viewer->getConfiguration();
      $filters = $viewer->getFilters();
      $skip_first_row = empty($viewer->getSetting('add_headers')) ? TRUE : FALSE;
      foreach ($configuration['worksheet_labels'] as $worksheet_id => $label) {
        if ($headers = $this->buildHeaders($array[$label], $configuration['worksheets'][$worksheet_id], $viewer->getViewerPluginId())) {
          foreach ($headers as $value) {
            $result[$worksheet_id][] = $value;
            $grouped['headers'][$worksheet_id] = $value;
          }
        }
        if ($rows = $this->buildRows($viewer, $skip_first_row, $array[$label], $configuration['worksheets'][$worksheet_id], $filters, $worksheet_id)) {
          foreach ($rows as $value) {
            $result[$worksheet_id][] = $value;
            if (!empty($value)) {
              $grouped['rows'][$worksheet_id] = $value;
            }
          }
        }
      }
    }
    if ($split_headers) {
      return $grouped;
    }
    return $result;
  }

}
