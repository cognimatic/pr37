<?php

namespace Drupal\viewer\Plugin\viewer\viewer;

use Drupal\Core\Form\FormStateInterface;

/**
 * Viewer plugin.
 *
 * @Viewer(
 *   id = "apexchart_piedoughnut",
 *   name = @Translation("ApexCharts.js: Pie/Doughnut"),
 *   provider = "viewer",
 *   processor = "processor_csv",
 *   filters = true,
 *   viewer_types = {
 *     "csv"
 *   }
 * )
 */
class ApexPieDoughnut extends ChartPieDoughnut {

  /**
   * {@inheritdoc}
   */
  public function getRenderable() {
    $build = parent::getRenderable();
    $build['#type'] = $build['#type'] == 'pie' ? 'pie' : 'donut';
    $build['#theme'] = 'viewer_apexchart';
    $build['#wrapper'] = 'piedoughnut';
    $build['#attached'] = [
      'library' => ['viewer/viewer.apexcharts_piedoughnut'],
    ];
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state, $params = []) {
    $form = parent::settingsForm($form, $form_state, $params);
    unset($form['general']['aggregate']);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsValues(array &$form, FormStateInterface $form_state) {
    $settings = parent::settingsValues($form, $form_state);
    unset($settings['aggregate']);
    return $settings;
  }

}
