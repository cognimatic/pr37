<?php

namespace Drupal\viewer\Entity\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * ViewerSourceScheduleForm controller for scheduling.
 *
 * @ingroup viewer
 */
class ViewerSourceScheduleForm extends BaseContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    // Hide some fields from UI.
    unset($form['actions']['delete'], $form['import_frequency'], $form['name']);
    $form['actions']['cancel'] = [
      '#type' => 'link',
      '#url' => Url::fromRoute('entity.viewer_source.collection'),
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
    $entity = $this->entity;
    parent::save($form, $form_state);
    $message = $this->t('Viewer Source %label updated', [
      '%label' => $entity->label(),
    ]);
    $this->loggerFactory->get('viewer')->notice($message);
    $this->messenger->addMessage($message);
    $form_state->setRedirect('entity.viewer_source.collection');
  }

}
