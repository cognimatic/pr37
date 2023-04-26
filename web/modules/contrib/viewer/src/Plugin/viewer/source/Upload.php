<?php

namespace Drupal\viewer\Plugin\viewer\source;

use Drupal\viewer\Plugin\ViewerSourceBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Upload source plugin.
 *
 * @ViewerSource(
 *   id = "upload",
 *   name = @Translation("Upload"),
 * )
 */
class Upload extends ViewerSourceBase {

  /**
   * {@inheritdoc}
   */
  public function sourceForm(array $form, FormStateInterface $form_state, $viewer_type, $viewer_source) {
    $form = parent::sourceForm($form, $form_state, $viewer_type, $viewer_source);
    $form['file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('@label file', ['@label' => $viewer_type->getName()]),
      '#upload_location' => $this->getUploadPath(),
      '#upload_validators' => [
        'file_validate_extensions' => $viewer_type->getExtensionsAsValidator(),
      ],
      '#weight' => -10,
      '#description' => $this->t('Please use actual file that will be used for display. Allowed extensions: %list', [
        '%list' => implode(', ', array_values($viewer_type->getExtensions())),
      ]),
      '#required' => TRUE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitSourceForm(array &$form, FormStateInterface $form_state, $viewer_type) {
    $file = $form_state->getValue('file', '');
    $settings = $viewer_type->submitPropertiesForm($form_state);
    if (!empty($file)) {
      $this->setBatchFile($file[0])
        ->setAdditionalFields([
          'name'   => $this->getKeyVal('name'),
          'source' => $this->getKeyVal('source'),
          'type' => $this->getKeyVal('type'),
        ])
        ->setImportFrequency()
        ->setBatchSettings($settings);
      return $this->buildManualBatchItems();
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state, $viewer_source = NULL, $settings = []) {
    $form = $this->sourceForm($form, $form_state, $viewer_source->getTypePlugin(), $viewer_source);
    unset($form['file']);
    return parent::settingsForm($form, $form_state, $viewer_source, $settings);
  }

  /**
   * {@inheritdoc}
   */
  public function submitImportForm(array &$form, FormStateInterface $form_state, $viewer_source = NULL) {
    if ($file = $form_state->getValue('file', 0)) {
      $this->setBatchFile($file[0])
        ->setImportFrequency()
        ->setBatchSettings($viewer_source->getSettings())
        ->setBatchViewerSourceEntity($viewer_source);
      return $this->buildManualBatchItems();
    }
    return FALSE;
  }

}
