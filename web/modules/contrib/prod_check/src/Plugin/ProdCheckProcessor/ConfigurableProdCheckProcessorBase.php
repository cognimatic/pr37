<?php

namespace Drupal\prod_check\Plugin\ProdCheckProcessor;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\prod_check\Plugin\ProdCheckCategoryPluginManager;
use Drupal\prod_check\Plugin\ProdCheckPluginManager;

/**
 * Provides a base implementation for a configurable Production check processor plugin.
 */
abstract class ConfigurableProdCheckProcessorBase extends ProdCheckProcessorBase implements ConfigurableInterface, PluginFormInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ProdCheckPluginManager $manager, ProdCheckCategoryPluginManager $category_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $manager, $category_manager);

    $this->configuration += $this->defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

}
