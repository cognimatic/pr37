<?php

namespace Drupal\eca_user\Plugin\ECA\Condition;

/**
 * Plugin implementation of the ECA condition of a user's id.
 *
 * @EcaCondition(
 *   id = "eca_user_id",
 *   label = "ID of user"
 * )
 */
class UserId extends CurrentUserId {

  use UserTrait;

  /**
   * {@inheritdoc}
   */
  public function evaluate(): bool {
    if ($account = $this->loadUserAccount()) {
      // We need to cast the ID to string to avoid false positives when an
      // empty string value get compared to integed 0.
      $result = (string) $this->configuration['user_id'] === (string) $account->id();
      return $this->negationCheck($result);
    }
    return FALSE;
  }

}
