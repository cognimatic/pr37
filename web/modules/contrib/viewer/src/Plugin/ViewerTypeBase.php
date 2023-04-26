<?php

namespace Drupal\viewer\Plugin;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\Entity\File;

/**
 * ViewerTypeBase plugin base class.
 *
 * @package viewer
 */
class ViewerTypeBase extends PluginBase implements ViewerTypeInterface, ContainerFactoryPluginInterface {

  use DependencySerializationTrait;
  use StringTranslationTrait;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FileSystemInterface $file_system) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition,
      $container->get('file_system')
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
  public function getExtensions() {
    return !empty($this->pluginDefinition['extensions']) ? $this->pluginDefinition['extensions'] : [];
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultViewerPluginId() {
    return $this->pluginDefinition['default_viewer'];
  }

  /**
   * {@inheritdoc}
   */
  public function getExtensionsAsValidator() {
    return [implode(' ', array_values($this->getExtensions()))];
  }

  /**
   * {@inheritdoc}
   */
  public function propertiesForm($settings = []) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function submitPropertiesForm(FormStateInterface $form_state) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getMetadata(File $file, $settings = []) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getContentAsArray(File $file, $settings = []) {
    $array = [];
    if (($handle = fopen(\Drupal::service('file_system')->realpath($file->getFileUri()), "r")) !== FALSE) {
      $row = 0;
      while (($data = fgetcsv($handle, 1024, $settings['delimiter'], $settings['enclosure'], $settings['escape'])) !== FALSE) {
        for ($c = 0; $c < count($data); $c++) {
          $array[$row][$c] = $data[$c];
        }
        $row++;
      }
      fclose($handle);
      return $array;
    }
  }

}
