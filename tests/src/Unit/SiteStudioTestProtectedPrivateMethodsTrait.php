<?php

namespace Drupal\Tests\cohesion\Unit;

/**
 * A trait allowing Unit testing of protected and private methods.
 */
trait SiteStudioTestProtectedPrivateMethodsTrait {

  /**
   * Calls protected or private methods and returns the result of invocation.
   *
   * @param object $object
   *   Object containing a method to call.
   * @param string $method
   *   Name of the method.
   * @param array $parameters
   *   Arguments needed for the method.
   *
   * @return mixed
   *   Result of invocation.
   *
   * @throws \Exception
   */
  private function callMethod(object $object, string $method, array $parameters = []) {
    try {
      $className = get_class($object);
      $reflection = new \ReflectionClass($className);
    }
    catch (\ReflectionException $e) {
      throw new \Exception($e->getMessage());
    }

    $method = $reflection->getMethod($method);
    $method->setAccessible(TRUE);

    return $method->invokeArgs($object, $parameters);
  }

}
