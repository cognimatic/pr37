<?php

namespace Drupal\viewer\Entity\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * ViewerEditForm form controller to edit viewer.
 *
 * @ingroup viewer
 */
class ViewerEditForm extends BaseContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    // Hide some fields from UI.
    unset($form['actions']['delete'], $form['viewer_plugin']);
    $plugin = $this->entity->getViewerPlugin();
    if ($plugin->isEmptyViewerSource()) {
      unset($form['viewer_source']);
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
    $entity = $this->entity;
    // Setting new import timestamp.
    if (!empty($form_state->getValue('import_frequency')[0]['value'])) {
      $this->entity->setNextImport($form_state->getValue('import_frequency')[0]['value']);
    }
    parent::save($form, $form_state);
    $message = $this->t('Viewer %label updated', [
      '%label' => $entity->label(),
    ]);
    $this->loggerFactory->get('viewer')->notice($message);
    $this->messenger->addMessage($message);
    $form_state->setRedirect('entity.viewer.collection');
  }

}
