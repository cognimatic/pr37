<?php

namespace Drupal\viewer\Plugin;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Mime\MimeTypes;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Site\Settings;
use Drupal\file\Entity\File;
use Drupal\viewer\ViewerInterface;
use Drupal\viewer\Traits\PluginBatchTrait;
use Drupal\viewer\Traits\TempKeyValTrait;

/**
 * ViewerSourceBase plugin base class.
 *
 * @package viewer
 */
class ViewerSourceBase extends PluginBase implements ViewerSourceInterface, ContainerFactoryPluginInterface {

  use StringTranslationTrait;
  use DependencySerializationTrait;
  use PluginBatchTrait;
  use TempKeyValTrait;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Private temporary store factory.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $tempStoreFactory;

  /**
   * Stores private data for viewer source multistep.
   *
   * @var \Drupal\user\PrivateTempStore
   */
  protected $store;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FileSystemInterface $file_system, PrivateTempStoreFactory $temp_store_factory, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->fileSystem = $file_system;
    $this->tempStoreFactory = $temp_store_factory;
    $this->store = $this->tempStoreFactory->get('viewer_source_multistep_values');
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition,
      $container->get('file_system'),
      $container->get('tempstore.private'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->pluginDefinition['name'];
  }

  /**
   * {@inheritdoc}
   */
  public function isCron() {
    return !empty($this->pluginDefinition['cron']);
  }

  /**
   * Method to check requirements for the source plugin.
   */
  public function requirementsAreMet() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function sourceForm(array $form, FormStateInterface $form_state, $viewer_type, $viewer_source) {
    $properties_form = $viewer_type->propertiesForm([]);
    if ($properties_form !== $form) {
      $form = array_merge($form, $properties_form);
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitSourceForm(array &$form, FormStateInterface $form_state, $viewer_type) {

  }

  /**
   * {@inheritdoc}
   */
  public function importForm(array $form, FormStateInterface $form_state, $viewer_type, $viewer_source) {
    $form = $this->sourceForm($form, $form_state, $viewer_source->getTypePlugin(), $viewer_source);
    if ($properties_form = $viewer_type->propertiesForm([])) {
      foreach (array_keys($properties_form) as $element) {
        unset($form[$element]);
      }
    }
    unset($form['import_frequency'], $form['credentials']);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitImportForm(array &$form, FormStateInterface $form_state, $xlsx = NULL) {
    // Nothing in the base form.
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state, $viewer_source = NULL, $settings = []) {
    $properties_form = $viewer_source->getTypePlugin()->propertiesForm($settings);
    if ($properties_form !== $form) {
      $form = array_merge($form, $properties_form);
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitSettingsForm(array &$form, FormStateInterface $form_state, $viewer_source = NULL, $settings = []) {
    $new_settings = $settings;
    $new_settings = $viewer_source->getTypePlugin()->submitPropertiesForm($form_state);
    return $new_settings;
  }

  /**
   * Get file from path.
   */
  public function getFileFromPath($filepath, $mime_types) {
    // Only allowing file types specified in the ViewerType plugin.
    $mime_type = (new MimeTypes())->guessMimeType($filepath);
    if (in_array($mime_type, $mime_types)) {
      if ($handle = fopen($filepath, 'r')) {
        $file = File::create([]);
        $file_path = $this->fileSystem->copy($filepath, $this->getUploadPath() . '/' . basename($filepath), FileSystemInterface::EXISTS_RENAME);
        $file->setFilename(basename($filepath));
        $file->setMimeType($mime_type);
        $file->setFileUri($file_path);
        $file->setTemporary();
        $file->save();
        fclose($handle);
        return $file;
      }
    }
  }

  /**
   * Get file from URL.
   */
  public function getFileFromUrl($fileurl, $mime_types) {
    $context = stream_context_create(['http' => ['method' => 'HEAD']]);
    $headers = array_change_key_case(get_headers($fileurl, TRUE, $context));
    // Only allowing file types specified in the ViewerType plugin.
    if (!empty($headers['content-type']) && in_array($headers['content-type'], $mime_types)) {
      if ($lines = file($fileurl)) {
        $contents = '';
        foreach ($lines as $line) {
          $contents .= $line;
        }
        $fileRepository = \Drupal::service('file.repository');
        $file = $fileRepository->writeData($contents, $this->getUploadPath() . '/' . basename($fileurl), FileSystemInterface::EXISTS_RENAME);
        $file->setTemporary();
        $file->save();
        return $file;
      }
    }
  }

  /**
   * Tell how to get a file object for further processing.
   */
  public function getFile($file, $settings, $type_plugin, $source_type = NULL) {
    return $this->entityTypeManager->getStorage('file')->load($file);
  }

  /**
   * Build batch prcoess items.
   */
  public function buildManualBatchItems() {
    $items = [];
    $items[] = ['\Drupal\viewer\Services\Batch::upload', [$this]];
    return $items;
  }

  /**
   * Build metadata for the file.
   */
  public function getMetadata(File $file, $settings = []) {
    return [];
  }

  /**
   * Encrypt string.
   */
  public function encryptString($string) {
    return openssl_encrypt($string, "AES-128-ECB", $this->encryptKey());
  }

  /**
   * Decrypt string.
   */
  public function decryptString($encrypted_string) {
    return openssl_decrypt($encrypted_string, "AES-128-ECB", $this->encryptKey());
  }

  /**
   * Encryption/Decryption key.
   */
  public function encryptKey() {
    return Settings::get('hash_salt', $this->getPluginId());
  }

  /**
   * Get upload path based on system settings.
   */
  protected function getUploadPath() {
    $path = ViewerInterface::PUBLIC_URI;
    if (\Drupal::service('stream_wrapper_manager')->isValidScheme('private')) {
      $path = ViewerInterface::PRIVATE_URI;
    }
    $this->fileSystem->prepareDirectory($path, FileSystemInterface::CREATE_DIRECTORY);
    return $path;
  }

}
