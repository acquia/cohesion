<?php

namespace Drupal\Tests\cohesion_elements\Unit;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\cohesion_elements\CohesionLayoutAccessControlHandler;
use Drupal\cohesion_elements\Entity\CohesionLayout;

/**
 * Tests the cohesion layout access control handler.
 *
 * @group Cohesion
 */
class CohesionLayoutAccessControlHandlerTest extends UnitTestCase {

  /**
   * Calls the protected checkAccess() method.
   */
  protected function checkAccess(CohesionLayout $layout, string $operation, AccountInterface $account) {
    $entity_type = $this->prophesize(EntityTypeInterface::class)->reveal();
    $handler = new CohesionLayoutAccessControlHandler($entity_type);
    $method = new \ReflectionMethod($handler, 'checkAccess');
    return $method->invoke($handler, $layout, $operation, $account);
  }

  /**
   * Creates a layout whose parent denies every operation.
   */
  protected function layoutWithDenyingParent(string $expected_operation): CohesionLayout {
    $parent = $this->prophesize(EntityInterface::class);
    $parent->access($expected_operation, \Prophecy\Argument::any(), TRUE)
      ->shouldBeCalled()
      ->willReturn(AccessResult::forbidden());
    $layout = $this->prophesize(CohesionLayout::class);
    $layout->getParentEntity()->willReturn($parent->reveal());
    return $layout->reveal();
  }

  /**
   * View access follows the parent so direct reads respect owner access.
   */
  public function testViewDelegatesToParent() {
    $account = $this->prophesize(AccountInterface::class)->reveal();
    $result = $this->checkAccess($this->layoutWithDenyingParent('view'), 'view', $account);
    $this->assertTrue($result->isForbidden());
  }

  /**
   * Update access is delegated to the parent.
   */
  public function testUpdateDelegatesToParent() {
    $account = $this->prophesize(AccountInterface::class)->reveal();
    $result = $this->checkAccess($this->layoutWithDenyingParent('update'), 'update', $account);
    $this->assertTrue($result->isForbidden());
  }

  /**
   * Delete access is mapped to update access on the parent.
   */
  public function testDeleteMapsToParentUpdate() {
    $account = $this->prophesize(AccountInterface::class)->reveal();
    $result = $this->checkAccess($this->layoutWithDenyingParent('update'), 'delete', $account);
    $this->assertTrue($result->isForbidden());
  }

  /**
   * A layout without a parent is allowed.
   */
  public function testNoParentIsAllowed() {
    $account = $this->prophesize(AccountInterface::class)->reveal();
    $layout = $this->prophesize(CohesionLayout::class);
    $layout->getParentEntity()->willReturn(NULL);
    $result = $this->checkAccess($layout->reveal(), 'view', $account);
    $this->assertTrue($result->isAllowed());
  }

}
