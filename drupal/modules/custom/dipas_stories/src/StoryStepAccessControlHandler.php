<?php

namespace Drupal\dipas_stories;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the story step entity type.
 */
class StoryStepAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view story step');

      case 'update':
        return AccessResult::allowedIfHasPermissions($account, ['edit story step', 'administer story step'], 'OR');

      case 'delete':
        return AccessResult::allowedIfHasPermissions($account, ['delete story step', 'administer story step'], 'OR');

      default:
        // No opinion.
        return AccessResult::neutral();
    }

  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermissions($account, ['create story step', 'administer story step'], 'OR');
  }

}
