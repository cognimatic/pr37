<?php

namespace Drupal\viewer\Plugin\viewer\viewer;

use Drupal\Core\Form\FormStateInterface;

/**
 * Viewer plugin.
 *
 * @Viewer(
 *   id = "footable",
 *   name = @Translation("FooTable"),
 *   processor = "processor_csv",
 *   filters = true,
 *   viewer_types = {
 *     "csv"
 *   }
 * )
 */
class FooTable extends Table {

  /**
   * {@inheritdoc}
   */
  public function getResponse() {
    $foo_headers = [];
    $foo_rows = [];
    $data = parent::getResponse();
    $headers = $data['headers'];
    foreach ($headers as $header) {
      $foo_headers[] = [
        'name' => $this->getMachineName($header),
        'title' => $header,
      ];
    }
    $rows = $data['rows'];
    foreach ($rows as $i => $row) {
      foreach ($row as $j => $cell) {
        $foo_rows[$i][$foo_headers[$j]['name']] = $cell;
      }
    }
    return [
      'headers' => $foo_headers,
      'rows' => $foo_rows,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getRenderable() {
    return [
      '#theme' => 'viewer_footable',
      '#uuid' => $this->getId(),
      '#viewer' => $this->getViewer(),
      '#settings' => $this->getSettings(),
      '#last_import' => $this->getViewerSource()->getLastImportRaw(),
      '#attached' => [
        'library' => ['viewer/viewer.footable'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state, $params = []) {
    $settings = $params['settings'];
    $form = parent::settingsForm($form, $form_state, $params);
    unset($form['general']['show_all'], $form['general']['items_per_load'], $form['general']['load_more_label']);
    $form['general']['paging'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable paging'),
      '#default_value' => !empty($settings['paging']),
    ];
    $form['general']['filtering'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable filtering'),
      '#default_value' => !empty($settings['filtering']),
    ];
    $form['general']['sorting'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable sorting'),
      '#default_value' => !empty($settings['sorting']),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsValues(array &$form, FormStateInterface $form_state) {
    $settings = parent::settingsValues($form, $form_state);
    unset($settings['show_all'], $settings['items_per_load'], $settings['load_more_label']);
    $settings += [
      'last_import_position' => $form_state->getValue('last_import_position'),
      'last_import_format' => $form_state->getValue('last_import_format'),
      'last_import' => $form_state->getValue('last_import'),
      'paging' => $form_state->getValue('paging'),
      'filtering' => $form_state->getValue('filtering'),
      'sorting' => $form_state->getValue('sorting'),
    ];
    return $settings;
  }

}
