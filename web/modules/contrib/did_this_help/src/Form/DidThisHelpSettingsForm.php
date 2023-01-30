<?php

namespace Drupal\did_this_help\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Did This Help? settings form.
 */
class DidThisHelpSettingsForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'did_this_help.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'did_this_help_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    $form['question'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Question'),
      '#default_value' => $config->get('question'),
      '#required' => TRUE,
    ];

    $form['no_answers'] = [
      '#type' => 'textarea',
      '#title' => $this->t('"No" answers'),
      '#default_value' => $config->get('no_answers'),
      '#description' => 'One answer per line',
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable(static::SETTINGS)
      ->set('question', $form_state->getValue('question'))
      ->set('no_answers', $form_state->getValue('no_answers'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
