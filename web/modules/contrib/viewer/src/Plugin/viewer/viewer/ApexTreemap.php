<?php

namespace Drupal\viewer\Plugin\viewer\viewer;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Xss;

/**
 * Viewer plugin.
 *
 * @Viewer(
 *   id = "apexchart_treemap",
 *   name = @Translation("ApexCharts.js: Treemap"),
 *   provider = "viewer",
 *   processor = "processor_csv",
 *   filters = true,
 *   viewer_types = {
 *     "csv"
 *   }
 * )
 */
class ApexTreemap extends ApexLine {

  /**
   * {@inheritdoc}
   */
  public function getRenderable() {
    $build = parent::getRenderable();
    $build['#wrapper'] = 'treemap';
    $build['#type'] = 'treemap';
    $build['#attached'] = [
      'library' => ['viewer/viewer.apexcharts_treemap'],
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
    if (empty($form_state->get('datasets')) && !empty($configuration['datasets']) && empty($form_state->get('datasets_loaded'))) {
      $form_state->set('datasets_loaded', TRUE);
      $form_state->set('datasets', $configuration['datasets']);
    }
    $viewer_source_id = $params['viewer_source'];
    $datasets = !empty($form_state->get('datasets')) ? $form_state->get('datasets') : [];
    $wrapper_id = 'viewer-tabs-wrapper';
    $group_class = 'group-order-weight';

    $form['datasets'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Labels'), $this->t('Values'), $this->t('Data Type'),
        $this->t('Color'), '', $this->t('Weight'),
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

      $form['datasets'][$header_index]['labels'] = [
        '#type' => 'select',
        '#title' => $this->t('Labels'),
        '#title_display' => 'invisible',
        '#options' => $this->getHeaders($viewer_source_id),
        '#empty_option' => $this->t('- Select Dataset -'),
        '#default_value' => ($details['labels'] != '') ? $details['labels'] : '',
      ];

      $form['datasets'][$header_index]['values'] = [
        '#type' => 'select',
        '#title' => $this->t('Values'),
        '#title_display' => 'invisible',
        '#options' => $this->getHeaders($viewer_source_id),
        '#empty_option' => $this->t('- Select Dataset -'),
        '#default_value' => ($details['values'] != '') ? $details['values'] : '',
      ];

      $form['datasets'][$header_index]['data_type'] = [
        '#type' => 'select',
        '#title' => $this->t('Data Type'),
        '#title_display' => 'invisible',
        '#options' => [
          'float' => $this->t('Float'),
          'integer' => $this->t('Integer'),
        ],
        '#empty_option' => $this->t('- Select Data Type -'),
        '#default_value' => ($details['data_type'] != '') ? $details['data_type'] : '',
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

    $form['new'][0]['labels'] = [
      '#type' => 'select',
      '#title' => $this->t('Labels'),
      '#title_display' => 'invisible',
      '#options' => $this->getHeaders($viewer_source_id),
      '#empty_option' => $this->t('- Select Dataset -'),
    ];

    $form['new'][0]['values'] = [
      '#type' => 'select',
      '#title' => $this->t('Values'),
      '#title_display' => 'invisible',
      '#options' => $this->getHeaders($viewer_source_id),
      '#empty_option' => $this->t('- Select Dataset -'),
    ];

    $form['new'][0]['data_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Data Type'),
      '#title_display' => 'invisible',
      '#options' => [
        'float' => $this->t('Float'),
        'integer' => $this->t('Integer'),
      ],
      '#empty_option' => $this->t('- Select Data Type -'),
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
        'labels' => $details['labels'],
        'values' => $details['values'],
        'data_type' => $details['data_type'],
        'color' => $details['color'],
        'weight' => $details['weight'],
      ];
    }
    return ['datasets' => $datasets];
  }

  /**
   * Callback for both ajax-enabled buttons.
   */
  public static function ajaxCallback(array &$form, FormStateInterface $form_state) {
    return $form['datasets'];
  }

  /**
   * Submit handler for the "add tab" button.
   */
  public static function addDataset(array &$form, FormStateInterface $form_state) {
    $datasets = !empty($form_state->get('datasets')) ? $form_state->get('datasets') : [];
    $new = $form_state->getValue('new');
    $datasets[count($datasets) + 1] = [
      'labels' => $new[0]['labels'],
      'values' => $new[0]['values'],
      'data_type' => $new[0]['data_type'],
      'color' => $new[0]['color'],
    ];
    $form_state->set('datasets', $datasets)->setRebuild();
  }

  /**
   * Submit handler for the "remove tab" button.
   */
  public static function removeDataset(array &$form, FormStateInterface $form_state) {
    $datasets = $form_state->get('datasets');
    $triggering_element = $form_state->getTriggeringElement();
    $index = $triggering_element['#name'];
    if (isset($datasets[$index])) {
      unset($datasets[$index]);
    }
    $form_state->set('datasets', $datasets)->setRebuild();
  }

}
