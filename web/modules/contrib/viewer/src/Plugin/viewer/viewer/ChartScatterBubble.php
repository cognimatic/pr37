<?php

namespace Drupal\viewer\Plugin\viewer\viewer;

use Drupal\Core\Form\FormStateInterface;

/**
 * Viewer plugin.
 *
 * @Viewer(
 *   id = "chartjs_scatterbubble",
 *   name = @Translation("Chart.js: Scatter/Bubble"),
 *   provider = "viewer",
 *   processor = "processor_csv",
 *   filters = true,
 *   viewer_types = {
 *     "csv"
 *   }
 * )
 */
class ChartScatterBubble extends ChartLine {

  /**
   * {@inheritdoc}
   */
  public function getRenderable() {
    $settings = $this->getSettings();
    $build = parent::getRenderable();
    $build['#wrapper'] = 'scatterbubble';
    $build['#type'] = !empty($settings['chart_type']) ? $settings['chart_type'] : 'scatter';
    $build['#attached'] = [
      'library' => ['viewer/viewer.scatterbubble'],
    ];
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state, $params = []) {
    $settings = $params['settings'];
    $form = parent::settingsForm($form, $form_state, $params);
    $form['options']['separator'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Value Separator'),
      '#size' => 5,
      '#default_value' => !empty($settings['separator']) ? $settings['separator'] : ',',
      '#description' => $this->t('This parameter controls how to split (x,y) chart coordinate values from datasets.'),
      '#required' => TRUE,
    ];
    $form['options']['chart_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Chart Type'),
      '#options' => [
        'scatter' => $this->t('Scatter'),
        'bubble' => $this->t('Bubble'),
      ],
      '#default_value' => !empty($settings['chart_type']) ? $settings['chart_type'] : 'scatter',
      '#weight' => -10,
    ];
    unset($form['general']['labels'], $form['options']['chart_curve']);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsValues(array &$form, FormStateInterface $form_state) {
    $settings = parent::settingsValues($form, $form_state);
    unset($settings['labels'], $settings['chart_curve']);
    $settings['separator'] = $form_state->getValue('separator');
    return $settings;
  }

}
