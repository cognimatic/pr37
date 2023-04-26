<?php

namespace Drupal\viewer\Form\Source;

use Drupal\Core\Form\FormStateInterface;

/**
 * New viewer source form.
 *
 * @ingroup viewer
 */
class NewForm extends BaseForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'new_viewer_source_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Reset all previously set temp variables.
    $this->deleteKeyVal();
    $form = parent::buildForm($form, $form_state);
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#description' => $this->t('This name will be displayed on the listing page and also will be used in reference fields.'),
      '#required' => TRUE,
    ];
    $form['type'] = [
      '#title' => $this->t('File Type'),
      '#type' => 'select',
      '#description' => $this->t('Choose file type.'),
      '#options' => $this->getTypePlugins(),
      '#empty_option' => $this->t('- Select Type -'),
      '#required' => TRUE,
    ];
    $form['source'] = [
      '#title' => $this->t('Source'),
      '#type' => 'radios',
      '#description' => $this->t('Choose import source for the file.'),
      '#options' => $this->getSourcePlugins(),
      '#empty_option' => $this->t('- Select Source -'),
      '#default_value' => 'upload',
      '#required' => TRUE,
    ];
    $form['actions']['submit']['#value'] = $this->t('Continue');
    $form['actions']['cancel']['#attributes']['class'] = [
      'button', 'dialog-cancel',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->setKeyVal('name', $form_state->getValue('name'));
    $this->setKeyVal('source', $form_state->getValue('source'));
    $this->setKeyVal('type', $form_state->getValue('type'));
    $form_state->setRedirect('viewer_source.new_source', [
      'viewer_type' => $form_state->getValue('type'),
      'viewer_source' => $form_state->getValue('source'),
    ]);
  }

}
