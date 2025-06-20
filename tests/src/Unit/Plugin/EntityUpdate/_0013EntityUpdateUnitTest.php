<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\Plugin\EntityUpdate\_0013EntityUpdate;

/**
 * Class MockUpdateEntity.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class _0013MockUpdateEntity extends EntityMockBase implements EntityJsonValuesInterface {
}

/**
 * @group Cohesion
 */
class _0013EntityUpdateUnitTest extends EntityUpdateUnitTestCase {

  /**
   * @var unit_0013MockUpdateEntity*/
  protected $unit;

  private $fixture_layout = '{"canvas":[],"model":{"8d3c7274-41bf-4ede-945d-4d10e597dc19":{"settings":{"isStyle":true,"type":"cohTypeahead","key":"linkToPage","title":"Linktopage","placeholder":"Typepagename","labelProperty":"name","valueProperty":"id","typeaheadEditable":true,"endpoint":"\/cohesionapi\/autocomplete\/node\/default:node?q=","schema":{"type":"string"}},"contextVisibility":{"condition":"ALL"}}},"mapper":{},"componentForm":[{"type":"form-container","uid":"form-tab-container","title":"Tabcontainer","parentIndex":"form-layout","status":{"collapsed":false},"options":{"formBuilder":true},"children":[{"type":"form-container","uid":"form-tab-item","title":"Tabitem","parentIndex":"form-layout","status":{"collapsed":false},"options":{"formBuilder":true},"children":[{"type":"form-container","uid":"form-section","title":"Fieldgroup","parentIndex":"form-layout","status":{"collapsed":true},"options":{"formBuilder":true},"children":[{"type":"form-field","uid":"form-link","title":"Link","parentIndex":"form-fields","status":{"collapsed":false},"uuid":"8d3c7274-41bf-4ede-945d-4d10e597dc19","parentUid":"form-section","humanId":"Field5","isContainer":false}],"uuid":"80bd5a1a-4024-4f81-a8a6-d52797e857e4","parentUid":"form-accordion","isContainer":true}],"parentUid":"root","uuid":"3a2b6515-ff53-465e-8272-9da6c864fabb","isContainer":true}],"parentUid":"root","uuid":"e66b6ef7-0ef0-4b99-a92a-ba9dfb21e812","isContainer":true}],"previewModel":{"90740fef-ffd4-4ba9-aa5b-6c8cfebec97e":{},"253d0ca8-6a61-40fd-bda4-c51a33b5bbb4":{"settings":{},"hideNoData":{}}}}';

  /**
   * {@inheritdoc}
   */
  public function setUp():void {
    $this->unit = new _0013EntityUpdate([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0013EntityUpdate::runUpdate
   */
  public function testRunUpdate() {

    // WYSIWYG in layout canvas.
    $layout = new _0013MockUpdateEntity($this->fixture_layout, TRUE);
    $this->assertionsLayoutCanvasBefore($layout->getDecodedJsonValues());
    $this->unit->runUpdate($layout);
    $this->assertionsLayoutCanvasAfter($layout->getDecodedJsonValues());
    $this->unit->runUpdate($layout);
    $this->assertionsLayoutCanvasAfter($layout->getDecodedJsonValues());
  }

  private function assertionsLayoutCanvasBefore($layout_array_before) {
    $this->assertEquals('/cohesionapi/autocomplete/node/default:node?q=', $layout_array_before['model']['8d3c7274-41bf-4ede-945d-4d10e597dc19']['settings']['endpoint'], 'endpoint');
  }

  private function assertionsLayoutCanvasAfter($layout_array_after) {
    $this->assertEquals('/cohesionapi/link-autocomplete?q=', $layout_array_after['model']['8d3c7274-41bf-4ede-945d-4d10e597dc19']['settings']['endpoint'], 'endpoint');
  }

}
