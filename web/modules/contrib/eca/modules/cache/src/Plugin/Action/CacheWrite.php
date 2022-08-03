<?php

namespace Drupal\eca_cache\Plugin\Action;

use Drupal\Core\Form\FormStateInterface;
use Drupal\eca\Plugin\Action\ActionBase;
use Drupal\eca\Service\YamlParser;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Action to write into cache.
 *
 * @Action(
 *   id = "eca_cache_write",
 *   label = @Translation("Cache: write"),
 *   description = @Translation("Write a value item into cache.")
 * )
 */
class CacheWrite extends CacheActionBase {

  /**
   * The YAML parser.
   *
   * @var \Drupal\eca\Service\YamlParser
   */
  protected YamlParser $yamlParser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): ActionBase {
    /** @var \Drupal\eca_config\Plugin\Action\ConfigWrite $instance */
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->setYamlParser($container->get('eca.service.yaml_parser'));
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function execute(): void {
    if (!($cache = $this->getCacheBackend()) || !($key = $this->getCacheKey())) {
      return;
    }

    $value = $this->configuration['value'];
    if ($this->configuration['use_yaml']) {
      try {
        $value = $this->yamlParser->parse($value);
      }
      catch (ParseException $e) {
        \Drupal::logger('eca')->error('Tried parsing a cache value item in action "eca_cache_write" as YAML format, but parsing failed.');
        return;
      }
    }
    else {
      $value = $this->tokenServices->getOrReplace($value);
    }

    $expire = (int) ($this->configuration['expire'] ?? -1);
    $tags = $this->getCacheTags();

    $cache->set($key, $value, $expire, $tags);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'value' => '',
      'expire' => '-1',
      'tags' => '',
      'use_yaml' => FALSE,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['value'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Cache item value'),
      '#description' => $this->t('The value to cache. Supports tokens.'),
      '#default_value' => $this->configuration['value'],
      '#weight' => -40,
    ];
    $form['use_yaml'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Interpret above config value as YAML format'),
      '#description' => $this->t('Nested data can be set using YAML format, for example <em>mykey: myvalue</em>. When using this format, this option needs to be enabled. When using tokens and YAML altogether, make sure that tokens are wrapped as a string. Example: <em>title: "[node:title]"</em>'),
      '#default_value' => $this->configuration['use_yaml'],
      '#weight' => -30,
    ];
    $form['expire'] = [
      '#type' => 'number',
      '#title' => $this->t('Lifetime until expiry'),
      '#description' => $this->t('The lifetime in seconds until the cached value is considered invalid. Set to -1 for unlimited lifetime.'),
      '#default_value' => $this->configuration['expire'],
      '#required' => TRUE,
      '#weight' => -20,
    ];
    $form['tags'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Cache tags'),
      '#description' => $this->t('Optionally add cache tags for fine-granular cache invalidation. Separate multiple tags with comma. More information about cache tags can be found in the <a href=":url" target="_blank" rel="nofollow noreferrer">documentation</a>.', [
        ':url' => 'https://www.drupal.org/docs/drupal-apis/cache-api/cache-tags',
      ]),
      '#default_value' => $this->configuration['tags'],
      '#weight' => -10,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration['value'] = $form_state->getValue('value');
    $this->configuration['use_yaml'] = $form_state->getValue('use_yaml');
    $this->configuration['expire'] = $form_state->getValue('expire');
    $this->configuration['tags'] = $form_state->getValue('tags');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * Set the YAML parser.
   *
   * @param \Drupal\eca\Service\YamlParser $yaml_parser
   *   The YAML parser.
   */
  public function setYamlParser(YamlParser $yaml_parser): void {
    $this->yamlParser = $yaml_parser;
  }

}
