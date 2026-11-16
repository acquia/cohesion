<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Plugin\EntityUpdate\_0009EntityUpdate;
use Drupal\cohesion_elements\Entity\CohesionElementEntityBase;

/**
 * Class MockDropzoneElementUpdateEntity.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class MockDropzoneElementUpdateEntity extends CohesionElementEntityBase {
  protected $json;

  public function __construct($json) {
    $this->json = $json;
  }

  public function getDecodedJsonValues($switch = TRUE) {
    return json_decode($this->json);
  }

  public function setJsonValue($json) {
    $this->json = $json;
  }

  public function getJsonValues() {
    return $this->json;
  }

}

/**
 * @group Cohesion
 */
class _0009EntityUpdateUnitTest extends EntityUpdateUnitTestCase {

  protected $unit;

  /**
   * {@inheritdoc}
   */
  public function setUp():void {
    // Create a mock of the Php uuid generator service.
    // $prophecy = $this->prophesize(Php::CLASS);
    // $prophecy->generate()->willReturn('0000-0000-0000-0000');
    // $uuid_service_mock = $prophecy->reveal();
    $this->unit = new _0009EntityUpdate([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0009EntityUpdate::runUpdate
   */
  public function testRunUpdate() {
    // Test update from old style.
    $entity = new MockDropzoneElementUpdateEntity('{
  "model": {
    "4624de4a-15da-43ba-b0f9-ab279567d59a": {
      "settings": {
        "dropzoneHideSelector": "",
        "label": "This is my title."
      }
    },
    "1d34ea9c-15da-43ba-b0f9-ab279567d59a": {
      "settings": {
        "someotherelement": "",
        "label": "This is my title."
      }
    }
  }
}');

    $this->unit->runUpdate($entity);

    $this->assertEquals('{"model":{"4624de4a-15da-43ba-b0f9-ab279567d59a":{"settings":{"dropzoneHideSelector":"","title":"This is my title."}},"1d34ea9c-15da-43ba-b0f9-ab279567d59a":{"settings":{"someotherelement":"","label":"This is my title."}}}}', $entity->getJsonValues());

  }

}
