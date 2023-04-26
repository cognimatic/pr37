<?php

namespace Drupal\viewer\Entity\AccessControl;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the ViewerSource entity.
 *
 * @ingroup viewer
 */
class ViewerSourceAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view inactive viewer source');
        }
        return AccessResult::allowedIfHasPermission($account, 'view active viewer source');

      case 'administer':
        return AccessResult::allowedIfHasPermission($account, 'administer viewer source');

      case 'import':
        return AccessResult::allowedIfHasPermission($account, 'add viewer source');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit viewer source');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete viewer source');
    }
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add viewer source');
  }

}
