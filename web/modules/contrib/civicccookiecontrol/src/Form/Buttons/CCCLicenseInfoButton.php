<?php

namespace Drupal\civiccookiecontrol\Form\Buttons;

use Drupal\civiccookiecontrol\Form\Steps\CCCStepsEnum;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * License info button.
 */
class CCCLicenseInfoButton extends CCCBaseButton {
  use StringTranslationTrait;

  /**
   * Ajax button or not.
   *
   * @var bool
   */
  private $ajax;

  /**
   * CCCLicenseInfoButton constructor.
   *
   * @param bool $ajax
   *   If ajax button or not.
   */
  public function __construct($ajax) {
    $this->ajax = $ajax;
  }

  /**
   * Get the button key.
   */
  public function getKey() {
    return 'ccc_license_info';
  }

  /**
   * Build the key render array.
   */
  public function build() {
    return [
      '#type' => 'submit',
      '#value' => $this->t('Customize Cookie Control @arrows', ['@arrows' => '>>']),
      '#goto_step' => CCCStepsEnum::CCC_SETTINGS,
      '#submit_handler' => 'submitValues',
    ];
  }

  /**
   * Ajaxify button.
   */
  public function ajaxify() {
    return $this->ajax;
  }

}
