<?php

namespace Drupal\civiccookiecontrol\Access;

use Drupal\civiccookiecontrol\CCCConfigNames;
use Drupal\civiccookiecontrol\Form\CCCFormHelper;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;

/**
 * Utility class implementing permission checks.
 */
class CookieControlAccess {

  /**
   * Function that checks if user has the perms to admin cookie control.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Current user account.
   *
   * @return \Drupal\Core\Access\AccessResult|\Drupal\Core\Access\AccessResultAllowed|\Drupal\Core\Access\AccessResultNeutral
   *   Return value.
   */
  public function checkAccess(AccountInterface $account) {
    return AccessResult::allowedIf(
          $account->hasPermission('administer civiccookiecontrol') && $this->checkApiKey()
      );
  }

  /**
   * Function that checks if the cookie control api key is valid.
   *
   * @return bool
   *   Return value.
   */
  public static function checkApiKey() {
    if (CCCFormHelper::validateApiKey(
          \Drupal::config(CCCConfigNames::COOKIECONTROL)->get('civiccookiecontrol_api_key'),
          \Drupal::config(CCCConfigNames::COOKIECONTROL)->get('civiccookiecontrol_product')
      ) ==
        \Drupal::config(CCCConfigNames::COOKIECONTROL)->get('civiccookiecontrol_product')) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

}
