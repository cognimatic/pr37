<?php

namespace Drupal\viewer\Plugin\viewer\source;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\Entity\File;
use Drupal\viewer\Plugin\ViewerSourceBase;

/**
 * FTP source plugin.
 *
 * @ViewerSource(
 *   id = "ftp",
 *   name = @Translation("FTP"),
 *   provider = "viewer",
 *   cron = TRUE
 * )
 */
class Ftp extends ViewerSourceBase {

  /**
   * {@inheritdoc}
   */
  public function requirementsAreMet() {
    return class_exists('\League\Flysystem\Ftp\FtpAdapter');
  }

  /**
   * {@inheritdoc}
   */
  public function sourceForm(array $form, FormStateInterface $form_state, $viewer_type, $viewer_source) {
    $form = parent::sourceForm($form, $form_state, $viewer_type, $viewer_source);
    $form['credentials'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('FTP credentials'),
      '#weight' => -10,
    ];
    $form['credentials']['host'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Host'),
      '#description' => $this->t('Host without the ftp://'),
      '#required' => TRUE,
    ];
    $form['credentials']['username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#size' => 20,
      '#required' => TRUE,
    ];
    $form['credentials']['password'] = [
      '#type' => 'password',
      '#title' => $this->t('Password'),
      '#required' => TRUE,
      '#size' => 20,
    ];
    $form['credentials']['port'] = [
      '#type' => 'number',
      '#title' => $this->t('Port'),
      '#default_value' => 21,
      '#size' => 10,
      '#required' => TRUE,
    ];
    $form['credentials']['ssl'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('SSL'),
    ];
    $form['credentials']['timeout'] = [
      '#type' => 'number',
      '#title' => $this->t('Timeout'),
      '#default_value' => 90,
      '#required' => TRUE,
      '#size' => 5,
    ];
    $form['path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Path to a @label file', ['@label' => $viewer_type->getName()]),
      '#description' => $this->t('Allowed extensions: %list. You may also use tokens for dates, example: [date:custom:?], [date:short] etc.', [
        '%list' => implode(', ', array_values($viewer_type->getExtensions())),
      ]),
      '#required' => TRUE,
      '#weight' => -9,
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
    $settings['host'] = $form_state->getValue('host');
    $settings['username'] = $form_state->getValue('username');
    $settings['password'] = $this->encryptString($form_state->getValue('password'));
    $settings['port'] = $form_state->getValue('port');
    $settings['ssl'] = $form_state->getValue('ssl');
    $settings['timeout'] = $form_state->getValue('timeout');
    if (!empty($file)) {
      $this->setBatchFile($file[0])
        ->setAdditionalFields([
          'name'   => $this->getKeyVal('name'),
          'source' => $this->getKeyVal('source'),
          'type' => $this->getKeyVal('type'),
        ])
        ->setImportFrequency($form_state->getValue('import_frequency'))
        ->setBatchFileSource('ftp')
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
    unset($form['import_frequency']);
    $form['credentials']['host']['#default_value'] = !empty($settings['host']) ? $settings['host'] : '';
    $form['credentials']['username']['#default_value'] = !empty($settings['username']) ? $settings['username'] : '';
    $form['credentials']['private_key']['#default_value'] = !empty($settings['private_key']) ? $settings['private_key'] : '';
    $form['credentials']['port']['#default_value'] = !empty($settings['port']) ? $settings['port'] : 21;
    $form['credentials']['ssl']['#default_value'] = !empty($settings['ssl']) ? $settings['ssl'] : '';
    $form['credentials']['timeout']['#default_value'] = !empty($settings['timeout']) ? $settings['timeout'] : 90;
    $form['path']['#default_value'] = !empty($settings['path']) ? $settings['path'] : '';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitSettingsForm(array &$form, FormStateInterface $form_state, $viewer_source = NULL, $settings = []) {
    $settings = parent::submitSettingsForm($form, $form_state, $viewer_source, $settings);
    $settings['path'] = $form_state->getValue('path');
    $settings['host'] = $form_state->getValue('host');
    $settings['username'] = $form_state->getValue('username');
    if ($password = $form_state->getValue('password')) {
      $settings['password'] = $this->encryptString($password);
    }
    $settings['port'] = (int) $form_state->getValue('port');
    $settings['ssl'] = $form_state->getValue('ssl');
    $settings['timeout'] = $form_state->getValue('timeout');
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
        'ftp' => $this->t('FTP (manual)'),
        'upload' => $this->t('Upload a file (manual)'),
      ],
      '#default_value' => 'ftp',
      '#description' => $this->t("This won't update the import source file. The purpose of this form is to manually perform import."),
      '#weight' => -20,
    ];
    $form = $this->sourceForm($form, $form_state);
    $settings = $viewer_source->getSettings();
    if (!empty($settings['path'])) {
      $form['path']['#default_value'] = $settings['path'];
    }
    $form['path']['#description'] = $this->t('Running a new import will remove preiously imported data and replace with the latest.');
    $form['path']['#states'] = [
      'visible' => [':input[name="source_type"]' => ['value' => 'ftp']],
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
      '#description' => $this->t('Manually uploading a file will not break any automated settings you have configured.'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitImportForm(array &$form, FormStateInterface $form_state, $viewer_source = NULL) {
    $source_type = $form_state->getValue('source_type', 'ftp');
    $this->setImportFrequency($viewer_source->getFrequency())
      ->setBatchSettings($viewer_source->getSettings())
      ->setBatchViewerSourceEntity($viewer_source);
    if ($source_type == 'ftp') {
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
    if ($source_type == 'ftp') {
      $sftp = \Drupal::service('viewer.ftp_sftp');
      $settings['password'] = !empty($settings['password']) ? $this->decryptString($settings['password']) : NULL;
      $sftp->ftp($settings);
      $settings['path'] = \Drupal::token()->replace($settings['path'], ['date' => ''], ['clear' => TRUE]);
      if ($data = $sftp->downloadFile($settings['path'])) {
        $fileRepository = \Drupal::service('file.repository');
        $file = $fileRepository->writeData($data, $this->getUploadPath() . '/' . basename($settings['path']), FileSystemInterface::EXISTS_RENAME);
        $file->setTemporary();
        $file->save();
        return $file;
      }
    }
    else {
      return File::load($file);
    }
  }

}
