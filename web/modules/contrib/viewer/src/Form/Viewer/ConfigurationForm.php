<?php

namespace Drupal\viewer\Form\Viewer;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Views plugin configuration form.
 *
 * @ingroup viewer
 */
class ConfigurationForm extends BaseForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'viewer_configuration_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $viewer = NULL, $viewer_source_id = NULL) {
    if ($this->getKeyVal('viewer') != $viewer->getPluginId()) {
      return $this->redirecToListing();
    }
    $form_state->set('viewer', $viewer);

    $params = [
      'configuration' => $this->getKeyVal('configuration', []),
      'settings' => [],
      'viewer_source' => !empty($viewer_source_id) ? $this->loadViewerSource($viewer_source_id) : NULL,
    ];
    $configuration_form = $viewer->configurationForm($form, $form_state, $params);
    if ($configuration_form !== $form) {
      $form = array_merge($form, $configuration_form);
    }
    else {
      $form['nosettings'] = ['#markup' => $this->t('The plugin does not have any configration.')];
    }
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Continue'),
      '#button_type' => 'primary',
      '#weight' => 10,
    ];
    $form['actions']['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#url' => Url::fromRoute('entity.viewer.collection'),
      '#weight' => 10,
      '#attributes' => ['class' => 'button'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($configuration = $form_state->get('viewer')->configurationValues($form, $form_state)) {
      $this->setKeyVal('configuration', $configuration);
    }
    $form_state->setRedirect('viewer.new_settings', [
      'viewer' => $this->getKeyVal('viewer'),
      'viewer_source_id' => $this->getKeyVal('source', 0),
    ]);
  }

}
