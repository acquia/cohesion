<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel;

use Drupal\sitestudio_data_transformers\Handlers\ElementLevel\CustomElementHandler;

/**
 * Test for custom element handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel\CustomElementHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\CustomElementHandler
 */
class CustomElementHandlerTest extends ElementHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"type":"item","uid":"example_element","isCustom":true,"title":"Example element","selected":false,"status":{"collapsed":true},"iconColor":"custom","uuid":"6d18ed2c-053b-4ccf-8ca5-c8c80ce1372e","parentUid":"root","children":[]}],"mapper":{"6d18ed2c-053b-4ccf-8ca5-c8c80ce1372e":{"settings":{"formDefinition":[{"formKey":"example_element_settings","children":[{"formKey":"example_element_dynamic","breakpoints":[],"activeFields":[{"name":"src","active":true}]}]}],"selectorType":"topLevel","form":null,"items":[],"title":"Default"}}},"model":{"6d18ed2c-053b-4ccf-8ca5-c8c80ce1372e":{"settings":{"myselectfield":"option1","mynumberselectfield":200,"mycheckboxfield":true,"myothercheckboxfield":false,"mywysiwygfield":{"text":"<p>This is some example content.<\/p>","textFormat":"cohesion"},"title":"Example element","mytextfield":"some text","mytextareafield":"text field"}}},"previewModel":{"6d18ed2c-053b-4ccf-8ca5-c8c80ce1372e":{}},"variableFields":{"6d18ed2c-053b-4ccf-8ca5-c8c80ce1372e":[]},"meta":{}}';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->handler = new CustomElementHandler($this->moduleHandler);
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\CustomElement::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'example_element',
      'id' => '6d18ed2c-053b-4ccf-8ca5-c8c80ce1372e',
      'data' => [
        'settings' => json_decode('{"myselectfield": "option1", "mynumberselectfield": 200, "mycheckboxfield": true, "myothercheckboxfield": false, "mywysiwygfield": { "text": "<p>This is some example content.</p>", "textFormat": "cohesion"}, "title": "Example element", "mytextfield": "some text", "mytextareafield": "text field"}'),
      ],
    ];
    parent::testGetData();
  }


}
