<?php

namespace Drupal\civica_payment_handler\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure civica payment settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'civica_payment_handler';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['civica_payment_handler.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = $this->config('civica_payment_handler.settings');

    $form['civica_payment_endpoint'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Civica Payment API endpoint'),
      '#default_value' => $config->get('civica_payment_endpoint'),
      '#description' => $this->t('URL for base API endpoint, without trailing slash.'),
    ];
    $form['callingAppIdentifier'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Calling App Identifier'),
      '#default_value' => $config->get('callingAppIdentifier'),
      '#description' => $this->t('Calling App Identifier - Should be left to Drupal unless changed by Civica.'),
    ];
    $form['customerId'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Customer ID'),
      '#default_value' => $config->get('customerId'),
      '#description' => $this->t('Civica Customer ID for API.'),
    ];
    $form['apiPassword'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Civica API Password'),
      '#default_value' => $config->get('apiPassword'),
      '#description' => $this->t('Password to authenticate with Civica payment API.'),
    ];
    $form['returnUrl'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Payment Return URL from Civica system'),
      '#default_value' => $config->get('returnUrl'),
      '#description' => $this->t('URL to return to after payment.'),
    ];
    $form['notifyURL'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Payment Notify URL from Civica system'),
      '#default_value' => $config->get('notifyURL'),
      '#description' => $this->t('Notification URL to return to after payment.'),
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

    $settings = $this->config('civica_payment_handler.settings');

    $keys = ['civica_payment_endpoint','callingAppIdentifier','customerId','apiPassword','returnUrl','notifyURL'];
    foreach ($keys as $key) {
      $settings->set($key, $form_state->getValue($key));
    }
    //$settings->set('contact_block_markup.value', $form_state->getValue(['contact_block_markup', 'value']));
    //$settings->set('contact_block_markup.format', $form_state->getValue(['contact_block_markup', 'format']));
    $settings->save();
    parent::submitForm($form, $form_state);
  }

  

  
}
