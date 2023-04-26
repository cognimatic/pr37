<?php

namespace Drupal\content_readability\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Displays Admin form for Content Readability.
 */
class ContentReadabilityAdminSettingsForm extends ConfigFormBase {

  /**
   * Configuration Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * The EntityTypeManager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructor.
   */
  public function __construct(ConfigFactory $configFactory, EntityTypeManagerInterface $entityTypeManager) {
    $this->configFactory = $configFactory->getEditable('content_readability.settings');
    $this->entityTypeManager = $entityTypeManager;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'content_readability.admin.config';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['content_readability_help'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Content Readability Help'),
      '#default_value' => $this->configFactory->get('content_readability_help'),
      '#maxlength' => NULL,
      '#description' => $this->t('Link to generic help about content readability
      Open to be customized for those with internal rules or an example page.'),
    ];

    $form['content_readability_add'] = [
      '#title' => $this->t('Add Profile'),
      '#type' => 'link',
      '#url' => Url::fromRoute('content_readability.add_profile.config'),
      '#attributes' => [
        'class' => 'button button-action button--primary button--small',
        'style' => 'margin:0',
      ],
    ];

    $form['content_readability_profiles'] = [
      '#type' => 'table',
      '#caption' => $this->t('List of Content Readability Profiles'),
      '#header' => [
        $this->t('Title'),
        $this->t('Grade Level'),
        $this->t('Operations'),
        $this->t('Weight'),
      ],
      '#tabledrag' => [
        [
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'table-sort-weight',
          'hidden' => TRUE,
        ],
      ],
    ];

    $profiles = $this->configFactory->get('content_readability_profiles');

    usort($profiles, function ($item1, $item2) {
      return $item1['weight'] <=> $item2['weight'];
    });

    foreach ($profiles as $index => $profile) {
      $form['content_readability_profiles'][$index]['#attributes']['class'][] = 'draggable';
      $form['content_readability_profiles'][$index]['name'] = [
        '#markup' => $profile['name'],
        '#type' => 'value',
        '#value' => $profile['machine_name'],
      ];
      $form['content_readability_profiles'][$index]['grade'] = [
        '#markup' => $profile['grade'],
        '#type' => 'value',
        '#value' => $profile['grade'],
      ];

      $links['edit'] = [
        'title' => $this->t('Edit'),
        'url' => Url::fromRoute('content_readability.edit_profile.config', ['profile' => $profile['machine_name']]),
      ];

      $links['delete'] = [
        'title' => $this->t('Delete'),
        'url' => Url::fromRoute('content_readability.delete_profile.config', ['profile' => $profile['machine_name']]),
      ];

      $form['content_readability_profiles'][$index]['operations'] = [
        '#type' => 'operations',
        '#links' => $links,
      ];

      // Weight column element.
      $form['content_readability_profiles'][$index]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for @title', ['@title' => $profile['name']]),
        '#title_display' => 'invisible',
        '#default_value' => $profile['weight'],
       // Classify the weight element for #tabledrag.
        '#attributes' => ['class' => ['table-sort-weight']],
      ];
    }

    $bundles = [];
    // Look into using service when less tired
    // public function EntityTypeBundleInfo::getBundleInfo.
    $content_types = $this->entityTypeManager->getStorage('node_type')->loadMultiple();
    $enabled_content_types = $this->configFactory->get('content_readability_visibility');

    // Loop through content types to build checkbox options.
    foreach ($content_types as $bundle) {
      $bundles[$bundle->id()] = $bundle->label();
    }

    $form['content_readability'] = [
      '#type' => 'details',
      '#open' => 1,
      '#title' => $this->t('Visibility'),
    ];

    $form['content_readability']['visibility'] = [
      "#type" => 'checkboxes',
      "#title" => $this->t('Attach to the following content types'),
      "#options" => $bundles,
      "#default_value" => $enabled_content_types,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $this->configFactory->set('content_readability_help', $values['content_readability_help']);

    $profiles = $this->configFactory->get('content_readability_profiles');
    foreach ($values['content_readability_profiles'] as $profile) {
      $profiles[$profile['name']]['weight'] = $profile['weight'];
    }

    $this->configFactory->set('content_readability_profiles', $profiles);
    $this->configFactory->set('content_readability_visibility', $values['visibility']);

    $this->configFactory->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['content_readability.settings'];
  }

}
