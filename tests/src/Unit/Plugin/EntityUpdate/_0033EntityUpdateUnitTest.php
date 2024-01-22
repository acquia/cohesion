<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\Plugin\EntityUpdate\_0033EntityUpdate;

/**
 * Class MockUpdateEntity.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class _0033MockUpdateEntity extends EntityMockBase implements EntityJsonValuesInterface {
}

/**
 * Class _0033EntityUpdateMock.
 *
 * @package Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate
 */
class _0033EntityUpdateMock extends _0033EntityUpdate {
  const NODES = [
    '33' => '7493aabf-22e0-4a62-ad1d-08dd5a95f15d',
    '3' => '84a7017e-2416-11eb-adc1-0242ac120002',
    '27' => '8b56827e-2416-11eb-adc1-0242ac120002',
  ];

  /**
   * Returns hardcoded entity UUID for ID.
   * @param $entity_type
   *   Unused entity type.
   * @param $entityId
   *   Entity Id.
   */
  public function getEntityUUID($entity_type, $entityId) {
    if (isset(self::NODES[$entityId])) {
      return self::NODES[$entityId];
    }

    return FALSE;
  }
}

/**
 * @group Cohesion
 */
class _0033EntityUpdateUnitTest extends EntityUpdateUnitTestCase {

  /**
   * Entity Update.
   *
   * @var unit_0033MockUpdateEntity
   */
  protected $unit;

  /**
   * Layout json_value.
   *
   * @var string
   */
  private $fixture_layout = '{
    "canvas": [
        {
            "uid": "coh_component_faq_container",
            "type": "component",
            "title": "FAQ Container & Items",
            "enabled": true,
            "category": "category-8",
            "componentId": "coh_component_faq_container",
            "componentType": "container",
            "uuid": "21230b9b-d4a5-41cb-bc3f-7c691350b740",
            "parentUid": "root",
            "isContainer": 0,
            "children": []
        }
    ],
    "mapper": {},
    "model": {
        "21230b9b-d4a5-41cb-bc3f-7c691350b740": {
            "settings": {
                "title": "FAQ Container & Items"
            },
            "fcf4153b-3378-4f1a-9049-507549bafb74": [
                {
                    "6cc843a4-f072-4858-936c-b8c111226d24": {
                        "entity": {
                            "entityType": "node",
                            "entityId": "33"
                        }
                    }
                },
                {
                    "6cc843a4-f072-4858-936c-b8c111226d24": {
                        "entity": {
                            "entityType": "node",
                            "entityId": "3"
                        }
                    }
                },
                {
                    "6cc843a4-f072-4858-936c-b8c111226d24": {
                        "entity": {
                            "entityType": "node",
                            "entityId": "27"
                        }
                    }
                }
            ]
        }
    },
    "previewModel": {
        "21230b9b-d4a5-41cb-bc3f-7c691350b740": {}
    },
    "variableFields": {
        "21230b9b-d4a5-41cb-bc3f-7c691350b740": []
    },
    "meta": {
        "fieldHistory": []
    }
}';

  /**
   * {@inheritdoc}
   */
  public function setUp():void {
    $this->unit = new _0033EntityUpdateMock([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0033EntityUpdate::runUpdate
   */
  public function testRunUpdate() {

    // Entity reference in layout canvas.
    $layout = new _0033MockUpdateEntity($this->fixture_layout, TRUE);


    $this->assertionsLayoutCanvasBefore($layout->getDecodedJsonValues());
    $this->unit->runUpdate($layout);
    $this->assertionsLayoutCanvasAfter($layout->getDecodedJsonValues());
    $this->unit->runUpdate($layout);
    $this->assertionsLayoutCanvasAfter($layout->getDecodedJsonValues());
  }

  /**
   * Tests layout array before entity update.
   *
   * @param array $layout_array_before
   *   Layout array.
   */
  private function assertionsLayoutCanvasBefore(array $layout_array_before) {
    $this->assertEquals('33', $layout_array_before['model']['21230b9b-d4a5-41cb-bc3f-7c691350b740']['fcf4153b-3378-4f1a-9049-507549bafb74'][0]['6cc843a4-f072-4858-936c-b8c111226d24']['entity']['entityId'], 'entity id');
    $this->assertEquals('3', $layout_array_before['model']['21230b9b-d4a5-41cb-bc3f-7c691350b740']['fcf4153b-3378-4f1a-9049-507549bafb74'][1]['6cc843a4-f072-4858-936c-b8c111226d24']['entity']['entityId'], 'entity id');
    $this->assertEquals('27', $layout_array_before['model']['21230b9b-d4a5-41cb-bc3f-7c691350b740']['fcf4153b-3378-4f1a-9049-507549bafb74'][2]['6cc843a4-f072-4858-936c-b8c111226d24']['entity']['entityId'], 'entity id');
  }

  /**
   * Tests layout array before entity update.
   *
   * @param array $layout_array_after
   *   Layout array.
   */
  private function assertionsLayoutCanvasAfter(array $layout_array_after) {
    $this->assertEquals($this->unit::NODES['33'], $layout_array_after['model']['21230b9b-d4a5-41cb-bc3f-7c691350b740']['fcf4153b-3378-4f1a-9049-507549bafb74'][0]['6cc843a4-f072-4858-936c-b8c111226d24']['entity']['entityId'], 'entity id');
    $this->assertEquals($this->unit::NODES['3'], $layout_array_after['model']['21230b9b-d4a5-41cb-bc3f-7c691350b740']['fcf4153b-3378-4f1a-9049-507549bafb74'][1]['6cc843a4-f072-4858-936c-b8c111226d24']['entity']['entityId'], 'entity id');
    $this->assertEquals($this->unit::NODES['27'], $layout_array_after['model']['21230b9b-d4a5-41cb-bc3f-7c691350b740']['fcf4153b-3378-4f1a-9049-507549bafb74'][2]['6cc843a4-f072-4858-936c-b8c111226d24']['entity']['entityId'], 'entity id');
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown():void {
    unset($this->unit);
  }

}
