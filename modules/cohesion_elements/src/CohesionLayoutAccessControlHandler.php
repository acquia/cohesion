<?php

namespace Drupal\cohesion_elements;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the paragraphs entity.
 *
 * @see \Drupal\paragraphs\Entity\Paragraph.
 */
class CohesionLayoutAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $cohesion_layout, $operation, AccountInterface $account) {
    // Allowed when the operation is not view or the status is true.
    /** @var \Drupal\cohesion_elements\Entity\CohesionLayout $paragraph */
    if ($cohesion_layout->getParentEntity() != NULL) {
      // Delete permission on the cohesion_layout, should just depend on
      // 'update' access permissions on the parent.
      $operation = ($operation == 'delete') ? 'update' : $operation;
      $parent_access = $cohesion_layout->getParentEntity()->access($operation, $account, TRUE);
      return $access_result = AccessResult::allowedIf($parent_access);
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
