<?php

namespace Drupal\viewer\Form\Viewer;

use Drupal\Core\Form\FormStateInterface;

/**
 * Viewer settings form.
 *
 * @ingroup viewer
 */
class SettingsForm extends BaseForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'viewer_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $viewer = NULL, $viewer_source_id = NULL) {
    if ($this->getKeyVal('viewer') != $viewer->getPluginId() && empty($this->getKeyVal('source'))) {
      // Make sure we don't access this page directly by skipping initial step.
      return $this->redirecToListing();
    }
    $form_state->setStorage([
      'viewer' => $viewer,
      'viewer_source' => (!empty($viewer_source_id) ? $this->loadViewerSource($viewer_source_id) : NULL),
    ]);
    $params = [
      'viewer_source' => (!empty($viewer_source_id) ? $this->loadViewerSource($viewer_source_id) : NULL),
      'settings' => [],
      'configuration' => $this->getKeyVal('configuration', []),
    ];
    $form = parent::buildForm($form, $form_state);
    $settings_form = $viewer->settingsForm($form, $form_state, $params);
    if ($settings_form !== $form) {
      $form = array_merge($form, $settings_form);
    }
    else {
      $form['nosettings'] = ['#markup' => $this->t('This viewer plugin does not have any configuration.')];
    }
    $form['actions']['submit']['#value'] = $this->t('Save');
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $storage = $form_state->getStorage();
    if ($settings = $storage['viewer']->settingsValues($form, $form_state)) {
      $this->setKeyVal('settings', $settings);
    }
    $this->save();
    $form_state->setRedirect('entity.viewer.collection');
  }

}
