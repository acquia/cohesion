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
    // Without a parent, access is allowed. Otherwise access follows the
    // parent, so direct reads (e.g. JSON:API) respect the owner's access.
    // Rendering through the field formatter skips this check, see
    // CohesionEntityReferenceRevisionsEntityFormatter::checkAccess().
    /** @var \Drupal\cohesion_elements\Entity\CohesionLayout $cohesion_layout */
    $parent = $cohesion_layout->getParentEntity();
    if ($parent != NULL) {
      // Delete permission on the cohesion_layout, should just depend on
      // 'update' access permissions on the parent.
      $operation = ($operation == 'delete') ? 'update' : $operation;
      return $parent->access($operation, $account, TRUE);
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
