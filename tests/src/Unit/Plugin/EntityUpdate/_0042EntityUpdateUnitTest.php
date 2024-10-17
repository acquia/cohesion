<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\Plugin\EntityUpdate\_0042EntityUpdate;

/**
 * Class MockUpdateEntity.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class _0042MockUpdateEntity extends EntityMockBase implements EntityJsonValuesInterface {
}

/**
 * @group Cohesion
 */
class _0042EntityUpdateUnitTest extends EntityUpdateUnitTestCase {


  protected $unit;

  /**
   * Component json_value.
   *
   * @var string
   */
  const COMPONENT_PRE = '{"canvas":[{"type":"container","uid":"heading","title":"Heading","status":{"collapsed":true},"uuid":"60c67a43-ca7f-40a4-8c6d-9cc20df931e8","parentUid":"root","children":[]}],"componentForm":[{"type":"form-field","uid":"form-input","title":"Input","translate":true,"status":{"collapsed":false},"uuid":"1d5f2ced-52e9-44c7-ba38-3ff80fb28b0d","parentUid":"root","children":[]}],"mapper":{"60c67a43-ca7f-40a4-8c6d-9cc20df931e8":{"settings":{"formDefinition":[{"formKey":"heading-settings","children":[{"formKey":"heading-element","breakpoints":[],"activeFields":[{"name":"element","active":true}]},{"formKey":"heading-heading","breakpoints":[],"activeFields":[{"name":"content","active":true}]},{"formKey":"heading-style","breakpoints":[],"activeFields":[{"name":"customStyle","active":true},{"name":"customStyle","active":true},{"name":"customStyle","active":true},{"name":"customStyle","active":true},{"name":"customStyle","active":true},{"name":"customStyle","active":true}]}]}],"selectorType":"topLevel","form":null,"items":[],"title":"Default"}},"1d5f2ced-52e9-44c7-ba38-3ff80fb28b0d":{}},"model":{"60c67a43-ca7f-40a4-8c6d-9cc20df931e8":{"settings":{"element":"h1","title":"Heading","customStyle":[{"customStyle":""}],"content":"[field.1d5f2ced-52e9-44c7-ba38-3ff80fb28b0d]"}},"1d5f2ced-52e9-44c7-ba38-3ff80fb28b0d":{"settings":{"title":"Input","schema":{"type":"string","escape":true},"machineName":"input","tooltipPlacement":"auto right","required":true,"validationMessage":[null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,null,"Test error message"]}}},"previewModel":{"1d5f2ced-52e9-44c7-ba38-3ff80fb28b0d":{},"60c67a43-ca7f-40a4-8c6d-9cc20df931e8":{"settings":{"title":"Heading","element":"h1"}}},"variableFields":{"1d5f2ced-52e9-44c7-ba38-3ff80fb28b0d":[],"60c67a43-ca7f-40a4-8c6d-9cc20df931e8":["settings.content"]},"disabledNodes":[],"meta":{}}';
  const COMPONENT_POST = '{"canvas":[{"type":"container","uid":"heading","title":"Heading","status":{"collapsed":true},"uuid":"60c67a43-ca7f-40a4-8c6d-9cc20df931e8","parentUid":"root","children":[]}],"componentForm":[{"type":"form-field","uid":"form-input","title":"Input","translate":true,"status":{"collapsed":false},"uuid":"1d5f2ced-52e9-44c7-ba38-3ff80fb28b0d","parentUid":"root","children":[]}],"mapper":{"60c67a43-ca7f-40a4-8c6d-9cc20df931e8":{"settings":{"formDefinition":[{"formKey":"heading-settings","children":[{"formKey":"heading-element","breakpoints":[],"activeFields":[{"name":"element","active":true}]},{"formKey":"heading-heading","breakpoints":[],"activeFields":[{"name":"content","active":true}]},{"formKey":"heading-style","breakpoints":[],"activeFields":[{"name":"customStyle","active":true},{"name":"customStyle","active":true},{"name":"customStyle","active":true},{"name":"customStyle","active":true},{"name":"customStyle","active":true},{"name":"customStyle","active":true}]}]}],"selectorType":"topLevel","form":null,"items":[],"title":"Default"}},"1d5f2ced-52e9-44c7-ba38-3ff80fb28b0d":{}},"model":{"60c67a43-ca7f-40a4-8c6d-9cc20df931e8":{"settings":{"element":"h1","title":"Heading","customStyle":[{"customStyle":""}],"content":"[field.1d5f2ced-52e9-44c7-ba38-3ff80fb28b0d]"}},"1d5f2ced-52e9-44c7-ba38-3ff80fb28b0d":{"settings":{"title":"Input","schema":{"type":"string","escape":true},"machineName":"input","tooltipPlacement":"auto right","required":true,"validationMessage":{"required":"Test error message"}}}},"previewModel":{"1d5f2ced-52e9-44c7-ba38-3ff80fb28b0d":{},"60c67a43-ca7f-40a4-8c6d-9cc20df931e8":{"settings":{"title":"Heading","element":"h1"}}},"variableFields":{"1d5f2ced-52e9-44c7-ba38-3ff80fb28b0d":[],"60c67a43-ca7f-40a4-8c6d-9cc20df931e8":["settings.content"]},"disabledNodes":[],"meta":{}}';

  /**
   * {@inheritdoc}
   */
  public function setUp() : void {
    $this->unit = new _0042EntityUpdate([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0042EntityUpdate::runUpdate
   */
  public function testRunUpdate() {

    // Test a Component form.
    $componentLayoutCanvas = new _0042MockUpdateEntity(self::COMPONENT_PRE, TRUE);
    $this->assertEquals(json_decode(self::COMPONENT_PRE, TRUE), $componentLayoutCanvas->getDecodedJsonValues());
    $this->unit->runUpdate($componentLayoutCanvas);
    $this->assertEquals(json_decode(self::COMPONENT_POST, TRUE), $componentLayoutCanvas->getDecodedJsonValues());
  }

}
