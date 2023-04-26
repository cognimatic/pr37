<?php

namespace Drupal\viewer\Entity\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * ViewerSettingsForm form controller for plugin settings.
 *
 * @ingroup viewer
 */
class ViewerSettingsForm extends BaseContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    unset($form['actions']['delete'], $form['name'], $form['viewer_plugin'], $form['viewer_source']);
    $viewer = $this->entity->getViewerPlugin();
    $viewer->setViewer($this->entity);
    $params = [
      'viewer_source' => (($datasource = $this->entity->getViewerSource()) ? $datasource : NULL),
      'settings' => $viewer->getSettings(),
      'configuration' => $viewer->getConfiguration(),
    ];
    $settings_form = $viewer->settingsForm($form, $form_state, $params);
    if ($settings_form !== $form) {
      $form = array_merge($form, $settings_form);
    }
    else {
      unset($form['actions']['submit']);
      $form['nosettings'] = ['#markup' => $this->t('This viewer plugin does not have any configurable options.')];
    }
    $form['actions']['cancel'] = [
      '#type' => 'link',
      '#url' => Url::fromRoute('entity.viewer.collection'),
      '#title' => $this->t('Cancel'),
      '#attributes' => [
        'class' => ['button', 'dialog-cancel'],
      ],
      '#weight' => 5,
    ];
    $form['actions']['#weight'] = 999;
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $viewer = $this->entity->getViewerPlugin();
    $viewer->setViewer($this->entity);
    if ($settings = $viewer->settingsValues($form, $form_state)) {
      $this->entity->mergeIntoSettings($settings);
    }
    $message = $this->t('%label viewer settings updated', [
      '%label' => $this->entity->label(),
    ]);
    $this->loggerFactory->get('viewer')->notice($message);
    $this->messenger->addMessage($message);
    parent::save($form, $form_state);
  }

}
