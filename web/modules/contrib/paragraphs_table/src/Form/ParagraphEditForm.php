<?php

namespace Drupal\paragraphs_table\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;

/**
 * Paragraph Edit Form class.
 */
class ParagraphEditForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  protected function init(FormStateInterface $form_state) {
    $langcode = \Drupal::languageManager()->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();
    $form_state->set('langcode', $langcode);

    if (!$this->entity->hasTranslation($langcode)) {
      $manager = \Drupal::service('content_translation.manager');

      $translation_source = $this->entity;

      $host = $this->entity->getParentEntity();
      $host_source_langcode = $host->language()->getId();
      if ($host->hasTranslation($langcode)) {
        $host = $host->getTranslation($langcode);
        $host_source_langcode = $manager->getTranslationMetadata($host)->getSource();
      }

      if ($this->entity->hasTranslation($host_source_langcode)) {
        $translation_source = $this->entity->getTranslation($host_source_langcode);
      }

      $this->entity = $this->entity->addTranslation($langcode, $translation_source->toArray());
      $manager->getTranslationMetadata($this->entity)->setSource($translation_source->language()->getId());
    }
    parent::init($form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $field_name = $this->entity->get('parent_field_name')->value;
    $host = $this->entity->getParentEntity();
    $entity_type = $host->getEntityTypeId();
    $bundle = $host->bundle();
    $entityFieldManager = \Drupal::service('entity_field.manager')->getFieldDefinitions($entity_type, $bundle);
    $form['#title'] = $this->t('Edit %type item %id', [
      '%type' => $entityFieldManager[$field_name]->getLabel(),
      '%id' => $this->entity->id(),
    ]);
    $form = parent::form($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function save(array $form, FormStateInterface $form_state) {
    return $this->entity->save();
  }

}
