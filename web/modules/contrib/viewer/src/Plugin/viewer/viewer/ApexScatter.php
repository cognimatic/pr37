<?php

namespace Drupal\viewer\Plugin\viewer\viewer;

use Drupal\Core\Form\FormStateInterface;

/**
 * Viewer plugin.
 *
 * @Viewer(
 *   id = "apexchart_scatter",
 *   name = @Translation("ApexCharts.js: Scatter"),
 *   provider = "viewer",
 *   processor = "processor_csv",
 *   filters = true,
 *   viewer_types = {
 *     "csv"
 *   }
 * )
 */
class ApexScatter extends ApexLine {

  /**
   * {@inheritdoc}
   */
  public function getRenderable() {
    $build = parent::getRenderable();
    $build['#wrapper'] = 'scatterbubble';
    $build['#type'] = 'scatter';
    $build['#attached'] = [
      'library' => ['viewer/viewer.apexcharts_scatterbubble'],
    ];
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state, $params = []) {
    $settings = $params['settings'];
    $form = parent::settingsForm($form, $form_state, $params);
    unset($form['general']['labels'], $form['options']['chart_curve'], $form['options']['chart_type']);
    $form['options']['separator'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Value Separator'),
      '#size' => 5,
      '#default_value' => !empty($settings['separator']) ? $settings['separator'] : ',',
      '#description' => $this->t('This parameter controls how to split (x,y) chart coordinate values from datasets.'),
      '#required' => TRUE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsValues(array &$form, FormStateInterface $form_state) {
    $settings = parent::settingsValues($form, $form_state);
    unset($settings['labels'], $settings['chart_curve'], $settings['chart_type']);
    $settings['separator'] = $form_state->getValue('separator');
    return $settings;
  }

}
