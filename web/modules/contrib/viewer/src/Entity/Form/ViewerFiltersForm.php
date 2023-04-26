<?php

namespace Drupal\viewer\Entity\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Url;
use Drupal\viewer\Entity\ViewerSourceInterface;

/**
 * ViewerFiltersForm form controller for plugin filters.
 *
 * @ingroup viewer
 */
class ViewerFiltersForm extends BaseContentEntityForm {

  /**
   * Check if saved data is loaded.
   *
   * @var bool
   */
  protected $loaded = FALSE;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    unset($form['actions']['delete'], $form['name'], $form['viewer_plugin'], $form['viewer_source']);
    $filters = $this->entity->getFilters();
    if (empty($form_state->get('filters')) && !empty($filters) && $this->loaded == FALSE) {
      $this->loaded = TRUE;
      $form_state->set('filters', $filters);
    }
    $filter_options = !empty($form_state->get('filters')) ? $form_state->get('filters') : [];
    $wrapper_id = 'viewer-filters-wrapper';

    $form['filters'] = [
      '#type' => 'table',
      '#header' => [$this->t('Column'), $this->t('Criteria'), '', '', ''],
      '#empty' => $this->t('There are no filters yet, please add filters below.'),
      '#prefix' => '<div id="' . $wrapper_id . '">',
      '#suffix' => '</div>',
    ];

    foreach ($filter_options as $index => $details) {

      $form['filters'][$index]['column'] = [
        '#type' => 'select',
        '#title' => $this->t('Column'),
        '#title_display' => 'invisible',
        '#options' => $this->getHeaders($this->entity->getViewerSource()),
        '#empty_option' => $this->t('- Column -'),
        '#default_value' => $details['column'] ?? '',
      ];

      $form['filters'][$index]['criteria'] = [
        '#type' => 'select',
        '#title' => $this->t('Criteria'),
        '#title_display' => 'invisible',
        '#options' => $this->getCriteriaOptions(),
        '#empty_option' => $this->t('- Criteria -'),
        '#default_value' => !empty($details['criteria']) ? $details['criteria'] : '',
      ];

      $form['filters'][$index]['value'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Value'),
        '#title_display' => 'invisible',
        '#default_value' => !empty($details['value']) ? $details['value'] : '',
        '#placeholder' => $this->t('Criteria Value'),
        '#states' => [
          'invisible' => [
            ':input[name="filters[' . $index . '][criteria]"]' => [
            ['value' => 'IS_EMPTY'],
            ['value' => 'IS_NOTEMPTY'],
            ],
          ],
        ],
      ];

      $form['filters'][$index]['format'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Format'),
        '#title_display' => 'invisible',
        '#default_value' => !empty($details['format']) ? $details['format'] : '',
        '#placeholder' => $this->t('Date Format'),
        '#states' => [
          'visible' => [
            ':input[name="filters[' . $index . '][criteria]"]' => [
            ['value' => 'EQUALS_DATE'],
            ['value' => 'GT_DATE'],
            ['value' => 'GTE_DATE'],
            ['value' => 'LT_DATE'],
            ['value' => 'LTE_DATE'],
            ],
          ],
        ],
      ];

      $form['filters'][$index]['remove'] = [
        '#type' => 'submit',
        '#value' => $this->t('x'),
        '#submit' => [
          [$this, 'removeFilter'],
        ],
        '#name' => $index,
        '#ajax' => [
          'callback' => [$this, 'ajaxCallback'],
          'wrapper' => $wrapper_id,
        ],
      ];
    }

    $form['new'] = [
      '#type' => 'table',
      '#suffix' => '<div class="form-item__description">' . $this->t('Filter uses original cell values for filtering. Converted values are ignored.') . '</div>',
    ];

    $form['new'][0]['column'] = [
      '#type' => 'select',
      '#title' => $this->t('Column'),
      '#title_display' => 'invisible',
      '#options' => $this->getHeaders($this->entity->getViewerSource()),
      '#empty_option' => $this->t('- Column -'),
    ];

    $form['new'][0]['criteria'] = [
      '#type' => 'select',
      '#title' => $this->t('Criteria'),
      '#title_display' => 'invisible',
      '#options' => $this->getCriteriaOptions(),
      '#empty_option' => $this->t('- Criteria -'),
    ];

    $form['new'][0]['value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Value'),
      '#title_display' => 'invisible',
      '#placeholder' => $this->t('Criteria Value'),
      '#states' => [
        'invisible' => [
          ':input[name="new[0][criteria]"]' => [
          ['value' => 'IS_EMPTY'],
          ['value' => 'IS_NOTEMPTY'],
          ],
        ],
      ],
    ];

    $form['new'][0]['format'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Format'),
      '#title_display' => 'invisible',
      '#placeholder' => $this->t('Date Format'),
      '#states' => [
        'visible' => [
          ':input[name="new[0][criteria]"]' => [
          ['value' => 'EQUALS_DATE'],
          ['value' => 'GT_DATE'],
          ['value' => 'GTE_DATE'],
          ['value' => 'LT_DATE'],
          ['value' => 'LTE_DATE'],
          ],
        ],
      ],
    ];

    $form['new'][0]['add'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add filter'),
      '#submit' => [
        [$this, 'addFilter'],
      ],
      '#ajax' => [
        'callback' => [$this, 'ajaxCallback'],
        'wrapper' => $wrapper_id,
      ],
    ];

    $form['actions']['cancel'] = [
      '#type' => 'link',
      '#url' => Url::fromRoute('entity.viewer.collection'),
      '#title' => $this->t('Cancel'),
      '#attributes' => [
        'class' => ['button', 'dialog-cancel'],
      ],
      '#weight' => 5,
    ];
    $form['actions']['#weight'] = 999;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->entity->setFilters($form_state->getValue('filters'));
    $message = $this->t('%label viewer filters updated', [
      '%label' => $this->entity->label(),
    ]);
    $this->loggerFactory->get('viewer')->notice($message);
    $this->messenger->addMessage($message);
    parent::save($form, $form_state);
  }

  /**
   * Callback for both ajax-enabled buttons.
   */
  public static function ajaxCallback(array &$form, FormStateInterface $form_state) {
    return $form['filters'];
  }

  /**
   * Submit handler for the "add tab" button.
   */
  public static function addFilter(array &$form, FormStateInterface $form_state) {
    $filters = !empty($form_state->get('filters')) ? $form_state->get('filters') : [];
    $new = $form_state->getValue('new');
    $filters[count($filters) + 1] = [
      'column'   => $new[0]['column'],
      'criteria' => $new[0]['criteria'],
      'value'    => $new[0]['value'],
      'format'    => $new[0]['format'],
    ];
    $form_state->set('remove', TRUE);
    $form_state->set('filters', $filters)->setRebuild();
  }

  /**
   * Submit handler for the "remove tab" button.
   */
  public static function removeFilter(array &$form, FormStateInterface $form_state) {
    $filters = $form_state->get('filters');
    $triggering_element = $form_state->getTriggeringElement();
    if ($remove_index = $triggering_element['#name']) {
      if (!empty($filters[$remove_index])) {
        unset($filters[$remove_index]);
      }
    }
    $form_state->set('filters', $filters)->setRebuild();
  }

  /**
   * Criteria operation values.
   */
  protected function getCriteriaOptions() {
    return [
      '=' => $this->t('Is equal to'),
      '>' => $this->t('Is greater than'),
      '>=' => $this->t('Is greater than or equal to'),
      '<' => $this->t('Is less than'),
      '<=' => $this->t('Is less than or equal to'),
      '!=' => $this->t('Is not equal to'),
      'CONTAINS' => $this->t('Contains'),
      'STARTS_WITH' => $this->t('Starts with'),
      'ENDS_WITH' => $this->t('Ends with'),
      'IN_ARRAY' => $this->t('In array'),
      'EQUALS_DATE' => $this->t('Date is equal to'),
      'GTE_DATE' => $this->t('Date is greater than or equal to'),
      'LT_DATE' => $this->t('Date is less than'),
      'LTE_DATE' => $this->t('Date is less than or equal to'),
      'IS_EMPTY' => $this->t('Is empty (NULL)'),
      'IS_NOTEMPTY' => $this->t('Is not empty (NOT NULL)'),
      'REGEX' => $this->t('Regular expression'),
    ];
  }

  /**
   * Get CSV headers (column headers).
   */
  protected function getHeaders($viewer_source) {
    if ($viewer_source instanceof ViewerSourceInterface) {
      if ($viewer_source->getTypePluginId() == 'xlsx') {
        return $this->getXlsxOptions($viewer_source);
      }
      return $this->getMetadataOverrides($viewer_source->getMetadata(), $this->entity->getConfiguration());
    }
    else {
      if ($viewer_source = \Drupal::entityTypeManager()->getStorage('viewer_source')->load($viewer_source)) {
        if ($viewer_source->getTypePluginId() == 'xlsx') {
          return $this->getXlsxOptions($viewer_source);
        }
        return $this->getMetadataOverrides($viewer_source->getMetadata(), $this->entity->getConfiguration());
      }
    }
  }

  /**
   * Proper filter options for XLSX.
   */
  protected function getXlsxOptions($viewer_source) {
    $options = [];
    foreach ($viewer_source->getMetadata() as $worksheet_id => $headers) {
      foreach ($headers as $index => $header) {
        $options[$worksheet_id][$this->getMachineName($worksheet_id) . '|' . $index] = $header;
      }
    }
    return $options;
  }

  /**
   * Generates a machine name from a string.
   */
  protected function getMachineName($string) {
    $transliterated = \Drupal::transliteration()->transliterate($string, LanguageInterface::LANGCODE_DEFAULT, '_');
    $transliterated = mb_strtolower($transliterated);
    $transliterated = preg_replace('@[^a-z0-9_.]+@', '_', $transliterated);
    return $transliterated;
  }

  /**
   * Show overridden headers when available in the filter options.
   */
  protected function getMetadataOverrides($metadata, $configuration) {
    $options = [];
    foreach ($metadata as $index => $label) {
      $options[$index] = !empty($configuration[$index]['override_header']) ? $configuration[$index]['override_header'] : $label;
    }
    return $options;
  }

}
