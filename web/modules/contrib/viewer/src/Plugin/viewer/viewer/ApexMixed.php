<?php

namespace Drupal\viewer\Plugin\viewer\viewer;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Xss;

/**
 * Viewer plugin.
 *
 * @Viewer(
 *   id = "apexchart_mixed",
 *   name = @Translation("ApexCharts.js: Mixed"),
 *   provider = "viewer",
 *   processor = "processor_csv",
 *   filters = true,
 *   viewer_types = {
 *     "csv"
 *   }
 * )
 */
class ApexMixed extends ApexLine {

  /**
   * {@inheritdoc}
   */
  public function getRenderable() {
    $build = parent::getRenderable();
    $build['#wrapper'] = 'mixed';
    $build['#type'] = 'mixed';
    $build['#attached'] = [
      'library' => ['viewer/viewer.apexcharts_mixed'],
    ];
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state, $params = []) {
    $form = parent::settingsForm($form, $form_state, $params);
    unset($form['options']);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsValues(array &$form, FormStateInterface $form_state) {
    $settings = parent::settingsValues($form, $form_state);
    unset($settings['chart_curve'], $settings['chart_type']);
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function configurationForm(array $form, FormStateInterface $form_state, $params = []) {
    $configuration = $params['configuration'];
    if (empty($form_state->get('datasets')) && !empty($configuration['datasets']) && empty($form_state->get('datasets_loaded'))) {
      $form_state->set('datasets_loaded', TRUE);
      $form_state->set('datasets', $configuration['datasets']);
    }
    $viewer_source_id = $params['viewer_source'];
    $datasets = !empty($form_state->get('datasets')) ? $form_state->get('datasets') : [];
    $wrapper_id = 'viewer-mixed-charts-wrapper';
    $group_class = 'group-order-weight';

    $form['datasets'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Dataset'), $this->t('Type'),
        $this->t('Label'), $this->t('Color'), '', $this->t('Weight'),
      ],
      '#tabledrag' => [[
        'action' => 'order',
        'relationship' => 'sibling',
        'group' => $group_class,
      ],
      ],
      '#empty' => $this->t('There are no datasets. Please add a dataset below.'),
      '#prefix' => '<div id="' . $wrapper_id . '">',
      '#suffix' => '</div>',
    ];

    $weight = 0;
    foreach ($datasets as $header_index => $details) {
      $form['datasets'][$header_index]['#attributes']['class'][] = 'draggable';
      $form['datasets'][$header_index]['#weight'] = $weight;

      $form['datasets'][$header_index]['dataset'] = [
        '#type' => 'select',
        '#title' => $this->t('Dataset'),
        '#title_display' => 'invisible',
        '#options' => $this->getHeaders($viewer_source_id),
        '#empty_option' => $this->t('- Select Dataset -'),
        '#default_value' => ($details['dataset'] != '') ? $details['dataset'] : '',
      ];

      $form['datasets'][$header_index]['type'] = [
        '#type' => 'select',
        '#title' => $this->t('Type'),
        '#title_display' => 'invisible',
        '#options' => [
          'line' => $this->t('Line'),
          'area' => $this->t('Area'),
          'column' => $this->t('Column'),
          'scatter' => $this->t('Scatter'),
        ],
        '#default_value' => ($details['type'] != '') ? $details['type'] : '',
        '#required' => TRUE,
      ];

      $form['datasets'][$header_index]['label'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Label'),
        '#title_display' => 'invisible',
        '#default_value' => !empty($details['label']) ? Xss::filter($details['label']) : '',
        '#placeholder' => $this->t('Chart dataset label'),
      ];

      $form['datasets'][$header_index]['color'] = [
        '#type' => 'color',
        '#title' => $this->t('Color'),
        '#title_display' => 'invisible',
        '#default_value' => !empty($details['color']) ? Xss::filter($details['color']) : '',
        '#placeholder' => $this->t('Chart color'),
      ];

      $form['datasets'][$header_index]['remove'] = [
        '#type' => 'submit',
        '#value' => $this->t('x'),
        '#submit' => [
          [$this, 'removeDataset'],
        ],
        '#name' => $header_index,
        '#ajax' => [
          'callback' => [$this, 'ajaxCallback'],
          'wrapper' => $wrapper_id,
        ],
      ];

      $form['datasets'][$header_index]['weight'] = [
        '#type' => 'weight',
        '#title' => '',
        '#title_display' => 'invisible',
        '#default_value' => $weight,
        '#attributes' => ['class' => [$group_class]],
      ];
      $weight++;
    }

    $form['new'] = [
      '#type' => 'table',
    ];

    $form['new'][0]['dataset'] = [
      '#type' => 'select',
      '#title' => $this->t('Dataset'),
      '#title_display' => 'invisible',
      '#options' => $this->getHeaders($viewer_source_id),
      '#empty_option' => $this->t('- Select Dataset -'),
    ];

    $form['new'][0]['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Type'),
      '#title_display' => 'invisible',
      '#options' => [
        'line' => $this->t('Line'),
        'area' => $this->t('Area'),
        'column' => $this->t('Column'),
        'scatter' => $this->t('Scatter'),
      ],
      '#empty_option' => $this->t('- Select Type -'),
    ];

    $form['new'][0]['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#title_display' => 'invisible',
      '#placeholder' => $this->t('Chart dataset label'),
    ];

    $form['new'][0]['color'] = [
      '#type' => 'color',
      '#title' => $this->t('Color'),
      '#title_display' => 'invisible',
      '#placeholder' => $this->t('Chart color'),
    ];

    $form['new'][0]['add'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add dataset'),
      '#submit' => [
        [$this, 'addDataset'],
      ],
      '#ajax' => [
        'callback' => [$this, 'ajaxCallback'],
        'wrapper' => $wrapper_id,
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function configurationValues(array &$form, FormStateInterface $form_state) {
    $datasets = [];
    foreach ($form_state->getValue('datasets') as $index => $details) {
      $datasets[$index] = [
        'dataset' => $details['dataset'],
        'type' => $details['type'],
        'label' => $details['label'],
        'color' => $details['color'],
        'weight' => $details['weight'],
      ];
    }
    return ['datasets' => $datasets];
  }

  /**
   * Submit handler for the "add tab" button.
   */
  public static function addDataset(array &$form, FormStateInterface $form_state) {
    $datasets = !empty($form_state->get('datasets')) ? $form_state->get('datasets') : [];
    $new = $form_state->getValue('new');
    $datasets[count($datasets) + 1] = [
      'dataset' => $new[0]['dataset'],
      'type' => $new[0]['type'],
      'label' => $new[0]['label'],
      'color' => $new[0]['color'],
    ];
    $form_state->set('datasets', $datasets)->setRebuild();
  }

}
