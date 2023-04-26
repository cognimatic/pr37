<?php

namespace Drupal\viewer\Plugin\viewer\viewer;

use Drupal\viewer\Plugin\ViewerBase;
use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;

/**
 * Viewer plugin.
 *
 * @Viewer(
 *   id = "pdfjs",
 *   name = @Translation("PDF: PDF.js"),
 *   viewer_types = {
 *     "pdf"
 *   }
 * )
 */
class PdfJs extends ViewerBase {

  /**
   * {@inheritdoc}
   */
  public function getResponse() {
    $path = '';
    if ($file = $this->getViewerSource()->getFile()) {
      $path = \Drupal::service('file_url_generator')->generateString($file->getFileUri());
    }
    return ['file_path' => $path];
  }

  /**
   * {@inheritdoc}
   */
  public function getRenderable() {
    return [
      '#theme' => 'viewer_pdfjs',
      '#uuid' => $this->getId(),
      '#viewer' => $this->getViewer(),
      '#settings' => $this->getSettings(),
      '#last_import' => $this->getViewerSource()->getLastImportRaw(),
      '#attached' => [
        'library' => ['viewer/viewer.pdfjs'],
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
    $form['general']['chart_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Chart Title'),
      '#default_value' => !empty($settings['chart_title']) ? $settings['chart_title'] : FALSE,
    ];
    $form['general']['chart_subtitle'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Chart Subtitle'),
      '#default_value' => !empty($settings['chart_subtitle']) ? $settings['chart_subtitle'] : FALSE,
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
    ];
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function configurationForm(array $form, FormStateInterface $form_state, $params = []) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function configurationValues(array &$form, FormStateInterface $form_state) {
    return [];
  }

}
