<?php

namespace Drupal\viewer\Plugin\viewer\source;

use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * File URL source plugin.
 *
 * @ViewerSource(
 *   id = "url",
 *   name = @Translation("URL"),
 *   provider = "viewer",
 *   cron = TRUE
 * )
 */
class FileURL extends FilePath {

  /**
   * {@inheritdoc}
   */
  public function sourceForm(array $form, FormStateInterface $form_state, $viewer_type, $viewer_source) {
    $form = parent::sourceForm($form, $form_state, $viewer_type, $viewer_source);
    $form['path']['#title'] = $this->t('URL to a @label file', ['@label' => $viewer_type->getName()]);
    $form['path']['#description'] = $this->t('Allowed extensions: %list. You may also use tokens for dates, example: [date:custom:?], [date:short] etc.', [
      '%list' => implode(', ', array_values($viewer_type->getExtensions())),
    ]);
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
        ->setBatchFileSource('url')
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
    $form['path']['#title'] = $this->t('URL to a @label file', [
      '@label' => $viewer_source->getTypePlugin()->getName(),
    ]);
    $form['path']['#description'] = $this->t('Allowed extensions: %list. You may also use tokens for dates, example: [date:custom:?], [date:short] etc.', [
      '%list' => implode(', ', array_values($viewer_source->getTypePlugin()->getExtensions())),
    ]);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function importForm(array $form, FormStateInterface $form_state, $viewer_type, $viewer_source) {
    $form = parent::importForm($form, $form_state, $viewer_type, $viewer_source);
    $form['source_type']['#options'] = [
      'url' => $this->t('URL (manual)'),
      'upload' => $this->t('Upload a file (manual)'),
    ];
    $form['source_type']['#default_value'] = 'url';
    $form['source_type']['#weight'] = -20;
    $form['path']['#states'] = [
      'visible' => [':input[name="source_type"]' => ['value' => 'url']],
    ];
    $form['file_wrapper']['#states'] = [
      'visible' => [':input[name="source_type"]' => ['value' => 'upload']],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitImportForm(array &$form, FormStateInterface $form_state, $viewer_source = NULL) {
    $source_type = $form_state->getValue('source_type', 'url');
    $this->setImportFrequency($viewer_source->getFrequency())
      ->setBatchSettings($viewer_source->getSettings())
      ->setBatchViewerSourceEntity($viewer_source);
    if ($source_type == 'url') {
      $file_url = $form_state->getValue('path', '');
      if (!empty($file_url)) {
        $this->setBatchFile($file_url)
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
    if ($source_type == 'url') {
      $settings['path'] = \Drupal::token()->replace($settings['path'], ['date' => ''], ['clear' => TRUE]);
      return $this->getFileFromUrl($settings['path'], array_keys($type_plugin->getExtensions()));
    }
    return File::load($file);
  }

}
