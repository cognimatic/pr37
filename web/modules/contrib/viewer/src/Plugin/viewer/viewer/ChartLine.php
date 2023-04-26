<?php

namespace Drupal\viewer\Plugin\viewer\viewer;

use Drupal\viewer\Plugin\ViewerBase;
use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;
use Drupal\viewer\Entity\ViewerSourceInterface;
use Drupal\Component\Utility\Xss;

/**
 * Viewer plugin.
 *
 * @Viewer(
 *   id = "chartjs_line",
 *   name = @Translation("Chart.js: Line"),
 *   provider = "viewer",
 *   processor = "processor_csv",
 *   filters = true,
 *   viewer_types = {
 *     "csv"
 *   }
 * )
 */
class ChartLine extends ViewerBase {

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
      '#wrapper' => 'linebar',
      '#type' => 'line',
      '#labels' => !empty($settings['labels']) ? $settings['labels'] : 0,
      '#attached' => [
        'library' => ['viewer/viewer.linebar'],
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
        'start' => $this->t('Left'),
        'center' => $this->t('Center'),
        'end' => $this->t('Right'),
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
        'start' => $this->t('Left'),
        'center' => $this->t('Center'),
        'end' => $this->t('Right'),
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
        0 => $this->t('Straight'),
        '0.4' => $this->t('Smooth'),
        9 => $this->t('Stepline'),
      ],
      '#empty_option' => $this->t('- Select curve style -'),
      '#default_value' => !empty($settings['chart_curve']) ? $settings['chart_curve'] : 0,
      '#description' => $this->t('In line / area charts, whether to draw smooth lines or straight lines'),
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
        $this->t('Dataset'), $this->t('Label'),
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

      $form['datasets'][$header_index]['dataset'] = [
        '#type' => 'select',
        '#title' => $this->t('Dataset'),
        '#title_display' => 'invisible',
        '#options' => $this->getHeaders($viewer_source_id),
        '#empty_option' => $this->t('- Select Dataset -'),
        '#default_value' => ($details['dataset'] != '') ? $details['dataset'] : '',
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
        'label' => $details['label'],
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
      'dataset' => $new[0]['dataset'],
      'label' => $new[0]['label'],
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

  /**
   * Get CSV headers (column headers).
   */
  protected function getHeaders($viewer_source) {
    if ($viewer_source instanceof ViewerSourceInterface) {
      return $viewer_source->getMetadata();
    }
    else {
      if ($viewer_source = \Drupal::entityTypeManager()->getStorage('viewer_source')->load($viewer_source)) {
        return $viewer_source->getMetadata();
      }
    }
  }

}
