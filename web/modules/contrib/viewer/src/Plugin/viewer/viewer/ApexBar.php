<?php

namespace Drupal\viewer\Plugin\viewer\viewer;

use Drupal\Core\Form\FormStateInterface;

/**
 * Viewer plugin.
 *
 * @Viewer(
 *   id = "apexchart_bar",
 *   name = @Translation("ApexCharts.js: Bar"),
 *   provider = "viewer",
 *   processor = "processor_csv",
 *   filters = true,
 *   viewer_types = {
 *     "csv"
 *   }
 * )
 */
class ApexBar extends ApexLine {

  /**
   * {@inheritdoc}
   */
  public function getRenderable() {
    $build = parent::getRenderable();
    $build['#wrapper'] = 'bar';
    $build['#type'] = 'bar';
    $build['#attached'] = [
      'library' => ['viewer/viewer.apexcharts_bar'],
    ];
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state, $params = []) {
    $settings = $params['settings'];
    $form = parent::settingsForm($form, $form_state, $params);
    unset($form['options']['chart_curve'], $form['options']['chart_type']);
    $form['options']['chart_horizontal'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Horizontal'),
      '#default_value' => !empty($settings['chart_horizontal']),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsValues(array &$form, FormStateInterface $form_state) {
    $settings = parent::settingsValues($form, $form_state);
    $settings += [
      'chart_horizontal' => $form_state->getValue('chart_horizontal'),
    ];
    return $settings;
  }

}
