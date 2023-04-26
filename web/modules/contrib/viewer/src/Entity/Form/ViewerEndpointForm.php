<?php

namespace Drupal\viewer\Entity\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * ViewerEndpointForm controller to display endpoint URL.
 *
 * @ingroup viewer
 */
class ViewerEndpointForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['endpoint'] = [
      '#type' => 'item',
      '#title' => $this->t('Endpoint URL'),
      '#description' => $this->t('Use this endpoint to get Viewer data, headers, filters, configuration and settings as JSON.'),
      '#markup' => Url::fromRoute('rest.viewer.GET', ['uuid' => $this->entity->uuid()], ['absolute' => TRUE])->toString(),
    ];

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
    // No action for this form.
  }

}
