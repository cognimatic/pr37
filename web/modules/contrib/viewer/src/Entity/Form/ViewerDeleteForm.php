<?php

namespace Drupal\viewer\Entity\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Viewer delete forms.
 *
 * @ingroup viewer
 */
class ViewerDeleteForm extends ContentEntityDeleteForm {

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
      '#markup' => $this->t('IMPORTANT: This action cannot be undone. This Viewer no longer will be visible in content where it is used.'),
    ];
    return $form;
  }

}
