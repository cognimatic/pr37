<?php

namespace Drupal\viewer\Form\Source;

use Drupal\Core\Form\FormStateInterface;

/**
 * Viewer source form.
 *
 * @ingroup viewer
 */
class SourceForm extends BaseForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'viewer_source_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $viewer_type = NULL, $viewer_source = NULL) {
    if (empty($this->getKeyVal('name')) || $this->getKeyVal('source') != $viewer_source->getPluginId()) {
      return $this->redirecToListing();
    }
    $form_state->setStorage([
      'viewer_type' => $viewer_type,
      'viewer_source' => $viewer_source,
    ]);
    $form = parent::buildForm($form, $form_state);
    $source_form = $viewer_source->sourceForm($form, $form_state, $viewer_type, $viewer_source);
    if ($source_form !== $form) {
      $form = array_merge($form, $source_form);
      $form['actions']['submit']['#value'] = $this->t('Save');
    }
    else {
      $form['missing-plugin']['#markup'] = $this->t('Plugin %name does not have upload form.', ['%name' => $viewer_source->getName()]);
      unset($form['actions']['submit']);
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $storage = $form_state->getStorage();
    if ($batch_items = $storage['viewer_source']->submitSourceForm($form, $form_state, $storage['viewer_type'])) {
      $batch = [
        'title' => ($storage['viewer_source']->getPluginId() == 'upload')
          ? $this->t('Uploading %name', ['%name' => $this->getKeyVal('name')])
          : $this->t('Importing %name', ['%name' => $this->getKeyVal('name')]),
        'finished' => '\Drupal\viewer\Services\Batch::completeImportCallback',
        'operations' => [],
      ];
      foreach ($batch_items as $item) {
        $batch['operations'][] = $item;
      }
      batch_set($batch);
      $form_state->setRedirect('entity.viewer_source.collection');
    }
    else {
      $form_state->setRedirect('viewer_source.new_source', [
        'viewer_type' => $storage['viewer_type']->getPluginId(),
        'viewer_source' => $storage['viewer_source']->getPluginId(),
      ]);
    }
  }

}
