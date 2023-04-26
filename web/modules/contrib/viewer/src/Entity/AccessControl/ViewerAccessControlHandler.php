<?php

namespace Drupal\viewer\Entity\AccessControl;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Viewer entity.
 *
 * @ingroup viewer
 */
class ViewerAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view inactive viewer');
        }
        return AccessResult::allowedIfHasPermission($account, 'view active viewer');

      case 'administer':
        return AccessResult::allowedIfHasPermission($account, 'administer viewer');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit viewer');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete viewer');
    }
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add viewer');
  }

}
