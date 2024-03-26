<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Plugin\EntityUpdate\_0019EntityUpdate;

class _0019EntityUpdateMock extends _0019EntityUpdate {

  private $fixture_style_guide = '{"model":{"9f5c3dcc-bc51-4fe5-91c0-fab6ece92c06":{"settings":{"title":"Input","schema":{"type":"string"},"machineName":"input"},"contextVisibility":{"condition":"ALL"}},"a2adb371-d8d3-4535-bcfa-7749277b4a8b":{"settings":{"title":"Select","type":"cohSelect","selectType":"custom","schema":{"type":"string"},"options":[{"label":"Cite","value":"cite"},{"label":"Em","value":"em"},{"label":"Span","value":"span"}],"machineName":"select"},"contextVisibility":{"condition":"ALL"}},"e8b8d279-e00d-4da5-ac21-37544c3d6f31":{"settings":{"title":"Toggle","type":"checkboxToggle","schema":{"type":"string"},"toggleType":"string","machineName":"toggle","trueValue":"fictive-class-name"},"contextVisibility":{"condition":"ALL"}},"fa23a0f4-1967-47a0-bbc0-66dda8ca1778":{"settings":{"title":"Image uploader","type":"cohFileBrowser","options":{"buttonText":"Select image","imageUploader":false,"allowedDescription":"Allowed: png, gif, jpg, jpeg \nMax file size: 2MB","removeLabel":"Remove"},"isStyle":true,"defaultActive":true,"schema":{"type":"string"},"machineName":"image-uploader"},"contextVisibility":{"condition":"ALL"}},"22990136-13ce-4635-b10f-1c7191bb08c6":{"settings":{"title":"Color picker","type":"cohColourPickerOpener","colourPickerOptions":{"flat":true,"showOnly":""},"schema":{"type":"object"},"restrictBy":"none","machineName":"color-picker"},"contextVisibility":{"condition":"ALL"}},"e9d545ae-bea2-43d5-8989-1d26426d6e91":{"settings":{"title":"Range slider","type":"cohRange","min":100,"max":900,"step":100,"schema":{"type":"number"},"machineName":"range-slider"},"contextVisibility":{"condition":"ALL"}}},"mapper":{},"previewModel":{"9f5c3dcc-bc51-4fe5-91c0-fab6ece92c06":{},"a2adb371-d8d3-4535-bcfa-7749277b4a8b":{},"e8b8d279-e00d-4da5-ac21-37544c3d6f31":{},"fa23a0f4-1967-47a0-bbc0-66dda8ca1778":{},"22990136-13ce-4635-b10f-1c7191bb08c6":{},"e9d545ae-bea2-43d5-8989-1d26426d6e91":{}},"variableFields":{"9f5c3dcc-bc51-4fe5-91c0-fab6ece92c06":[],"a2adb371-d8d3-4535-bcfa-7749277b4a8b":[],"e8b8d279-e00d-4da5-ac21-37544c3d6f31":[],"fa23a0f4-1967-47a0-bbc0-66dda8ca1778":[],"22990136-13ce-4635-b10f-1c7191bb08c6":[],"e9d545ae-bea2-43d5-8989-1d26426d6e91":[]},"styleGuideForm":[{"type":"form-field","uid":"form-range-slider","title":"Range slider","status":{"collapsed":false},"parentUid":"root","uuid":"e9d545ae-bea2-43d5-8989-1d26426d6e91","humanId":"Field 6","isContainer":false},{"type":"form-field","uid":"form-colorpicker","title":"Color picker","status":{"collapsed":false},"parentUid":"root","uuid":"22990136-13ce-4635-b10f-1c7191bb08c6","humanId":"Field 5","isContainer":false},{"type":"form-field","uid":"form-image","title":"Image uploader","status":{"collapsed":false},"parentUid":"root","uuid":"fa23a0f4-1967-47a0-bbc0-66dda8ca1778","humanId":"Field 4","isContainer":false},{"type":"form-field","uid":"form-checkbox-toggle","title":"Toggle","status":{"collapsed":false},"parentUid":"root","uuid":"e8b8d279-e00d-4da5-ac21-37544c3d6f31","humanId":"Field 3","isContainer":false},{"type":"form-field","uid":"form-select","title":"Select","status":{"collapsed":false},"parentUid":"root","uuid":"a2adb371-d8d3-4535-bcfa-7749277b4a8b","humanId":"Field 2","isContainer":false},{"type":"form-field","uid":"form-input","title":"Input","status":{"collapsed":false},"parentUid":"root","uuid":"9f5c3dcc-bc51-4fe5-91c0-fab6ece92c06","humanId":"Field 1","isContainer":false}]}';

  public function styleGuideLoad($style_guide_uuid) {
    return new EntityMockBase($this->fixture_style_guide, TRUE);
  }

}

/**
 * @group Cohesion
 */
class _0019EntityUpdateUnitTest extends EntityUpdateUnitTestCase {

  /**
   * @var unit\Drupal\cohesion\Plugin\EntityUpdate\_0019EntityUpdate*/
  protected $unit;

  private $fixture = '{"model":{"24dce085-7d16-44b6-8c7d-71ef0daa8bb2":{"e9d545ae-bea2-43d5-8989-1d26426d6e91":600,"22990136-13ce-4635-b10f-1c7191bb08c6":{"name":"Black","uid":"black","value":{"hex":"#000000","rgba":"rgba(0, 0, 0, 1)"},"wysiwyg":true,"class":".coh-color-black","variable":"$coh-color-black","inuse":false},"fa23a0f4-1967-47a0-bbc0-66dda8ca1778":"[media-reference:file:0e9e72b4-a708-4efa-8331-4770b6b23163]","e8b8d279-e00d-4da5-ac21-37544c3d6f31":"fictive-class-name","a2adb371-d8d3-4535-bcfa-7749277b4a8b":"span","9f5c3dcc-bc51-4fe5-91c0-fab6ece92c06":"Text for inline element"}},"changedFields":["model.24dce085-7d16-44b6-8c7d-71ef0daa8bb2.e9d545ae-bea2-43d5-8989-1d26426d6e91","model.24dce085-7d16-44b6-8c7d-71ef0daa8bb2.22990136-13ce-4635-b10f-1c7191bb08c6","model.24dce085-7d16-44b6-8c7d-71ef0daa8bb2.fa23a0f4-1967-47a0-bbc0-66dda8ca1778","model.24dce085-7d16-44b6-8c7d-71ef0daa8bb2.e8b8d279-e00d-4da5-ac21-37544c3d6f31","model.24dce085-7d16-44b6-8c7d-71ef0daa8bb2.a2adb371-d8d3-4535-bcfa-7749277b4a8b","model.24dce085-7d16-44b6-8c7d-71ef0daa8bb2.9f5c3dcc-bc51-4fe5-91c0-fab6ece92c06"]}';

  /**
   * {@inheritdoc}
   */
  public function setUp():void {
    $this->unit = new _0019EntityUpdateMock([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0018EntityUpdate::runUpdate
   */
  public function testRunUpdate() {

    $style_guide_manager = new EntityMockBase($this->fixture, FALSE);
    $json_values = $style_guide_manager->getDecodedJsonValues(TRUE);
    $this->assertEquals($json_values->model->{'24dce085-7d16-44b6-8c7d-71ef0daa8bb2'}->{"e8b8d279-e00d-4da5-ac21-37544c3d6f31"}, "fictive-class-name");
    $this->unit->process($style_guide_manager, '24dce085-7d16-44b6-8c7d-71ef0daa8bb2');
    $json_values = $style_guide_manager->getDecodedJsonValues(TRUE);
    $this->assertEquals($json_values->model->{'24dce085-7d16-44b6-8c7d-71ef0daa8bb2'}->{"e8b8d279-e00d-4da5-ac21-37544c3d6f31"}, TRUE);

  }

}
