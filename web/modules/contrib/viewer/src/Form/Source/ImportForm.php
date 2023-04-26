<?php

namespace Drupal\viewer\Form\Source;

use Drupal\Core\Form\FormStateInterface;

/**
 * Import ImportForm form controller.
 *
 * @ingroup viewer
 */
class ImportForm extends BaseForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'viewer_source_import_form';
  }

  /**
   * Title callback.
   */
  public function getTitle() {
    $viewer_source = \Drupal::routeMatch()->getParameter('viewer_source');
    return $this->t('%name', ['%name' => $viewer_source->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $viewer_source = NULL) {
    $form = parent::buildForm($form, $form_state);
    $form_state->setStorage(['viewer_source' => $viewer_source]);
    $plugin = $this->viewerSourceManager->createInstance($viewer_source->getSourcePluginId());
    $import_form = $plugin->importForm($form, $form_state, $viewer_source->getTypePlugin(), $viewer_source);
    if ($import_form !== $form) {
      $form = array_merge($form, $import_form);
      $form['actions']['submit']['#value'] = !empty($viewer_source->getFrequency()) ? $this->t('Import File') : $this->t('Upload File');
    }
    else {
      $form['missing-plugin']['#markup'] = $this->t('Plugin %name does not have import form.', ['%name' => $viewer_source->getName()]);
      unset($form['actions']['submit']);
    }
    $form['actions']['cancel']['#attributes']['class'] = [
      'button', 'dialog-cancel',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $storage = $form_state->getStorage();
    $viewer_source = $storage['viewer_source'];
    $plugin = $this->viewerSourceManager->createInstance($viewer_source->getSourcePluginId());
    if ($batch_items = $plugin->submitImportForm($form, $form_state, $viewer_source)) {
      $batch = [
        'title' => ($viewer_source->getSourcePluginId() == 'upload')
          ? $this->t('Uploading %name', ['%name' => $viewer_source->label()])
          : $this->t('Importing %name', ['%name' => $viewer_source->label()]),
        'finished' => '\Drupal\viewer\Services\Batch::completeImportCallback',
        'operations' => [],
      ];
      foreach ($batch_items as $item) {
        $batch['operations'][] = $item;
      }
      batch_set($batch);
      \Drupal::messenger()->addMessage($this->t('%name import complete', ['%name' => $viewer_source->label()]));
      $form_state->setRedirect('entity.viewer_source.collection');
    }
  }

}
