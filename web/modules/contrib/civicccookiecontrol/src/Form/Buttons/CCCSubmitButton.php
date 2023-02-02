<?php

namespace Drupal\civiccookiecontrol\Form\Buttons;

use Drupal\civiccookiecontrol\Form\Steps\CCCStepsEnum;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * The submit button.
 */
class CCCSubmitButton extends CCCBaseButton {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getKey() {
    return 'ccc_save';
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#type' => 'submit',
      '#value' => $this->t('Save Cookie Control Settings'),
      '#goto_step' => CCCStepsEnum::CCC_SETTINGS,
      '#submit_handler' => 'submitValues',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function ajaxify() {
    return FALSE;
  }

}
