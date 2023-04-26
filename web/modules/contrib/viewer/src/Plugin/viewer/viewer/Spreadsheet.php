<?php

namespace Drupal\viewer\Plugin\viewer\viewer;

use Drupal\viewer\Plugin\ViewerBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Viewer plugin.
 *
 * @Viewer(
 *   id = "spreadsheet",
 *   name = @Translation("Spreadsheet: Tabs"),
 *   processor = "processor_xlsx",
 *   filters = true,
 *   viewer_types = {
 *     "xlsx"
 *   }
 * )
 */
class Spreadsheet extends ViewerBase {

  /**
   * {@inheritdoc}
   */
  public function getRenderable() {
    return [
      '#theme' => 'viewer_spreadsheet',
      '#uuid' => $this->getId(),
      '#viewer' => $this->getViewer(),
      '#settings' => $this->getSettings(),
      '#configuration' => $this->getConfiguration(),
      '#last_import' => $this->getViewerSource()->getLastImportRaw(),
      '#attached' => [
        'library' => ['viewer/viewer.spreadsheet'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state, $params = []) {
    $settings = $params['settings'];
    $form = parent::settingsForm($form, $form_state, $params);
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

    $form['plugin'] = [
      '#type' => 'vertical_tabs',
    ];

    $worksheets = [];
    $element_ids = [];

    $metadata = $viewer_source->getMetadata();
    foreach ($metadata as $worksheet => $headers) {
      $machine_name = $this->getMachineName($worksheet);
      $tab_id = 'worksheet_' . $machine_name;
      $conf_id = $machine_name;
      $group_class = 'group-' . $machine_name . '-class';
      // We need this for the save loop.
      $element_ids[] = $conf_id;
      $worksheets[$machine_name] = $worksheet;

      $form[$tab_id] = [
        '#type' => 'details',
        '#title' => $worksheet,
        '#group' => 'plugin',
      ];

      $form[$tab_id]['overridden_label_' . $conf_id] = [
        '#type' => 'textfield',
        '#title' => '',
        '#placeholder' => $this->t('Override worksheet label'),
        '#default_value' => !empty($configuration['worksheet_overrides'][$conf_id]['overridden_label']) ? $configuration['worksheet_overrides'][$conf_id]['overridden_label'] : '',
      ];

      $form[$tab_id]['disabled_' . $conf_id] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Hide this worksheet from display'),
        '#default_value' => !empty($configuration['worksheet_overrides'][$conf_id]['disabled']) ? $configuration['worksheet_overrides'][$conf_id]['disabled'] : '',
      ];

      $form[$tab_id][$conf_id] = [
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
      if (!empty($configuration['worksheets'][$conf_id][0]['weight'])) {
        $sorted_metadata = [];
        foreach ($configuration['worksheets'][$conf_id] as $key => $details) {
          $sorted_metadata[$details['index']] = $headers[$details['index']];
        }
        $headers = $sorted_metadata;
      }

      foreach ($headers as $key => $label) {
        $form[$tab_id][$conf_id][$key]['#attributes']['class'][] = 'draggable';
        $form[$tab_id][$conf_id][$key]['#weight'] = !empty($configuration['worksheets'][$conf_id][$key]['weight']) ? $configuration['worksheets'][$conf_id][$key]['weight'] : $weight;
        $form[$tab_id][$conf_id][$key]['name'] = [
          '#plain_text' => $label,
        ];
        $form[$tab_id][$conf_id][$key]['override_header'] = [
          '#type' => 'textfield',
          '#title' => '',
          '#placeholder' => $this->t('Override column header'),
          '#default_value' => !empty($configuration['worksheets'][$conf_id][$key]['override_header']) ? $configuration['worksheets'][$conf_id][$key]['override_header'] : '',
          '#states' => [
            'disabled' => [
              [':input[data-field-index="' . $conf_id . $key . '"]' => ['checked' => TRUE]],
              'or',
              [':input[data-empty-index="' . $conf_id . $key . '"]' => ['checked' => TRUE]],
            ],
          ],
        ];
        $form[$tab_id][$conf_id][$key]['hide'] = [
          '#type' => 'checkbox',
          '#title' => '',
          '#default_value' => !empty($configuration['worksheets'][$conf_id][$key]['hide']) ? $configuration['worksheets'][$conf_id][$key]['hide'] : FALSE,
          '#attributes' => ['data-field-index' => $conf_id . $key],
        ];
        $form[$tab_id][$conf_id][$key]['empty'] = [
          '#type' => 'checkbox',
          '#title' => '',
          '#default_value' => !empty($configuration['worksheets'][$conf_id][$key]['empty']) ? $configuration['worksheets'][$conf_id][$key]['empty'] : FALSE,
          '#states' => [
            'disabled' => [
              ':input[data-field-index="' . $conf_id . $key . '"]' => ['checked' => TRUE],
            ],
          ],
          '#attributes' => ['data-empty-index' => $conf_id . $key],
        ];
        $form[$tab_id][$conf_id][$key]['index'] = [
          '#type' => 'value',
          '#value' => $key,
        ];
        $form[$tab_id][$conf_id][$key]['cell_plugin'] = [
          '#type' => 'select',
          '#title' => '',
          '#options' => $this->getCellPlugins(),
          '#default_value' => !empty($configuration['worksheets'][$conf_id][$key]['cell_plugin']) ? $configuration['worksheets'][$conf_id][$key]['cell_plugin'] : 'as_is',
          '#states' => [
            'disabled' => [
              ':input[data-field-index="' . $conf_id . $key . '"]' => ['checked' => TRUE],
            ],
          ],
        ];
        $form[$tab_id][$conf_id][$key]['weight'] = [
          '#type' => 'weight',
          '#title' => '',
          '#title_display' => 'invisible',
          '#default_value' => !empty($configuration['worksheets'][$conf_id][$key]['weight']) ? $configuration['worksheets'][$conf_id][$key]['weight'] : $weight,
          '#attributes' => ['class' => [$group_class]],
          '#delta' => 50,
        ];
        $weight++;
      }
    }

    $form_state->set('element_ids', $element_ids);
    $form_state->set('worksheets', $worksheets);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function configurationValues(array &$form, FormStateInterface $form_state) {
    $values = [];
    if ($element_ids = $form_state->get('element_ids')) {
      $values['worksheet_labels'] = $form_state->get('worksheets');
      foreach ($element_ids as $element) {
        $values['worksheets'][$element] = $form_state->getValue($element);
        $values['worksheet_overrides'][$element] = [
          'overridden_label' => $form_state->getValue('overridden_label_' . $element),
          'disabled' => $form_state->getValue('disabled_' . $element),
        ];
      }
    }
    return $values;
  }

}
