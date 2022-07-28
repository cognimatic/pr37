<?php

namespace Drupal\content_readability\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactory;

/**
 * Displays the Content Readability Edit Form.
 */
class ContentReadabilityEditProfile extends ConfigFormBase {

  /**
   * Configuration Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Constructor.
   */
  public function __construct(ConfigFactory $configFactory) {
    $this->configFactory = $configFactory->getEditable('content_readability.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'content_readability.edit_profile.config';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string $profile = NULL) {
    $profiles = $this->configFactory->get('content_readability_profiles');

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Content Readability Profile Name'),
      '#default_value' => $profiles[$profile]['name'],
      '#maxlength' => NULL,
      '#description' => $this->t('Enter the name of this profile.'),
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#maxlength' => 64,
      '#default_value' => $profiles[$profile]['machine_name'],
      '#disabled' => TRUE,
      '#description' => $this->t('A unique name for this item. It must only
        contain lowercase letters, numbers, and underscores.'),
      '#machine_name' => [
        'exists' => [
          $this,
          'exists',
        ],
      ],
    ];

    $form['grade'] = [
      '#type' => 'number',
      '#title' => $this->t('Grade Level'),
      '#default_value' => $profiles[$profile]['grade'],
      '#description' => $this->t("Enter a whole number that represents the
      Grade Level.  i.e 6 for Sixth Grade. \n The number corresponds
      to the education grade level a person needs to understand the content."),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $profiles = $this->configFactory->get('content_readability_profiles');
    $profiles[$values['id']] = [
      'name' => $values['label'],
      'grade' => $values['grade'],
      'machine_name' => $values['id'],
      'weight' => $profiles[$values['id']]['weight'],
    ];

    $this->configFactory->set('content_readability_profiles', $profiles);
    $this->configFactory->save();

    $form_state->setRedirect('content_readability.admin.config');
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['content_readability.settings'];
  }

}
