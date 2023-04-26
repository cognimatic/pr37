<?php

namespace Drupal\viewer\Plugin\viewer\viewer;

use Drupal\Core\Form\FormStateInterface;

/**
 * Viewer plugin.
 *
 * @Viewer(
 *   id = "chartjs_bar",
 *   name = @Translation("Chart.js: Bar"),
 *   provider = "viewer",
 *   processor = "processor_csv",
 *   filters = true,
 *   viewer_types = {
 *     "csv"
 *   }
 * )
 */
class ChartBar extends ChartLine {

  /**
   * {@inheritdoc}
   */
  public function getRenderable() {
    $build = parent::getRenderable();
    $build['#type'] = 'bar';
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state, $params = []) {
    $settings = $params['settings'];
    $form = parent::settingsForm($form, $form_state, $params);
    $form['options']['horizontal'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Horizontal bars'),
      '#default_value' => !empty($settings['horizontal']) ? $settings['horizontal'] : FALSE,
      '#states' => [
        'visible' => [':input[name="type"]' => ['value' => 'bar']],
      ],
    ];
    unset($form['options']['chart_type'], $form['options']['chart_curve']);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsValues(array &$form, FormStateInterface $form_state) {
    $settings = parent::settingsValues($form, $form_state);
    $settings += [
      'horizontal' => $form_state->getValue('horizontal'),
    ];
    return $settings;
  }

}
