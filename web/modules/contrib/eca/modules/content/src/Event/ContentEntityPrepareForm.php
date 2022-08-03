<?php

namespace Drupal\eca_content\Event;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\eca\Event\FormEventInterface;
use Drupal\eca\Service\ContentEntityTypes;

/**
 * Provides an event when a content entity form is being prepared.
 *
 * @internal
 *   This class is not meant to be used as a public API. It is subject for name
 *   change or may be removed completely, also on minor version updates.
 *
 * @package Drupal\eca_content\Event
 */
class ContentEntityPrepareForm extends ContentEntityBaseEntity implements FormEventInterface {

  /**
   * The operation.
   *
   * @var string
   */
  protected string $operation;

  /**
   * The form state.
   *
   * @var \Drupal\Core\Form\FormStateInterface
   */
  protected FormStateInterface $formState;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   * @param \Drupal\eca\Service\ContentEntityTypes $entity_types
   *   The entity types.
   * @param string $operation
   *   The operation.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function __construct(ContentEntityInterface $entity, ContentEntityTypes $entity_types, string $operation, FormStateInterface $form_state) {
    parent::__construct($entity, $entity_types);
    $this->operation = $operation;
    $this->formState = $form_state;
  }

  /**
   * Gets the operation.
   *
   * @return string
   *   The operation.
   */
  public function getOperation(): string {
    return $this->operation;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormState(): FormStateInterface {
    return $this->formState;
  }

  /**
   * {@inheritdoc}
   */
  public function &getForm(): ?array {
    $form = &$this->formState->getCompleteForm();
    return $form;
  }

}
