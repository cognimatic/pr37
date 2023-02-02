<?php

namespace Drupal\civiccookiecontrol\Form\Buttons;

use Drupal\civiccookiecontrol\Form\Steps\CCCStepsEnum;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * The submit button.
 */
class CCCFlushFormCacheButton extends CCCBaseButton {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getKey() {
    return 'ccc_flush_form_cache';
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#type' => 'submit',
      '#value' => $this->t('Flush Configuration Form Cache'),
      '#goto_step' => CCCStepsEnum::CCC_SETTINGS,
      '#skip_validation' => TRUE,
      '#submit_handler' => 'clearTempstore',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function ajaxify() {
    return FALSE;
  }

}
