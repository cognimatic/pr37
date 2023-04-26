<?php

namespace Drupal\viewer\Plugin\viewer\processor;

use ArrayQuery\QueryBuilder;
use Drupal\viewer\Plugin\ViewerProcessorBase;
use Drupal\viewer\Entity\ViewerInterface;

/**
 * Default ViewerProcessor plugin.
 *
 * @ViewerProcessor(
 *   id = "processor_csv"
 * )
 */
class CsvProcessor extends ViewerProcessorBase {

  /**
   * Process array.
   */
  public function getDataAsArray(ViewerInterface $viewer, $split_headers = TRUE) {
    $grouped = [];
    $result = [];
    if ($viewer_source = $viewer->getViewerSource()) {
      $array = $viewer_source->getContentAsArray();
      $configuration = $viewer->getConfiguration();
      $filters = $viewer->getFilters();
      $skip_first_row = empty($viewer->getSetting('add_headers')) ? TRUE : FALSE;
      if ($headers = $this->buildHeaders($array, $configuration, $viewer->getViewerPluginId())) {
        foreach ($headers as $value) {
          $result[] = $value;
          $grouped['headers'] = $value;
        }
      }
      if ($rows = $this->buildRows($viewer, $skip_first_row, $array, $configuration, $filters, NULL)) {
        foreach ($rows as $value) {
          $result[] = $value;
          $grouped['rows'][] = $value;
        }
      }
    }
    if ($split_headers) {
      return $grouped;
    }
    return $result;
  }

  /**
   * Build headers with respected configuration.
   */
  protected function buildHeaders($rows, $configuration, $source_plugin) {
    $headers = [];
    $csv_first_row = ($current = current($rows)) ? $current : [];
    $first_key = array_key_first($configuration);
    if (!is_string($first_key)) {
      foreach ($configuration as $key => $details) {
        if (empty($configuration[$key]['hide'])) {
          if (!empty($configuration[$key]['empty'])) {
            $headers[0][] = '';
          }
          else {
            if (!empty($configuration[$key]['override_header'])) {
              $headers[0][] = !empty($configuration[$key]['override_header']) ? $configuration[$key]['override_header'] : '';
            }
            else {
              $headers[0][] = !empty($csv_first_row[$key]) ? $csv_first_row[$key] : 'Header ' . $key;
            }
          }
        }
      }
    }
    else {
      foreach ($csv_first_row as $key => $header) {
        if (empty($configuration[$key]['hide'])) {
          if (!empty($configuration[$key]['empty'])) {
            $headers[0][] = '';
          }
          else {
            if (!empty($configuration[$key]['override_header'])) {
              $headers[0][] = !empty($configuration[$key]['override_header']) ? $configuration[$key]['override_header'] : '';
            }
            else {
              $headers[0][] = !empty($header) ? $header : 'Header ' . $key;
            }
          }
        }
      }
    }
    return $headers;
  }

  /**
   * Build rows and process cell transformations.
   */
  protected function buildRows($viewer, $skip_first_row, $rows, $configuration, $filters = NULL, $worksheet_id = NULL) {
    $index_order = [];
    $idx = 0;
    // New element position indexes (when sorted)
    foreach ($configuration as $pos => $details) {
      if (!empty($configuration[$pos]['weight'])) {
        if (empty($configuration[$pos]['hide'])) {
          $index_order[$pos] = $idx;
          $idx++;
        }
      }
    }
    $raw_items = [];
    $items = [];
    $start_index = $skip_first_row ? 1 : 0;
    foreach ($rows as $row_index => $cells) {
      if ($row_index >= $start_index) {
        if (!empty($configuration[0]['weight'])) {
          if (!empty($worksheet_id)) {
            $items[$worksheet_id][$row_index] = [];
          }
          else {
            $items[$row_index] = [];
          }
          foreach ($index_order as $pos => $cell_index) {
            if (empty($configuration[$pos]['hide'])) {
              $cell_plugin = $viewer->getCellPlugin(!empty($configuration[$pos]['cell_plugin']) ? $configuration[$pos]['cell_plugin'] : 'as_is');
              if (!empty($worksheet_id)) {
                array_splice($items[$worksheet_id][$row_index], $cell_index, 0, $cell_plugin->convert($cells[$pos], $cells));
              }
              else {
                array_splice($items[$row_index], $cell_index, 0, $cell_plugin->convert($cells[$pos], $cells));
              }
            }
          }
        }
        else {
          foreach ($cells as $cell_index => $cell) {
            if (empty($configuration[$cell_index]['hide'])) {
              $cell_plugin = $viewer->getCellPlugin(!empty($configuration[$cell_index]['cell_plugin']) ? $configuration[$cell_index]['cell_plugin'] : 'as_is');
              if (!empty($worksheet_id)) {
                $items[$worksheet_id][$row_index][] = $cell_plugin->convert($cell, $cells);
              }
              else {
                $items[$row_index][] = $cell_plugin->convert($cell, $cells);
              }
            }
          }
        }
        foreach ($cells as $cell_index => $cell) {
          if (!empty($worksheet_id)) {
            $raw_items[$worksheet_id][$row_index][] = $cell;
          }
          else {
            $raw_items[$row_index][] = $cell;
          }
        }
      }
    }
    if (!empty($worksheet_id)) {
      if (!empty($filters)) {
        $items = $this->filter($filters, $items, $raw_items, $worksheet_id);
      }
      $items[$worksheet_id] = array_filter($items[$worksheet_id]);
      $items = array_map('array_values', $items);
    }
    else {
      if (!empty($filters)) {
        return $this->filter($filters, $items, $raw_items, $worksheet_id);
      }
    }
    return $items;
  }

  /**
   * Array filter.
   */
  protected function filter($filters, $items, $raw_items, $worksheet_id = NULL) {
    $array = (!empty($worksheet_id) && !empty($raw_items[$worksheet_id])) ? $raw_items[$worksheet_id] : $raw_items;
    $query = QueryBuilder::create($array);
    foreach ($filters as $filter) {
      $column = $filter['column'];
      $value = $filter['value'];
      $criteria = $filter['criteria'];
      if ($criteria == 'IN_ARRAY') {
        $value = explode('|', $filter['value']);
      }
      if (strstr($column, '|')) {
        $e = explode('|', $column);
        $column = $e[1];
      }
      $dt = ['EQUALS_DATE', 'GT_DATE', 'GTE_DATE', 'LT_DATE', 'LTE_DATE'];
      $format = (!empty($filter['format']) && in_array($criteria, $dt)) ? $filter['format'] : '';
      if (!empty($worksheet_id)) {
        if ($worksheet_id == $e[0]) {
          $query->addCriterion($column, $value, $criteria, $format);
        }
      }
      else {
        $query->addCriterion($column, $value, $criteria, $format);
      }
    }
    $results = [];
    if ($filtered = $query->getResults()) {
      $items_array = (!empty($worksheet_id) && !empty($items[$worksheet_id])) ? $items[$worksheet_id] : $items;
      $row_ids = array_keys($filtered);
      foreach ($items_array as $idx => $result) {
        if (in_array($idx, $row_ids)) {
          if (!empty($worksheet_id)) {
            $results[$worksheet_id][] = $result;
          }
          else {
            $results[] = $result;
          }
        }
      }
    }
    return $results;
  }

}
