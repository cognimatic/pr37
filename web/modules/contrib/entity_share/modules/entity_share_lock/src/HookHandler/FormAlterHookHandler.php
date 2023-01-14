<?php

declare(strict_types = 1);

namespace Drupal\entity_share_lock\HookHandler;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\entity_share_client\Service\StateInformationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Hook handler for the form_alter() hook.
 */
class FormAlterHookHandler implements ContainerInjectionInterface {
  use StringTranslationTrait;

  /**
   * The machine name of the locked policy.
   */
  const LOCKED_POLICY = 'locked';

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The state information service.
   *
   * @var \Drupal\entity_share_client\Service\StateInformationInterface
   */
  protected $stateInformation;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\entity_share_client\Service\StateInformationInterface $stateInformation
   *   The state information service.
   */
  public function __construct(
    MessengerInterface $messenger,
    StateInformationInterface $stateInformation
  ) {
    $this->messenger = $messenger;
    $this->stateInformation = $stateInformation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger'),
      $container->get('entity_share_client.state_information')
    );
  }

  /**
   * Disable a content form depending on criteria.
   *
   * @param array $form
   *   Nested array of form elements that comprise the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form. The arguments that
   *   \Drupal::formBuilder()->getForm() was originally called with are
   *   available in the array $form_state->getBuildInfo()['args'].
   * @param string $form_id
   *   String representing the name of the form itself. Typically, this is the
   *   name of the function that generated the form.
   */
  public function formAlter(array &$form, FormStateInterface $form_state, $form_id) {
    $build_info = $form_state->getBuildInfo();

    // Check if acting on an entity form.
    if (!isset($build_info['callback_object']) || !($build_info['callback_object'] instanceof EntityFormInterface)) {
      return;
    }

    // Check that it is an edit form.
    if (!preg_match('/_edit_form$/', $form_id)) {
      return;
    }

    $entity_form = $build_info['callback_object'];
    $entity = $entity_form->getEntity();
    $entity_type = $entity->getEntityType();
    $entity_type_id = $entity_type->id();

    // Check if it is a content entity.
    if ($entity_type->getGroup() != 'content') {
      return;
    }

    // Do not act on user.
    if ($entity_type_id == 'user') {
      return;
    }

    // If the entity type does not have a UUID it can not be imported with
    // Entity Share.
    if (!$entity_type->hasKey('uuid')) {
      return;
    }

    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    /** @var \Drupal\entity_share_client\Entity\EntityImportStatusInterface $import_status */
    $import_status = $this->stateInformation->getImportStatusOfEntity($entity);

    // Check if the entity is from an import.
    if (!$import_status) {
      return;
    }

    if ($import_status->getPolicy() == self::LOCKED_POLICY) {
      $form['#disabled'] = TRUE;
      $this->messenger->addWarning($this->t('The entity had been locked from edition because of an import policy.'));
    }
  }

}