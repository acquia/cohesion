<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\Plugin\EntityUpdate\_0032EntityUpdate;

/**
 * Class MockUpdateEntity.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class _0032MockUpdateEntity extends EntityMockBase implements EntityJsonValuesInterface {
}

/**
 * @group Cohesion
 */
class _0032EntityUpdateUnitTest extends _0032EntityUpdate {

  /**
   * @var unit_0032MockUpdateEntity*/
  protected $unit;

  private $fixture_layout = '{"canvas": [{"title": "TEST CC", "type": "component", "componentContentId": "cc_1", "uid": "cc_1", "componentId": "cpt_text", "category": "category-4", "componentType": "wysiwyg", "url": "/admin/cohesion/components/component_contents/1", "uuid": "0b726ca3-72ef-45f2-b0d8-88f8bc81ebac", "parentUid": "root", "isContainer": 0, "children": [] }, {"type": "container", "uid": "drupal-field", "title": "Field", "status": {"collapsed": true }, "uuid": "d3770d67-640d-44b4-93cd-7773be658817", "parentUid": "root", "isContainer": true, "children": [] } ], "mapper": {"d3770d67-640d-44b4-93cd-7773be658817": {} }, "model": {"d3770d67-640d-44b4-93cd-7773be658817": {"settings": {"title": "Field - Layout canvas", "settings": {"drupalField": ""}, "drupalField": "content.field_layout_canvas"}, "context-visibility": {"contextVisibility": {"condition": "ALL"} }, "styles": {"settings": {"element": "drupal-field"} } } }, "previewModel": {"d3770d67-640d-44b4-93cd-7773be658817": {} }, "variableFields": {"d3770d67-640d-44b4-93cd-7773be658817": [] }, "meta": {"fieldHistory": [] } }';

  public function fetchUUID($id) {
    return 'cc_b167bbba-52ed-4774-921e-aa4bdb9eb421';
  }

  /**
   * {@inheritdoc}
   */
  public function setUp():void {
    $this->unit = new _0032EntityUpdate([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0032EntityUpdate::runUpdate
   */
  public function testRunUpdate() {

    $layout = new _0032MockUpdateEntity($this->fixture_layout, TRUE);
    $this->assertionsLayoutCanvasBefore($layout->getDecodedJsonValues());
    $this->unit->runUpdate($layout);
    $this->assertionsLayoutCanvasAfter($layout->getDecodedJsonValues());
    $this->unit->runUpdate($layout);
    $this->assertionsLayoutCanvasAfter($layout->getDecodedJsonValues());
  }

  private function assertionsLayoutCanvasBefore($layout_array_before) {
    $this->assertEquals("cc_1", $layout_array_before['canvas'][0]['uid'], 'component content uid');
    $this->assertEquals("cc_1", $layout_array_before['canvas'][0]['componentContentId'], 'component content componentContentId');
  }

  private function assertionsLayoutCanvasAfter($layout_array_after) {
    $this->assertEquals("cc_b167bbba-52ed-4774-921e-aa4bdb9eb421", $layout_array_after['canvas'][0]['uid'], 'component content uid');
    $this->assertEquals("cc_b167bbba-52ed-4774-921e-aa4bdb9eb421", $layout_array_after['canvas'][0]['componentContentId'], 'component content componentContentId');
  }

}
