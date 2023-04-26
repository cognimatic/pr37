<?php

namespace Drupal\viewer\Entity\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * ViewerPreviewForm form controller to display preview.
 *
 * @ingroup viewer
 */
class ViewerPreviewForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    unset($form['actions'], $form['name'], $form['viewer_plugin'], $form['viewer_source']);
    $form['preview'] = [
      '#type' => 'inline_template',
      '#template' => '<iframe src="{{ preview_src }}" style="width: 100%; height: 100%; border: none;" class="viewer-preview-iframe"></iframe>',
      '#context' => [
        'preview_src' => $this->entity->toUrl('iframe_preview_src')->toString(TRUE)->getGeneratedUrl(),
      ],
      '#attached' => [
        'library' => ['viewer/viewer.admin'],
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    // Do nothing here.
  }

}
