<?php

declare(strict_types=1);

namespace Drupal\auditfiles\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * @implements \Drupal\auditfiles\Form\AuditFilesAuditorFormInterface
 */
trait AuditFilesAuditorFormTrait {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    /** @var array{confirm: bool, files: string[], op: string} $storage */
    $storage = $form_state->getStorage();
    return !isset($storage['confirm'])
      ? $this->buildListForm($form, $form_state)
      : $this->buildConfirmForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  abstract public function buildListForm(array $form, FormStateInterface $form_state): array;

  /**
   * {@inheritdoc}
   */
  abstract public function buildConfirmForm(array $form, FormStateInterface $form_state): array;

}
