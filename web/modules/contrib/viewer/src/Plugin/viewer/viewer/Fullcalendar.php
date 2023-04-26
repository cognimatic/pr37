<?php

namespace Drupal\viewer\Plugin\viewer\viewer;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\viewer\Entity\ViewerSourceInterface;
use Drupal\viewer\Plugin\ViewerBase;

/**
 * Viewer plugin.
 *
 * @Viewer(
 *   id = "fullcalendar",
 *   name = @Translation("Fullcalendar: Basic"),
 *   provider = "viewer",
 *   processor = "processor_csv",
 *   filters = true,
 *   viewer_types = {
 *     "csv"
 *   }
 * )
 */
class Fullcalendar extends ViewerBase {

  /**
   * {@inheritdoc}
   */
  public function getResponse() {
    $fc_headers = ['title', 'start', 'end'];
    $fc_events = [];
    $data = parent::getResponse();
    $rows = $data['rows'];
    foreach ($rows as $i => $row) {
      for ($j = 0; $j < 3; $j++) {
        $fc_events[$i][$fc_headers[$j]] = $row[$j];
      }
    }
    return [
      'events' => $fc_events,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getRenderable() {
    $settings = $this->getSettings();
    return [
      '#theme' => 'viewer_fullcalendar',
      '#uuid' => $this->getId(),
      '#viewer' => $this->getViewer(),
      '#settings' => $settings,
      '#last_import' => $this->getViewerSource()->getLastImportRaw(),
      '#attached' => [
        'library' => ['viewer/viewer.fullcalendar'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state, $params = []) {
    $settings = $params['settings'];
    $form = parent::settingsForm($form, $form_state, $params);
    $form['options'] = [
      '#type' => 'details',
      '#title' => $this->t('Options'),
      '#group' => 'plugin',
      '#weight' => -10,
    ];
    $form['options']['initial_date'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Initial Date'),
      '#default_value' => !empty($settings['initial_date']) ? $settings['initial_date'] : '',
      '#description' => $this->t('Initial Date. Example: 2020-09-12'),
    ];
    $form['options']['height'] = [
      '#type' => 'number',
      '#title' => $this->t('Calendar Height'),
      '#default_value' => !empty($settings['height']) ? $settings['height'] : 650,
      '#description' => $this->t('Calendar height'),
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
      'initial_date' => $form_state->getValue('initial_date'),
      'height' => $form_state->getValue('height'),
      'last_import_position' => $form_state->getValue('last_import_position'),
      'last_import_format' => $form_state->getValue('last_import_format'),
      'last_import' => $form_state->getValue('last_import'),
    ];
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function configurationForm(array $form, FormStateInterface $form_state, $params = []) {
    $configuration = $params['configuration'];
    $viewer_source_id = $params['viewer_source'];
    $form['columns'] = [
      '#type' => 'table',
    ];

    $form['columns'][0]['title'] = [
      '#type' => 'select',
      '#title' => $this->t('Title'),
      '#title_display' => 'invisible',
      '#options' => $this->getHeaders($viewer_source_id),
      '#empty_option' => $this->t('- Select Title -'),
      "#default_value" => $configuration['columns'][0]['title'] ?? '',
      '#required' => TRUE,
    ];

    $form['columns'][0]['start'] = [
      '#type' => 'select',
      '#title' => $this->t('Start Date'),
      '#title_display' => 'invisible',
      '#options' => $this->getHeaders($viewer_source_id),
      '#empty_option' => $this->t('- Select Start Date -'),
      "#default_value" => $configuration['columns'][0]['start'] ?? '',
      '#required' => TRUE,
    ];

    $form['columns'][0]['end'] = [
      '#type' => 'select',
      '#title' => $this->t('End Date'),
      '#title_display' => 'invisible',
      '#options' => $this->getHeaders($viewer_source_id),
      '#empty_option' => $this->t('- Select End Date -'),
      "#default_value" => $configuration['columns'][0]['end'] ?? '',
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function configurationValues(array &$form, FormStateInterface $form_state) {
    return ['columns' => $form_state->getValue('columns')];
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
