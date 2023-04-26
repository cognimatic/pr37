<?php

namespace Drupal\viewer\Plugin\viewer\viewer;

use Drupal\Core\Form\FormStateInterface;

/**
 * Viewer plugin.
 *
 * @Viewer(
 *   id = "apexchart_candlestick",
 *   name = @Translation("ApexCharts.js: Candlestick"),
 *   provider = "viewer",
 *   processor = "processor_csv",
 *   filters = true,
 *   viewer_types = {
 *     "csv"
 *   }
 * )
 */
class ApexCandlestick extends ApexLine {

  /**
   * {@inheritdoc}
   */
  public function getRenderable() {
    $build = parent::getRenderable();
    $build['#wrapper'] = 'candlestick';
    $build['#type'] = 'candlestick';
    $build['#attached'] = [
      'library' => ['viewer/viewer.apexcharts_candlestick'],
    ];
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state, $params = []) {
    $form = parent::settingsForm($form, $form_state, $params);
    unset($form['options'], $form['general']['labels']);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsValues(array &$form, FormStateInterface $form_state) {
    $settings = parent::settingsValues($form, $form_state);
    unset($settings['chart_curve'], $settings['chart_type'], $settings['labels']);
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function configurationForm(array $form, FormStateInterface $form_state, $params = []) {
    $configuration = $params['configuration'];
    $viewer_source_id = $params['viewer_source'];
    $form['dataset'] = [
      '#type' => 'table',
    ];

    $form['dataset'][0]['timestamp'] = [
      '#type' => 'select',
      '#title' => $this->t('Timestamp'),
      '#title_display' => 'invisible',
      '#options' => $this->getHeaders($viewer_source_id),
      '#empty_option' => $this->t('- Select Timestamp -'),
      "#default_value" => $configuration['datasets'][0]['timestamp'] ?? '',
      '#required' => TRUE,
    ];

    $form['dataset'][0]['open'] = [
      '#type' => 'select',
      '#title' => $this->t('Open'),
      '#title_display' => 'invisible',
      '#options' => $this->getHeaders($viewer_source_id),
      '#empty_option' => $this->t('- Select Open -'),
      "#default_value" => $configuration['datasets'][0]['open'] ?? '',
      '#required' => TRUE,
    ];

    $form['dataset'][0]['high'] = [
      '#type' => 'select',
      '#title' => $this->t('High'),
      '#title_display' => 'invisible',
      '#options' => $this->getHeaders($viewer_source_id),
      '#empty_option' => $this->t('- Select High -'),
      "#default_value" => $configuration['datasets'][0]['high'] ?? '',
      '#required' => TRUE,
    ];

    $form['dataset'][0]['low'] = [
      '#type' => 'select',
      '#title' => $this->t('Low'),
      '#title_display' => 'invisible',
      '#options' => $this->getHeaders($viewer_source_id),
      '#empty_option' => $this->t('- Select Low -'),
      "#default_value" => $configuration['datasets'][0]['low'] ?? '',
      '#required' => TRUE,
    ];

    $form['dataset'][0]['close'] = [
      '#type' => 'select',
      '#title' => $this->t('Close'),
      '#title_display' => 'invisible',
      '#options' => $this->getHeaders($viewer_source_id),
      '#empty_option' => $this->t('- Select Close -'),
      "#default_value" => $configuration['datasets'][0]['close'] ?? '',
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function configurationValues(array &$form, FormStateInterface $form_state) {
    return ['datasets' => $form_state->getValue('dataset')];
  }

}
