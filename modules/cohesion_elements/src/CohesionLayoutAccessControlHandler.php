<?php

namespace Drupal\cohesion_elements;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for cohesion layout entity.
 *
 * @see \Drupal\cohesion_elements\Entity\CohesionLayout.
 */
class CohesionLayoutAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $cohesion_layout, $operation, AccountInterface $account) {
    // Allowed when the operation is not view or the status is true.
    /** @var \Drupal\cohesion_elements\Entity\CohesionLayout $cohesion_layout */
    if ($cohesion_layout->getParentEntity() != NULL) {
      // Delete permission on the cohesion_layout, should just depend on
      // 'update' access permissions on the parent.
      $operation = ($operation == 'delete') ? 'update' : $operation;
      return $cohesion_layout->getParentEntity()->access($operation, $account, TRUE);
    }
    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    // Allowed when nobody implements.
    return AccessResult::allowed();
  }

}
