<?php

namespace Drupal\viewer\Plugin\viewer\viewer;

use Drupal\Core\Form\FormStateInterface;

/**
 * Viewer plugin.
 *
 * @Viewer(
 *   id = "chartjs_piedoughnut",
 *   name = @Translation("Chart.js: Pie/Doughnut"),
 *   provider = "viewer",
 *   processor = "processor_csv",
 *   filters = true,
 *   viewer_types = {
 *     "csv"
 *   }
 * )
 */
class ChartPieDoughnut extends ChartLine {

  /**
   * {@inheritdoc}
   */
  public function getRenderable() {
    $settings = $this->getSettings();
    return [
      '#theme' => 'viewer_chartjs',
      '#uuid' => $this->getId(),
      '#viewer' => $this->getViewer(),
      '#settings' => $settings,
      '#last_import' => $this->getViewerSource()->getLastImportRaw(),
      '#wrapper' => 'piedoughnut',
      '#type' => !empty($settings['chart_type']) ? $settings['chart_type'] : 'pie',
      '#labels' => !empty($settings['labels']) ? $settings['labels'] : 0,
      '#attached' => [
        'library' => ['viewer/viewer.piedoughnut'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state, $params = []) {
    $form = parent::settingsForm($form, $form_state, $params);
    $settings = $params['settings'];
    $form['general']['labels'] = [
      '#type' => 'select',
      '#title' => $this->t('Dataset labels'),
      '#options' => $this->getHeaders($params['viewer_source']),
      '#empty_option' => $this->t('- Select Dataset -'),
      '#default_value' => !empty($settings['labels']) ? $settings['labels'] : 0,
      '#description' => $this->t('The y-chart axis labels'),
      '#states' => [
        'invisible' => [':input[name="aggregate"]' => ['checked' => TRUE]],
      ],
    ];
    $form['general']['aggregate'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Aggregate values'),
      '#default_value' => !empty($settings['aggregate']) ? $settings['aggregate'] : FALSE,
      '#description' => $this->t('Aggregation will ignore Dataset labels option and use column titles as labels. Colors will be generate randomly. IMPORTANT: Aggregation only works with numeric values'),
    ];
    $form['options']['chart_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Chart Type'),
      '#options' => [
        'pie' => $this->t('Pie'),
        'doughnut' => $this->t('Doughnut'),
      ],
      '#default_value' => !empty($settings['chart_type']) ? $settings['chart_type'] : 'pie',
      '#required' => TRUE,
    ];
    unset($form['options']['chart_curve']);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsValues(array &$form, FormStateInterface $form_state) {
    $settings = parent::settingsValues($form, $form_state);
    unset($settings['chart_curve']);
    $settings += [
      'aggregate' => (bool) $form_state->getValue('aggregate'),
    ];
    return $settings;
  }

}
