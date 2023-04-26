<?php

namespace Drupal\viewer\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\viewer\Entity\Viewer;

/**
 * ViewerBase plugin base class.
 *
 * @package viewer
 */
class ViewerBase extends PluginBase implements ViewerInterface {

  use StringTranslationTrait;

  /**
   * Viewer entity.
   *
   * @var \Drupal\viewer\Entity\ViewerInterface
   */
  protected $viewer;

  /**
   * {@inheritdoc}
   */
  public function setViewer(Viewer $viewer) {
    $this->viewer = $viewer;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getViewer() {
    return $this->viewer;
  }

  /**
   * {@inheritdoc}
   */
  public function getViewerSource() {
    return $this->viewer->getViewerSource();
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
  public function isEmptyViewerSource() {
    return !isset($this->pluginDefinition['empty_viewer_source']) ? FALSE : (bool) $this->pluginDefinition['empty_viewer_source'];
  }

  /**
   * {@inheritdoc}
   */
  public function viewerTypes() {
    return !empty($this->pluginDefinition['viewer_types']) ? $this->pluginDefinition['viewer_types'] : [];
  }

  /**
   * {@inheritdoc}
   */
  public function getProcessPlugin() {
    if (!empty($this->pluginDefinition['processor'])) {
      if ($plugin = \Drupal::service('plugin.manager.viewer_processor')->createInstance($this->pluginDefinition['processor'])) {
        return $plugin;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function filterable() {
    return !empty($this->pluginDefinition['filters']);
  }

  /**
   * {@inheritdoc}
   */
  public function requirementsAreMet() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getResponse() {
    return $this->getViewer()->getDataAsArray(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function getRenderable() {
    return [
      '#markup' => 'This is your plugin default template',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state, $params = []) {
    $settings = $params['settings'];
    $form['plugin'] = [
      '#type' => 'vertical_tabs',
    ];
    $form['header'] = [
      '#type' => 'details',
      '#title' => $this->t('Header'),
      '#group' => 'plugin',
      '#weight' => -9,
    ];
    $form['header']['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => !empty($settings['title']) ? $settings['title'] : '',
    ];
    $form['header']['subtitle'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subtitle'),
      '#default_value' => !empty($settings['subtitle']) ? $settings['subtitle'] : '',
    ];
    $form['header']['header_summary'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Summary'),
      '#default_value' => !empty($settings['header_summary']) ? $settings['header_summary'] : '',
    ];
    $form['footer'] = [
      '#type' => 'details',
      '#title' => $this->t('Footer'),
      '#group' => 'plugin',
      '#weight' => -8,
    ];
    $form['footer']['footer_summary'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Summary'),
      '#default_value' => !empty($settings['footer_summary']) ? $settings['footer_summary'] : '',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsValues(array &$form, FormStateInterface $form_state) {
    return [
      'title' => $form_state->getValue('title'),
      'subtitle' => $form_state->getValue('subtitle'),
      'header_summary' => $form_state->getValue('header_summary'),
      'footer_summary' => $form_state->getValue('footer_summary'),
    ];
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

  /**
   * Load Viewer uuid.
   */
  public function getId() {
    return ($viewer = $this->getViewer()) ? $viewer->uuid() : NULL;
  }

  /**
   * Load Viewer settings.
   */
  public function getSettings() {
    return $this->getViewer()->getSettings();
  }

  /**
   * Load Viewer filters.
   */
  public function getFilters() {
    return $this->getViewer()->getFilters();
  }

  /**
   * Load Viewer configuration (transformer).
   */
  public function getConfiguration() {
    return $this->getViewer()->getConfiguration();
  }

  /**
   * Generates a machine name from a string.
   */
  protected function getMachineName($string) {
    $transliterated = \Drupal::transliteration()->transliterate($string, LanguageInterface::LANGCODE_DEFAULT, '_');
    $transliterated = mb_strtolower($transliterated);
    $transliterated = preg_replace('@[^a-z0-9_.]+@', '_', $transliterated);
    return $transliterated;
  }

  /**
   * Get all date formats.
   */
  protected function getDateFormats() {
    $formats = [];
    $entities = \Drupal::entityTypeManager()->getStorage('date_format')->loadMultiple();
    foreach ($entities as $format) {
      $formats[$format->id()] = $format->label();
    }
    return $formats;
  }

  /**
   * Get cell plugins.
   */
  protected function getCellPlugins() {
    $plugins = [];
    $plugins['as_is'] = $this->t('As is');
    $viewer_cell = \Drupal::service('plugin.manager.viewer_cell');
    foreach ($viewer_cell->getDefinitions() as $id => $plugin) {
      $plugin = $viewer_cell->createInstance($plugin['id']);
      if (!empty($plugin->getApplicableViewers()) && in_array($this->getPluginId(), $plugin->getApplicableViewers())) {
        $plugins[$id] = $plugin->getName();
      }
    }
    return $plugins;
  }

}
