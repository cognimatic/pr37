<?php

namespace Drupal\viewer\Plugin\viewer\type;

use Drupal\file\Entity\File;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Drupal\viewer\Plugin\ViewerTypeBase;

/**
 * Viewer Type plugin.
 *
 * @ViewerType(
 *   id = "xlsx",
 *   name = @Translation("XLSX"),
 *   default_viewer = "spreadsheet_tabs",
 *   extensions = {},
 * )
 */
class Xlsx extends ViewerTypeBase {

  /**
   * {@inheritdoc}
   */
  public function getExtensions() {
    return [
      'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
      'application/vnd.ms-excel' => 'xls',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(File $file, $settings = []) {
    $metadata = [];
    $rows = $this->getContentAsArray($file, $settings);
    foreach (array_keys($rows) as $worksheet) {
      $metadata[$worksheet] = current($rows[$worksheet]);
    }
    return $metadata;
  }

  /**
   * {@inheritdoc}
   */
  public function getContentAsArray(File $file, $settings = []) {
    $reader = IOFactory::createReader(strstr($file->getFilename(), '.xlsx') ? 'Xlsx' : 'Xls');
    $spreadsheet = $reader->load($this->fileSystem->realpath($file->getFileUri()));
    $sheet_columns = [];
    $loadedSheetNames = $spreadsheet->getSheetNames();
    foreach ($loadedSheetNames as $sheet_name) {
      $spreadsheet->setActiveSheetIndexByName($sheet_name);
      $sheetData = $spreadsheet->getActiveSheet()->toArray(NULL, TRUE, TRUE, TRUE);
      if (!empty($sheetData[1])) {
        $index = 0;
        $loop = 0;
        foreach ($sheetData as $value) {
          if (!empty($value)) {
            foreach ($value as $v) {
              $sheet_columns[$sheet_name][$index][] = $v;
            }
            $index++;
          }
          $loop++;
        }
      }
    }
    return $sheet_columns;
  }

}
