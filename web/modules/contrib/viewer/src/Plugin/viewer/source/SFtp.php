<?php

namespace Drupal\viewer\Plugin\viewer\source;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\Entity\File;
use Drupal\viewer\Plugin\ViewerSourceBase;

/**
 * SFTP source plugin.
 *
 * @ViewerSource(
 *   id = "sftp",
 *   name = @Translation("SFTP"),
 *   provider = "viewer",
 *   cron = TRUE
 * )
 */
class SFtp extends ViewerSourceBase {

  /**
   * {@inheritdoc}
   */
  public function requirementsAreMet() {
    return class_exists('\League\Flysystem\PhpseclibV3\SftpAdapter');
  }

  /**
   * {@inheritdoc}
   */
  public function sourceForm(array $form, FormStateInterface $form_state, $viewer_type, $viewer_source) {
    $form = parent::sourceForm($form, $form_state, $viewer_type, $viewer_source);
    $form['credentials'] = [
      '#type' => 'details',
      '#title' => $this->t('SFTP credentials'),
      '#open' => TRUE,
      '#weight' => -10,
    ];
    $form['credentials']['host'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Host'),
      '#description' => $this->t('Host without the sftp://'),
      '#size' => 50,
      '#required' => TRUE,
    ];
    $form['credentials']['username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#size' => 20,
      '#required' => TRUE,
    ];
    $form['credentials']['use_private_key'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use private key'),
      '#required' => FALSE,
    ];
    $form['credentials']['private_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Path to a private key'),
      '#states' => [
        'visible' => [':input[name="use_private_key"]' => ['checked' => TRUE]],
      ],
    ];
    $form['credentials']['passphrase'] = [
      '#type' => 'password',
      '#title' => $this->t('Passphrase (optional)'),
      '#states' => [
        'visible' => [':input[name="use_private_key"]' => ['checked' => TRUE]],
      ],
    ];
    $form['credentials']['password'] = [
      '#type' => 'password',
      '#title' => $this->t('Password'),
      '#size' => 20,
      '#states' => [
        'visible' => [':input[name="use_private_key"]' => ['checked' => FALSE]],
      ],
    ];
    $form['credentials']['port'] = [
      '#type' => 'number',
      '#title' => $this->t('Port'),
      '#default_value' => '2222',
      '#size' => 10,
      '#required' => TRUE,
    ];
    $form['credentials']['user_agent'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('User Agent'),
    ];
    $form['credentials']['timeout'] = [
      '#type' => 'number',
      '#title' => $this->t('Timeout'),
      '#default_value' => 30,
      '#size' => 5,
    ];
    $form['credentials']['maxtries'] = [
      '#type' => 'number',
      '#title' => $this->t('Max tries'),
      '#default_value' => 10,
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
    $settings['use_private_key'] = $form_state->getValue('use_private_key');
    $settings['private_key'] = $form_state->getValue('private_key');
    $settings['passphrase'] = $this->encryptString($form_state->getValue('passphrase'));
    $settings['password'] = $this->encryptString($form_state->getValue('password'));
    $settings['port'] = $form_state->getValue('port');
    $settings['user_agent'] = $form_state->getValue('user_agent');
    $settings['timeout'] = $form_state->getValue('timeout');
    $settings['maxtries'] = $form_state->getValue('maxtries');
    if (!empty($file)) {
      $this->setBatchFile($file[0])
        ->setAdditionalFields([
          'name'   => $this->getKeyVal('name'),
          'source' => $this->getKeyVal('source'),
          'type' => $this->getKeyVal('type'),
        ])
        ->setImportFrequency($form_state->getValue('import_frequency'))
        ->setBatchFileSource('sftp')
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
    $form['credentials']['use_private_key']['#default_value'] = !empty($settings['use_private_key']);
    $form['credentials']['private_key']['#default_value'] = !empty($settings['private_key']) ? $settings['private_key'] : '';
    $form['credentials']['port']['#default_value'] = !empty($settings['port']) ? $settings['port'] : '2222';
    $form['credentials']['user_agent']['#default_value'] = !empty($settings['user_agent']) ? $settings['user_agent'] : '';
    $form['credentials']['timeout']['#default_value'] = !empty($settings['timeout']) ? $settings['timeout'] : 30;
    $form['credentials']['maxtries']['#default_value'] = !empty($settings['maxtries']) ? $settings['maxtries'] : 10;
    $form['path']['#default_value'] = !empty($settings['path']) ? $settings['path'] : '';
    return parent::settingsForm($form, $form_state, $viewer_source, $settings);
  }

  /**
   * {@inheritdoc}
   */
  public function submitSettingsForm(array &$form, FormStateInterface $form_state, $viewer_source = NULL, $settings = []) {
    $settings = parent::submitSettingsForm($form, $form_state, $viewer_source, $settings);
    $settings['path'] = $form_state->getValue('path');
    $settings['host'] = $form_state->getValue('host');
    $settings['username'] = $form_state->getValue('username');
    $settings['use_private_key'] = $form_state->getValue('use_private_key');
    $settings['private_key'] = $form_state->getValue('private_key');
    if ($passphrase = $form_state->getValue('passphrase')) {
      $settings['passphrase'] = $this->encryptString($passphrase);
    }
    if ($password = $form_state->getValue('password')) {
      $settings['password'] = $this->encryptString($password);
    }
    $settings['port'] = $form_state->getValue('port');
    $settings['user_agent'] = $form_state->getValue('user_agent');
    $settings['timeout'] = $form_state->getValue('timeout');
    $settings['maxtries'] = $form_state->getValue('maxtries');
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
        'sftp' => $this->t('SFTP (manual)'),
        'upload' => $this->t('Upload a file (manual)'),
      ],
      '#default_value' => 'sftp',
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
      'visible' => [':input[name="source_type"]' => ['value' => 'sftp']],
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
    $source_type = $form_state->getValue('source_type', 'sftp');
    $this->setImportFrequency($viewer_source->getFrequency())
      ->setBatchSettings($viewer_source->getSettings())
      ->setBatchViewerSourceEntity($viewer_source);
    if ($source_type == 'sftp') {
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
    if ($source_type == 'sftp') {
      $sftp = \Drupal::service('viewer.ftp_sftp');
      $settings['password'] = !empty($settings['password']) ? $this->decryptString($settings['password']) : NULL;
      $settings['passphrase'] = !empty($settings['passphrase']) ? $this->decryptString($settings['passphrase']) : NULL;
      $sftp->sftp($settings);
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
