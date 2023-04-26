<?php

namespace Drupal\viewer\Plugin\viewer\viewer;

use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;

/**
 * Viewer plugin.
 *
 * @Viewer(
 *   id = "apexchart_line",
 *   name = @Translation("ApexCharts.js: Line"),
 *   provider = "viewer",
 *   processor = "processor_csv",
 *   filters = true,
 *   viewer_types = {
 *     "csv"
 *   }
 * )
 */
class ApexLine extends ChartLine {

  /**
   * {@inheritdoc}
   */
  public function getRenderable() {
    $settings = $this->getSettings();
    return [
      '#theme' => 'viewer_apexchart',
      '#uuid' => $this->getId(),
      '#viewer' => $this->getViewer(),
      '#settings' => $settings,
      '#last_import' => $this->getViewerSource()->getLastImportRaw(),
      '#wrapper' => 'line',
      '#type' => 'line',
      '#labels' => !empty($settings['labels']) ? $settings['labels'] : 0,
      '#attached' => [
        'library' => ['viewer/viewer.apexcharts_line'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state, $params = []) {
    $settings = $params['settings'];
    $form = parent::settingsForm($form, $form_state, $params);
    $form['general'] = [
      '#type' => 'details',
      '#title' => $this->t('General'),
      '#group' => 'plugin',
      '#weight' => -20,
    ];
    $form['general']['chart_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Chart Title'),
      '#default_value' => !empty($settings['chart_title']) ? $settings['chart_title'] : FALSE,
    ];
    $form['general']['chart_title_position'] = [
      '#type' => 'select',
      '#title' => $this->t('Chart Title Position'),
      '#options' => [
        'left' => $this->t('Left'),
        'center' => $this->t('Center'),
        'right' => $this->t('Right'),
      ],
      '#default_value' => !empty($settings['chart_title_position']) ? $settings['chart_title_position'] : 'center',
    ];
    $form['general']['chart_subtitle'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Chart Subtitle'),
      '#default_value' => !empty($settings['chart_subtitle']) ? $settings['chart_subtitle'] : FALSE,
    ];
    $form['general']['chart_subtitle_position'] = [
      '#type' => 'select',
      '#title' => $this->t('Chart Subtitle Position'),
      '#options' => [
        'left' => $this->t('Left'),
        'center' => $this->t('Center'),
        'right' => $this->t('Right'),
      ],
      '#default_value' => !empty($settings['chart_subtitle_position']) ? $settings['chart_subtitle_position'] : 'center',
    ];
    $form['general']['labels'] = [
      '#type' => 'select',
      '#title' => $this->t('Dataset labels'),
      '#options' => $this->getHeaders($params['viewer_source']),
      '#empty_option' => $this->t('- Select Dataset -'),
      '#default_value' => !empty($settings['labels']) ? $settings['labels'] : 0,
      '#description' => $this->t('The y-chart axis labels'),
    ];
    $form['options'] = [
      '#type' => 'details',
      '#title' => $this->t('Options'),
      '#group' => 'plugin',
      '#weight' => -10,
    ];
    $form['options']['chart_curve'] = [
      '#type' => 'select',
      '#title' => $this->t('Curve Style'),
      '#options' => [
        'smooth' => $this->t('Smooth'),
        'straight' => $this->t('Straight'),
        'stepline' => $this->t('Stepline'),
      ],
      '#empty_option' => $this->t('- Select curve style -'),
      '#default_value' => !empty($settings['chart_curve']) ? $settings['chart_curve'] : 'straight',
      '#description' => $this->t('In line / area charts, whether to draw smooth lines or straight lines'),
      '#required' => TRUE,
    ];
    $form['options']['chart_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Chart Type'),
      '#options' => [
        'line' => $this->t('Line'),
        'area' => $this->t('Area'),
      ],
      '#empty_option' => $this->t('- Select type -'),
      '#default_value' => !empty($settings['chart_type']) ? $settings['chart_type'] : 'line',
      '#description' => $this->t('Specify the chart type'),
      '#required' => TRUE,
    ];
    $form['date'] = [
      '#type' => 'details',
      '#title' => $this->t('Date'),
      '#group' => 'plugin',
      '#weight' => -7,
    ];
    $form['date']['last_import_position'] = [
      '#type' => 'select',
      '#title' => $this->t('Last import position'),
      '#options' => [
        0 => $this->t('Hidden'),
        'header' => $this->t('Header'),
        'footer' => $this->t('Footer'),
        'both' => $this->t('Both'),
      ],
      '#default_value' => $settings['last_import_position'] ?? 0,
    ];
    $form['date']['last_import_format'] = [
      '#type' => 'select',
      '#title' => $this->t('Last import date format'),
      '#description' => $this->t('Last import date format. See <a href="@link">Date and time formats.</a>', [
        '@link' => Url::fromRoute('entity.date_format.collection')->toString(),
      ]),
      '#options' => $this->getDateFormats(),
      '#default_value' => $settings['last_import_format'] ?? 'short',
      '#states' => [
        'visible' => [':input[name="show_last_import"]' => ['checked' => TRUE]],
      ],
    ];
    $form['date']['last_import'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last import output'),
      '#default_value' => !empty($settings['last_import']) ? $settings['last_import'] : 'As of @date',
      '#states' => [
        'visible' => [':input[name="show_last_import"]' => ['checked' => TRUE]],
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsValues(array &$form, FormStateInterface $form_state) {
    $settings = parent::settingsValues($form, $form_state);
    $settings += [
      'last_import_position' => $form_state->getValue('last_import_position'),
      'last_import_format' => $form_state->getValue('last_import_format'),
      'last_import' => $form_state->getValue('last_import'),
      'chart_title' => $form_state->getValue('chart_title'),
      'chart_title_position' => $form_state->getValue('chart_title_position'),
      'chart_subtitle' => $form_state->getValue('chart_subtitle'),
      'chart_subtitle_position' => $form_state->getValue('chart_subtitle_position'),
      'chart_curve' => $form_state->getValue('chart_curve'),
      'chart_type' => $form_state->getValue('chart_type'),
      'labels' => $form_state->getValue('labels'),
    ];
    return $settings;
  }

}
