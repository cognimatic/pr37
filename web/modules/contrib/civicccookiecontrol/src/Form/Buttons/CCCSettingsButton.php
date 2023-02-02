<?php

namespace Drupal\civiccookiecontrol\Form\Buttons;

use Drupal\civiccookiecontrol\Form\Steps\CCCStepsEnum;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * The settings button.
 */
class CCCSettingsButton extends CCCBaseButton {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getKey() {
    return 'ccc_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#type' => 'submit',
      '#value' => $this->t('@arrows License Info', ['@arrows' => '<<']),
      '#goto_step' => CCCStepsEnum::CCC_LICENSE_INFO,
      '#skip_validation' => TRUE,
    ];
  }

}
