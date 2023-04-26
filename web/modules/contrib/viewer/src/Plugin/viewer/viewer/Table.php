<?php

namespace Drupal\viewer\Plugin\viewer\viewer;

use Drupal\viewer\Plugin\ViewerBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Viewer plugin.
 *
 * @Viewer(
 *   id = "table",
 *   name = @Translation("Table"),
 *   processor = "processor_csv",
 *   filters = true,
 *   viewer_types = {
 *     "csv"
 *   }
 * )
 */
class Table extends ViewerBase {

  /**
   * {@inheritdoc}
   */
  public function getRenderable() {
    return [
      '#theme' => 'viewer_table',
      '#uuid' => $this->getId(),
      '#viewer' => $this->getViewer(),
      '#settings' => $this->getSettings(),
      '#last_import' => $this->getViewerSource()->getLastImportRaw(),
      '#attached' => [
        'library' => ['viewer/viewer.table'],
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
      '#weight' => -10,
    ];
    $form['general']['show_all'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show all rows'),
      '#default_value' => isset($settings['show_all']) ? (bool) $settings['show_all'] : FALSE,
    ];
    $form['general']['items_per_load'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of items per page load'),
      '#default_value' => !empty($settings['items_per_load']) ? (int) $settings['items_per_load'] : 10,
      '#states' => [
        'invisible' => [':input[name="show_all"]' => ['checked' => TRUE]],
      ],
    ];
    $form['general']['load_more_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Load More Label'),
      '#default_value' => !empty($settings['load_more_label']) ? $settings['load_more_label'] : 'Show More',
      '#states' => [
        'invisible' => [':input[name="show_all"]' => ['checked' => TRUE]],
      ],
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
    $form['misc'] = [
      '#type' => 'details',
      '#title' => $this->t('Misc'),
      '#group' => 'plugin',
      '#weight' => -6,
    ];
    $form['misc']['add_headers'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add headers to the CSV'),
      '#description' => $this->t('Some CSV files do not have the column headers, this option ensures to add the headers automatically when displaying the file.'),
      '#default_value' => !empty($settings['add_headers']) ? $settings['add_headers'] : FALSE,
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
      'items_per_load' => $form_state->getValue('items_per_load'),
      'load_more_label' => $form_state->getValue('load_more_label'),
      'show_all' => $form_state->getValue('show_all'),
      'add_headers' => $form_state->getValue('add_headers'),
    ];
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function configurationForm(array $form, FormStateInterface $form_state, $params = []) {
    $configuration = $params['configuration'];
    $viewer_source = $params['viewer_source'];
    $group_class = 'group-order-weight';

    $form['configuration'] = [
      '#type' => 'table',
      '#header' => [
        $this->t('Header'), $this->t('Override Header'), $this->t('Hide'),
        $this->t('Empty'), '', $this->t('Converter'), $this->t('Weight'),
      ],
      '#tabledrag' => [[
        'action' => 'order',
        'relationship' => 'sibling',
        'group' => $group_class,
      ],
      ],
    ];

    $weight = 0;
    $metadata = $viewer_source->getMetadata();
    if (!empty($configuration[0]['weight'])) {
      $sorted_metadata = [];
      foreach ($configuration as $key => $details) {
        $sorted_metadata[$details['index']] = $metadata[$details['index']];
      }
      $metadata = $sorted_metadata;
    }

    foreach ($metadata as $key => $label) {
      $form['configuration'][$key]['#attributes']['class'][] = 'draggable';
      $form['configuration'][$key]['#weight'] = !empty($configuration[$key]['weight']) ? $configuration[$key]['weight'] : $weight;
      $form['configuration'][$key]['name'] = [
        '#plain_text' => $label,
      ];
      $form['configuration'][$key]['override_header'] = [
        '#type' => 'textfield',
        '#title' => '',
        '#placeholder' => $this->t('Override column header'),
        '#default_value' => !empty($configuration[$key]['override_header']) ? $configuration[$key]['override_header'] : '',
        '#states' => [
          'disabled' => [
            [':input[data-field-index="' . $key . '"]' => ['checked' => TRUE]],
            'or',
            [':input[data-empty-index="' . $key . '"]' => ['checked' => TRUE]],
          ],
        ],
      ];
      $form['configuration'][$key]['hide'] = [
        '#type' => 'checkbox',
        '#title' => '',
        '#default_value' => !empty($configuration[$key]['hide']) ? $configuration[$key]['hide'] : FALSE,
        '#attributes' => ['data-field-index' => $key],
      ];
      $form['configuration'][$key]['empty'] = [
        '#type' => 'checkbox',
        '#title' => '',
        '#default_value' => !empty($configuration[$key]['empty']) ? $configuration[$key]['empty'] : FALSE,
        '#states' => [
          'disabled' => [
            ':input[data-field-index="' . $key . '"]' => ['checked' => TRUE],
          ],
        ],
        '#attributes' => ['data-empty-index' => $key],
      ];
      $form['configuration'][$key]['index'] = [
        '#type' => 'value',
        '#value' => $key,
      ];
      $form['configuration'][$key]['cell_plugin'] = [
        '#type' => 'select',
        '#title' => '',
        '#options' => $this->getCellPlugins(),
        '#default_value' => !empty($configuration[$key]['cell_plugin']) ? $configuration[$key]['cell_plugin'] : 'as_is',
        '#states' => [
          'disabled' => [
            ':input[data-field-index="' . $key . '"]' => ['checked' => TRUE],
          ],
        ],
      ];
      $form['configuration'][$key]['weight'] = [
        '#type' => 'weight',
        '#title' => '',
        '#title_display' => 'invisible',
        '#default_value' => !empty($configuration[$key]['weight']) ? $configuration[$key]['weight'] : $weight,
        '#attributes' => ['class' => [$group_class]],
        '#delta' => 50,
      ];
      $weight++;
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function configurationValues(array &$form, FormStateInterface $form_state) {
    return $form_state->getValue('configuration');
  }

}
