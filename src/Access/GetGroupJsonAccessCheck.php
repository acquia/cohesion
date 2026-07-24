<?php

namespace Drupal\cohesion\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Custom access check for cohesion.group_json route.
 */
class GetGroupJsonAccessCheck {

  /**
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected RequestStack $requestStack;

  /**
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   */
  public function __construct(RequestStack $request_stack) {
    $this->requestStack = $request_stack;
  }

  /**
   * @param \Drupal\Core\Session\AccountInterface $account
   * @return \Drupal\Core\Access\AccessResultAllowed|\Drupal\Core\Access\AccessResultForbidden
   */
  public function access(AccountInterface $account): AccessResult {
    $is_cli = (PHP_SAPI === 'cli');
    $is_test = defined('PHPUNIT_COMPOSER_INSTALL') || defined('__PHPUNIT_PHAR__') || getenv('SIMPLETEST_BASE_URL');

    if ($account->isAnonymous()) {
      if ($is_cli && !$is_test) {
        // Allow anonymous in CLI, but not during tests.
        return AccessResult::allowed();
      }
      // Forbid anonymous in all other cases.
      return AccessResult::forbidden();
    }
    // Allow authenticated users.
    return AccessResult::allowed();
  }

}
