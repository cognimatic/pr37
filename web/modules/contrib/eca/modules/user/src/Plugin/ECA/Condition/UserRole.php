<?php

namespace Drupal\eca_user\Plugin\ECA\Condition;

/**
 * Plugin implementation of the ECA condition of a user's role.
 *
 * @EcaCondition(
 *   id = "eca_user_role",
 *   label = "Role of user"
 * )
 */
class UserRole extends CurrentUserRole {

  use UserTrait;

  /**
   * {@inheritdoc}
   */
  public function evaluate(): bool {
    if ($account = $this->loadUserAccount()) {
      $userRoles = $account->getRoles();
      $result = in_array($this->configuration['role'], $userRoles, TRUE);
      return $this->negationCheck($result);
    }
    return FALSE;
  }

}
