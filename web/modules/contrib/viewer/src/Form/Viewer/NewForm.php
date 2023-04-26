<?php

namespace Drupal\viewer\Form\Viewer;

use Drupal\Core\Form\FormStateInterface;

/**
 * New viewer form.
 *
 * @ingroup viewer
 */
class NewForm extends BaseForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'viewer_new_form';
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
    $form['source'] = [
      '#title' => $this->t('Viewer Source'),
      '#type' => 'select',
      '#description' => $this->t('Choose data provider for the viewer.'),
      '#options' => $this->getSources(),
      '#empty_option' => $this->t('- Select Source -'),
      '#ajax' => [
        'callback' => '::getViewerSelect',
        'wrapper' => 'viewer-options-wrapper',
        'event' => 'change',
      ],
    ];
    // We don't need data source for the plugins with custom transformer forms.
    if ($empty_viewer_source_plugins = $this->getViewerPlugins(TRUE)) {
      $conditions = [];
      foreach ($empty_viewer_source_plugins as $id => $label) {
        $conditions[] = ['value' => $id];
      }
      $form['source']['#states']['invisible'] = [':input[name="viewer"]' => $conditions];
    }
    $form['viewer_wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'viewer-options-wrapper',
      ],
    ];
    if (!empty($form_state->getValue('source'))) {
      $form['viewer_wrapper']['viewer'] = [
        '#title' => $this->t('Viewer'),
        '#type' => 'select',
        '#description' => $this->t('Choose viewer plugin for the data to display.'),
        '#empty_option' => $this->t('- Select Viewer -'),
        '#options' => $this->getViewerPluginOptions(),
        '#required' => TRUE,
      ];
      $form['viewer_wrapper']['viewer']['#options'] = $this->getViewerPluginOptions($form_state->getValue('source'));
      if ($default_viewer = $this->getDefaultViewerPlugin($form_state->getValue('source'))) {
        $form['viewer_wrapper']['viewer']['#value'] = $default_viewer;
      }
    }
    $form['actions']['submit']['#value'] = $this->t('Continue');
    $form['actions']['cancel']['#attributes']['class'] = [
      'button', 'dialog-cancel',
    ];
    return $form;
  }

  /**
   * Viewers AJAX callback.
   */
  public function getViewerSelect(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild(TRUE);
    return $form['viewer_wrapper'];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    if (!empty($values['viewer'])) {
      $viewer = \Drupal::service('plugin.manager.viewer')->createInstance($values['viewer']);
      if (!$viewer->isEmptyViewerSource() && empty($values['source'])) {
        $form_state->setErrorByName('source', $this->t('Viewer Source is required'));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $viewer = \Drupal::service('plugin.manager.viewer')->createInstance($form_state->getUserInput()['viewer']);
    $this->setKeyVal('name', $form_state->getValue('name'));
    $this->setKeyVal('source', $form_state->getValue('source'));
    $this->setKeyVal('viewer', $viewer->getPluginId());
    $form_state->setRedirect('viewer.new_configuration', [
      'viewer' => $viewer->getPluginId(),
      'viewer_source_id' => !empty($form_state->getValue('source')) ? $form_state->getValue('source') : 0,
    ]);
  }

}
