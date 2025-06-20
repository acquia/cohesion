<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\Plugin\EntityUpdate\_0003EntityUpdate;

/**
 * Class MockUpdateEntity.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class MockUpdateCanvasEntity extends EntityMockBase implements EntityJsonValuesInterface {
}

/**
 * @group Cohesion
 */
class _0003EntityUpdateUnitTest extends EntityUpdateUnitTestCase {

  /**
   * @var unit_0003EntityUpdate*/
  protected $unit;

  private $fixture = '{ "canvas": [ { "uid": "4f582610", "type": "component", "title": "Section: Heading", "enabled": true, "category": "general", "componentId": "4f582610", "componentType": "container", "parentIndex": 0, "uuid": "e56e65fc-d0cc-491d-90fa-c6b32d773e94", "parentUid": "root", "isContainer": 0, "children": [], "componentContentId": "cc_eeecbef0-f4f0-468a-b3a3-090d1a5c5333", "status": {} } ], "model": { "e56e65fc-d0cc-491d-90fa-c6b32d773e94": { "settings": { "title": "Section: Heading" }, "1d7ef21d-f585-4961-9f8e-aa6561ca9179": "Section heading here", "isVariableMode": false, "af30a7c6-76a5-409f-9b65-c38c02007dba": "" } }, "isVariableMode": false }';

  /**
   * {@inheritdoc}
   */
  public function setUp():void {
    $this->unit = new _0003EntityUpdate([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0003EntityUpdate::runUpdate
   */
  public function testRunUpdate() {
    $layout = new MockUpdateCanvasEntity($this->fixture);

    // Run the update.
    $this->unit->updateEntity($layout);

    $this->assertStringNotContainsString($layout->getJsonValues(), 'componentContentId');
    $this->assertStringNotContainsString($layout->getJsonValues(), 'cc_eeecbef0-f4f0-468a-b3a3-090d1a5c5333');
  }

}
