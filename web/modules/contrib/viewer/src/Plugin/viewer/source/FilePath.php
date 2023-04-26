<?php

namespace Drupal\viewer\Plugin\viewer\source;

use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\viewer\Plugin\ViewerSourceBase;

/**
 * File Path source plugin.
 *
 * @ViewerSource(
 *   id = "path",
 *   name = @Translation("Path"),
 *   cron = TRUE
 * )
 */
class FilePath extends ViewerSourceBase {

  /**
   * {@inheritdoc}
   */
  public function sourceForm(array $form, FormStateInterface $form_state, $viewer_type, $viewer_source) {
    $form = parent::sourceForm($form, $form_state, $viewer_type, $viewer_source);
    $form['path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Absolute path to a @label file', ['@label' => $viewer_type->getName()]),
      '#description' => $this->t('Allowed extensions: %list. You may also use tokens for dates, example: [date:custom:?], [date:short] etc.', [
        '%list' => implode(', ', array_values($viewer_type->getExtensions())),
      ]),
      '#required' => TRUE,
      '#weight' => -10,
    ];
    $form['import_frequency'] = [
      '#type' => 'select',
      '#title' => $this->t('Frequency'),
      '#options' => viewer_import_frequencies(),
      '#description' => $this->t('How often perform automatic imports (this process adds items to the Drupal Queue API). This configuration option also depends on how Cron is configured in the system.'),
      '#weight' => -9,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitSourceForm(array &$form, FormStateInterface $form_state, $viewer_type) {
    $file = $form_state->getValue('path', '');
    $settings = $viewer_type->submitPropertiesForm($form_state);
    $settings['path'] = $form_state->getValue('path');
    if (!empty($file)) {
      $this->setBatchFile($file[0])
        ->setAdditionalFields([
          'name'   => $this->getKeyVal('name'),
          'source' => $this->getKeyVal('source'),
          'type' => $this->getKeyVal('type'),
        ])
        ->setImportFrequency($form_state->getValue('import_frequency'))
        ->setBatchFileSource('path')
        ->setBatchSettings($settings);
      return $this->buildManualBatchItems();
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state, $viewer_source = NULL, $settings = []) {
    $form = parent::settingsForm($form, $form_state, $viewer_source, $settings);
    $form['path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Absolute path to a @label file', ['@label' => $viewer_source->getTypePlugin()->getName()]),
      '#default_value' => !empty($settings['path']) ? $settings['path'] : '',
      '#description' => $this->t('Allowed extensions: %list. You may also use tokens for dates, example: [date:custom:?], [date:short] etc.', [
        '%list' => implode(', ', array_values($viewer_source->getTypePlugin()->getExtensions())),
      ]),
      '#required' => TRUE,
      '#weight' => -20,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitSettingsForm(array &$form, FormStateInterface $form_state, $viewer_source = NULL, $settings = []) {
    $settings = parent::submitSettingsForm($form, $form_state, $viewer_source, $settings);
    $settings['path'] = $form_state->getValue('path');
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function importForm(array $form, FormStateInterface $form_state, $viewer_type, $viewer_source) {
    $form = parent::importForm($form, $form_state, $viewer_type, $viewer_source);
    $form['source_type'] = [
      '#title' => $this->t('Source type'),
      '#type' => 'radios',
      '#options' => [
        'path' => $this->t('Path (manual)'),
        'upload' => $this->t('Upload a file (manual)'),
      ],
      '#default_value' => 'path',
      '#description' => $this->t("This won't update the import source file. The purpose of this form is to manually perform import."),
      '#weight' => -20,
    ];
    $settings = $viewer_source->getSettings();
    if (!empty($settings['path'])) {
      $form['path']['#default_value'] = $settings['path'];
    }
    $form['path']['#description'] = $this->t('Running a new import will remove preiously imported data and replace with the latest. Allowed extensions: %list.
      You may also use tokens for dates, example: [date:custom:?], [date:short] etc.', [
        '%list' => implode(', ', array_values($viewer_type->getExtensions())),
      ]);
    $form['path']['#states'] = [
      'visible' => [':input[name="source_type"]' => ['value' => 'path']],
    ];
    $form['file_wrapper'] = [
      '#type' => 'fieldset',
      '#states' => [
        'visible' => [':input[name="source_type"]' => ['value' => 'upload']],
      ],
    ];
    $form['file_wrapper']['file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Upload @label file', ['@label' => $viewer_source->getTypePlugin()->getName()]),
      '#upload_location' => $this->getUploadPath(),
      '#upload_validators' => [
        'file_validate_extensions' => $viewer_type->getExtensionsAsValidator(),
      ],
      '#description' => $this->t('Manually uploading a file will not break any automated settings you have configured. Allowed extensions: %list', [
        '%list' => implode(', ', array_values($viewer_type->getExtensions())),
      ]),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitImportForm(array &$form, FormStateInterface $form_state, $viewer_source = NULL) {
    $source_type = $form_state->getValue('source_type', 'path');
    $this->setImportFrequency($viewer_source->getFrequency())
      ->setBatchSettings($viewer_source->getSettings())
      ->setBatchViewerSourceEntity($viewer_source);
    if ($source_type == 'path') {
      $path = $form_state->getValue('path', '');
      if (!empty($path)) {
        $this->setBatchFile($path)
          ->setBatchFileSource($source_type);
        return $this->buildManualBatchItems();
      }
    }
    else {
      $file = $form_state->getValue('file', 0);
      if (!empty($file[0])) {
        $this->setBatchFile($file[0]);
        return $this->buildManualBatchItems();
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getFile($file, $settings, $type_plugin, $source_type = NULL) {
    if ($source_type == 'path') {
      $settings['path'] = \Drupal::token()->replace($settings['path'], ['date' => ''], ['clear' => TRUE]);
      return $this->getFileFromPath($settings['path'], array_keys($type_plugin->getExtensions()));
    }
    return File::load($file);
  }

}
