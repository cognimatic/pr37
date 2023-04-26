<?php

namespace Drupal\viewer\Plugin\viewer\type;

use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\viewer\Plugin\ViewerTypeBase;

/**
 * Viewer Type plugin.
 *
 * @ViewerType(
 *   id = "csv",
 *   name = @Translation("CSV"),
 *   default_viewer = "tables",
 *   extensions = {
 *     "text/csv" = "csv",
 *     "text/plain" = "txt",
 *   },
 * )
 */
class Csv extends ViewerTypeBase {

  /**
   * {@inheritdoc}
   */
  public function propertiesForm($settings = []) {
    $form['delimiter'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Delimiter'),
      '#default_value' => !empty($settings['delimiter']) ? $settings['delimiter'] : ',',
      '#size' => 5,
      '#required' => TRUE,
    ];
    $form['enclosure'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Enclosure'),
      '#default_value' => !empty($settings['enclosure']) ? $settings['enclosure'] : '"',
      '#size' => 5,
      '#required' => TRUE,
    ];
    $form['escape'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Escape'),
      '#default_value' => !empty($settings['escape']) ? $settings['escape'] : '\\',
      '#size' => 5,
      '#required' => TRUE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitPropertiesForm(FormStateInterface $form_state) {
    return [
      'delimiter' => $form_state->getValue('delimiter', ','),
      'enclosure' => $form_state->getValue('enclosure', '"'),
      'escape'    => $form_state->getValue('escape', '\\'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(File $file, $settings = []) {
    return $this->getHeadersFromFile($file, $settings);
  }

  /**
   * Process uploaded CSV file to pull data.
   */
  protected function getHeadersFromFile($file, $settings = []) {
    $headers = [];
    if (($handle = fopen($this->fileSystem->realpath($file->getFileUri()), "r")) !== FALSE) {
      $row = 0;
      while (($data = fgetcsv($handle, 1000, $settings['delimiter'], $settings['enclosure'], $settings['escape'])) !== FALSE) {
        if ($row == 0) {
          for ($c = 0; $c < count($data); $c++) {
            $headers[$c] = $data[$c];
          }
        }
        $row++;
      }
      fclose($handle);
    }
    return $headers;
  }

}
