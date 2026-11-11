<?php

namespace Drupal\Tests\cohesion\Unit;

use Drupal\cohesion\ApiUtils;
use Drupal\Component\Uuid\Php;
use Drupal\Tests\UnitTestCase;

/**
 * @group Cohesion
 */
class ApiUtilsUnitTest extends UnitTestCase {

  /**
   * @var \Drupal\cohesion\ApiUtils*/
  protected $unit;

  /**
   * Before a test method is run, setUp() is invoked.
   * Create new unit object.
   */
  public function setUp(): void {
    // Create a mock of the Php uuid generator service.
    $prophecy = $this->prophesize(Php::CLASS);
    $prophecy->generate()->willReturn('0000-0000-0000-0000');
    $uuid_service_mock = $prophecy->reveal();

    $this->unit = new ApiUtils($uuid_service_mock);
  }

  /**
   * Checks that when an entity JSON is duplicated, only UUIDs as keys are changed and UUIDs in content stay the same.
   *
   * @covers \Drupal\cohesion\ApiUtils::uniqueJsonKeyUuids
   */
  public function testUniqueJsonKeyUuids() {

    // Json contains UUIDs in keys and content.
    $json = json_encode([
      '7eb642eb-eb42-4640-a01a-07871203abe9' => [
        'content' => 'f743d762-7bec-42c4-a3f9-df54174aa669',
      ],
    ]);

    $processed_json = $this->unit->uniqueJsonKeyUuids($json);

    // The UUIS in the content should not have changed.
    $this->assertStringNotContainsString('7eb642eb-eb42-4640-a01a-07871203abe9', $processed_json);
    // The UUID as a key should have changed.
    $this->assertStringContainsString('f743d762-7bec-42c4-a3f9-df54174aa669', $processed_json);

  }

  /**
   * Once test method has finished running, whether it succeeded or failed, tearDown() will be invoked.
   * Unset the $unit object.
   */
  public function tearDown(): void {
    unset($this->unit);
  }

}
