<?php

namespace Drupal\bugherd\Form;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SettingsForm.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * The cache tags invalidator service.
   *
   * @var Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagsInvalidator;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, CacheTagsInvalidatorInterface $cache_tags_invalidator) {
    parent::__construct($config_factory);
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('cache_tags.invalidator')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'bugherd.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('bugherd.settings');
    $form['bugherd_project_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('BugHerd Project key'),
      '#description' => $this->t('To obtain your project key login or sign up for BugHerd at @link.', [
        '@link' => Link::fromTextAndUrl('link', Url::fromUri('https://www.bugherd.com'))->toString(),
      ]),
      '#maxlength' => 128,
      '#size' => 64,
      '#default_value' => $config->get('bugherd_project_key'),
    ];

    $form['bugherd_disable_on_admin'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Disable on admin pages'),
      '#description' => $this->t('Ticking the checkbox will prevent the BugHerd button being available on admin pages'),
      '#default_value' => $config->get('bugherd_disable_on_admin'),
    ];

    $form['public_feedback'] = [
      '#type' => 'details',
      '#title' => $this->t('Public feedback features'),
      '#open' => FALSE,
    ];

    $form['public_feedback']['public_feedback_message'] = [
      '#markup' => $this->t(
        'Those features work only for the public feedback. In order to enable the feedback for every visitor, follow the BugHerd documentation: @link',
        ['@link' => Link::fromTextAndUrl('Setting up the public feedback', Url::fromUri('https://support.bugherd.com/hc/en-us/articles/207581263-Setting-Up-The-Public-Feedback-Tab'))->toString()]
      ),
    ];

    $form['public_feedback']['bugherd_widget_position'] = [
      '#type' => 'select',
      '#title' => $this->t('Position'),
      '#description' => $this->t('Choose the default location of the BugHerd widget'),
      '#default_value' => $config->get('bugherd_widget_position'),
      '#options' => [
        'bottom-right' => 'Bottom right',
        'bottom-left' => 'Bottom left',
      ],
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('bugherd.settings')
      ->set('bugherd_project_key', $form_state->getValue('bugherd_project_key'))
      ->set('bugherd_disable_on_admin', $form_state->getValue('bugherd_disable_on_admin'))
      ->set('bugherd_widget_position', $form_state->getValue('bugherd_widget_position'))
      ->save();

    // Clear all pages tagged with BugHerd cache tag.
    $this->cacheTagsInvalidator->invalidateTags([
      'bugherd',
    ]);
  }

}
