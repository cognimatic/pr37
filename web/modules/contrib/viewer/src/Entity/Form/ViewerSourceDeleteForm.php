<?php

namespace Drupal\viewer\Entity\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for ViewerSource delete forms.
 *
 * @ingroup viewer
 */
class ViewerSourceDeleteForm extends ContentEntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $entity = $this->getEntity();
    return $this->t('You are about to delete %name', ['%name' => $entity->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Yes, delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $form['actions']['cancel']['#attributes']['class'][] = 'dialog-cancel';
    $form['description'] = [
      '#markup' => $this->t('IMPORTANT: This action cannot be undone. All Viewers that are using this Viewer Source will be also deleted.'),
      '#prefix' => '<p>',
      '#suffx' => '</p>',
    ];
    return $form;
  }

}
