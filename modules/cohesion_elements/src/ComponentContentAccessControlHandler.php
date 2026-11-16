<?php

namespace Drupal\cohesion_elements;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Class ComponentContentAccessControlHandler.
 *
 * Defines the access control handler for the component entity entity type.
 *
 * @see \Drupal\cohesion_elements\Entity\ComponentContent
 *
 * @package Drupal\cohesion_elements
 */
class ComponentContentAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIf($entity->isPublished())->addCacheableDependency($entity)->orIf(AccessResult::allowedIfHasPermission($account, 'administer component content'));
    }

    return AccessResult::allowedIfHasPermission($account, 'administer component content');
  }

  /**
   * {@inheritdoc}
   *
   * Separate from the checkAccess because the entity does not yet exist, it
   * will be created during the 'add' process.
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'administer component content');
  }

}
