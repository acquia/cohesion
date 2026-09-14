<?php

namespace Drupal\Tests\cohesion\Kernel;

use Drupal\cohesion\Access\GetGroupJsonAccessCheck;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\user\Entity\User;

/**
 *
 * @group Cohesion
 *
 * @requires module cohesion
 */
class GetGroupJsonAccessCheckTest extends KernelTestBase {

  protected static $modules = [
    'system',
    'file',
    'user',
    'cohesion',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->installSchema('cohesion', ['coh_usage']);
    $this->installEntitySchema('user');
  }

  public function testAccessForAuthenticatedUser() {
    $user = User::create([
      'name' => $this->randomMachineName(),
      'status' => 1,
    ]);
    $user->save();

    $request_stack = $this->createMock(RequestStack::class);
    $access_check = new GetGroupJsonAccessCheck($request_stack);

    $this->assertTrue($access_check->access($user)->isAllowed());
  }

  public function testAccessForAnonymousUser() {
    $request_stack = $this->createMock(RequestStack::class);
    $access_check = new GetGroupJsonAccessCheck($request_stack);

    $anon = User::getAnonymousUser();
    assert($anon instanceof AccountInterface);
    $result = $access_check->access($anon);
    $this->assertTrue($result->isForbidden());
  }
}
