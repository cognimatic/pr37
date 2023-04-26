<?php

namespace Drupal\viewer\Entity\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * ViewerConfigurationForm controller for plugin config forms.
 *
 * @ingroup viewer
 */
class ViewerConfigurationForm extends BaseContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    unset($form['actions']['delete'], $form['name'], $form['viewer_plugin'], $form['viewer_source']);

    $viewer = $this->entity->getViewerPlugin();
    $viewer->setViewer($this->entity);

    $params = [
      'configuration' => $this->entity->getConfiguration(),
      'settings' => $this->entity->getSettings(),
      'viewer_source' => $this->entity->getViewerSource(),
    ];

    $configuration_form = $viewer->configurationForm($form, $form_state, $params);
    if ($configuration_form !== $form) {
      $form = array_merge($form, $configuration_form);
    }
    else {
      $form['nosettings'] = ['#markup' => $this->t('This viewer plugin does not have any confiration.')];
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
    if ($configuration = $viewer->configurationValues($form, $form_state)) {
      $this->entity->setConfiguration($configuration);
    }
    $message = $this->t('%label viewer configuration updated', [
      '%label' => $this->entity->label(),
    ]);
    $this->loggerFactory->get('viewer')->notice($message);
    $this->messenger->addMessage($message);
    parent::save($form, $form_state);
  }

}
