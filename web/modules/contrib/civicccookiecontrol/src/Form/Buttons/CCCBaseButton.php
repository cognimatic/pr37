<?php

namespace Drupal\civiccookiecontrol\Form\Buttons;

/**
 * Base ajax button implementation for cookiecontrol navigation.
 */
abstract class CCCBaseButton implements CCCButtonInterface {

  /**
   * {@inheritdoc}
   */
  public function ajaxify() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getSubmitHandler() {
    return FALSE;
  }

}
