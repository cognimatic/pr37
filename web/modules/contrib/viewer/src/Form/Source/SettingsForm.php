<?php

namespace Drupal\viewer\Form\Source;

use Drupal\Core\Form\FormStateInterface;

/**
 * Viewer source settings form.
 *
 * @ingroup viewer
 */
class SettingsForm extends BaseForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'viewer_source_edit_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $viewer_source = NULL) {
    $form_state->setStorage(['viewer_source' => $viewer_source]);
    $form = parent::buildForm($form, $form_state);
    $plugin = $viewer_source->getSourcePlugin();
    $settings_form = $plugin->settingsForm($form, $form_state, $viewer_source, $viewer_source->getSettings());
    if ($settings_form !== $form) {
      $form = array_merge($form, $settings_form);
    }
    else {
      $form['nosettings'] = ['#markup' => $this->t('This viewer source plugin does not have settings.')];
    }
    $form['actions']['submit']['#value'] = $this->t('Save');
    $form['actions']['cancel']['#attributes']['class'] = [
      'button', 'dialog-cancel',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $storage = $form_state->getStorage();
    $viewer_source = $storage['viewer_source'];
    $plugin = $viewer_source->getSourcePlugin();
    if ($new_settings = $plugin->submitSettingsForm($form, $form_state, $viewer_source, $viewer_source->getSettings())) {
      $viewer_source->setSettings(array_merge($viewer_source->getSettings(), $new_settings));
      $viewer_source->save();
    }
    \Drupal::messenger()->addMessage($this->t('%name settings updated', ['%name' => $viewer_source->label()]));
    $form_state->setRedirect('entity.viewer_source.collection');
  }

}
