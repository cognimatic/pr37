<?php

namespace Drupal\viewer\Plugin\viewer\viewer;

use Drupal\Core\Form\FormStateInterface;

/**
 * Viewer plugin.
 *
 * @Viewer(
 *   id = "datatables",
 *   name = @Translation("Datatables"),
 *   provider = "viewer",
 *   processor = "processor_csv",
 *   filters = true,
 *   viewer_types = {
 *     "csv"
 *   }
 * )
 */
class Datatables extends Table {

  /**
   * {@inheritdoc}
   */
  public function getRenderable() {
    return [
      '#theme' => 'viewer_datatables',
      '#uuid' => $this->getId(),
      '#viewer' => $this->getViewer(),
      '#settings' => $this->getSettings(),
      '#last_import' => $this->getViewerSource()->getLastImportRaw(),
      '#attached' => [
        'library' => ['viewer/viewer.datatables'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state, $params = []) {
    $settings = $params['settings'];
    $form = parent::settingsForm($form, $form_state, $params);
    unset($form['general']['show_all'], $form['general']['items_per_load'], $form['general']['load_more_label']);
    $form['general']['paging'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable paging'),
      '#default_value' => !empty($settings['paging']),
    ];
    $form['general']['searching'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable search'),
      '#default_value' => !empty($settings['searching']),
    ];
    $form['general']['ordering'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable sorting'),
      '#default_value' => !empty($settings['ordering']),
    ];
    $form['general']['page_length'] = [
      '#type' => 'select',
      '#title' => $this->t('Number of items per page'),
      '#options' => [
        10 => 10,
        25 => 25,
        50 => 50,
        100 => 100,
      ],
      '#default_value' => !empty($settings['page_length']) ? (int) $settings['page_length'] : 25,
      '#states' => [
        'visible' => [':input[name="paging"]' => ['checked' => TRUE]],
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsValues(array &$form, FormStateInterface $form_state) {
    $settings = parent::settingsValues($form, $form_state);
    unset($settings['show_all'], $settings['items_per_load'], $settings['load_more_label']);
    $settings += [
      'last_import_position' => $form_state->getValue('last_import_position'),
      'last_import_format' => $form_state->getValue('last_import_format'),
      'last_import' => $form_state->getValue('last_import'),
      'paging' => $form_state->getValue('paging'),
      'page_length' => $form_state->getValue('page_length'),
      'searching' => $form_state->getValue('searching'),
      'ordering' => $form_state->getValue('ordering'),
    ];
    return $settings;
  }

}
