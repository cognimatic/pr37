<?php

namespace Drupal\abc_contact_center\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure abc_contact_center settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'abc_contact_center_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['abc_contact_center.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $settings = $this->config('abc_contact_center.settings');

    $form['rightnow_script'] = [
      '#type' => 'textfield',
      '#title' => t('Oracle rightnow client address'),
      '#default_value' => $settings->get('rightnow_script'),
      '#size' => 150,
      '#maxlength' => 256,
      '#description' => $this->t('The address of the script that injects the Abby functionality on the page.'),
      '#required' => TRUE,
    ];

    $form['liberty_endpoint'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Liberty API address'),
      '#default_value' => $settings->get('liberty_endpoint'),
      '#size' => 200,
      '#maxlength' => 512,
      '#description' => $this->t('The address of the endpoint in the liberty system that provides queue times.'),
      '#required' => TRUE,
    );

    $form['opening_time'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Contact Center Opening Time'),
      '#default_value' => $settings->get('opening_time'),
      '#size' => 8,
      '#maxlength' => 12,
      '#description' => $this->t('Start time for the Abby block to show.'),
      '#required' => TRUE,
    );

    $form['closing_time'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Contact Center closing time'),
      '#default_value' => $settings->get('closing_time'),
      '#size' => 8,
      '#maxlength' => 12,
      '#description' => $this->t('End time for the Abby block to show.'),
      '#required' => TRUE,
    );

    $form['contact_block_markup'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Contact block markup'),
      '#default_value' => $settings->get('contact_block_markup.value'),
      '#description' => $this->t('content for contact block.'),
      '#format' => $settings->get('contact_block_markup.format'),
      '#required' => FALSE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $settings = $this->config('abc_contact_center.settings');

    $keys = ['rightnow_script', 'liberty_endpoint', 'opening_time', 'closing_time'];
    foreach ($keys as $key) {
      $settings->set($key, $form_state->getValue($key));
    }
    $settings->set('contact_block_markup.value', $form_state->getValue(['contact_block_markup', 'value']));
    $settings->set('contact_block_markup.format', $form_state->getValue(['contact_block_markup', 'format']));
    $settings->save();
    parent::submitForm($form, $form_state);
  }

}
