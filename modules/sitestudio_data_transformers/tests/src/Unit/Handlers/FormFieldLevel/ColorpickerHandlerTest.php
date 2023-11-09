<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel;

use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\cohesion_elements\Entity\Component;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\ColorpickerHandler;
use Drupal\Tests\UnitTestCase;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use Shaper\Validator\JsonSchemaValidator;

/**
 * Test for input form field handler.
 *
 * @group Cohesionz
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel\ColorpickerHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\ColorpickerHandler
 */
class ColorpickerHandlerTest extends UnitTestCase {

  const JSON_VALUES = '{"canvas":[{"uid":"cpt_colorpicker_component","type":"component","title":"Colorpicker component","enabled":true,"category":"category-10","componentId":"cpt_colorpicker_component","uuid":"51d6bf75-9905-44b4-bbc7-2edfa9f7f1e6","parentUid":"root","status":{},"children":[]}],"mapper":{"51d6bf75-9905-44b4-bbc7-2edfa9f7f1e6":{}},"model":{"51d6bf75-9905-44b4-bbc7-2edfa9f7f1e6":{"ed299839-006b-4e38-a368-d4e0c674ca38":{"value":{"hex":"#3899ec","rgba":"rgba(56, 153, 236, 1)"}},"settings":{"title":"Colorpicker component"}}},"previewModel":{"51d6bf75-9905-44b4-bbc7-2edfa9f7f1e6":{}},"variableFields":{"51d6bf75-9905-44b4-bbc7-2edfa9f7f1e6":[]},"meta":{}}';
  const COMPONENT = [
    'uuid' => 'ed299839-006b-4e38-a368-d4e0c674ca38',
    'langcode' => 'en',
    'status' => true,
    'label' => 'Colorpicker component',
    'id' => 'cpt_colorpicker_component',
    'json_values' => '{"canvas":[],"componentForm":[{"type":"form-field","uid":"form-colorpicker","title":"Color picker","status":{"collapsed":false},"uuid":"ed299839-006b-4e38-a368-d4e0c674ca38","parentUid":"root","children":[]}],"mapper":{"ed299839-006b-4e38-a368-d4e0c674ca38":{}},"model":{"ed299839-006b-4e38-a368-d4e0c674ca38":{"settings":{"type":"cohColourPickerOpener","title":"Color picker","colourPickerOptions":{"flat":true,"showOnly":""},"schema":{"type":"object"},"machineName":"color-picker","restrictBy":"none","tooltipPlacement":"auto right"}}},"previewModel":{"ed299839-006b-4e38-a368-d4e0c674ca38":{}},"variableFields":{"ed299839-006b-4e38-a368-d4e0c674ca38":[]},"disabledNodes":[],"meta":{}}',
    'json_mapper' => '{}',
    'category' => 'cpt_cat_test',
  ];

  /**
   * ColorpickerHandler.
   *
   * @var \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\ColorpickerHandler
   */
  protected $handler;

  protected function setUp(): void {
    $moduleExtensionMock = $this->getMockBuilder(Extension::class)
      ->disableOriginalConstructor()
      ->getMock();
    $moduleExtensionMock->expects($this->any())
      ->method('getPath')
      ->willReturn(__DIR__ . '/../../../../..');

    $moduleHandlerMock = $this->getMockBuilder(ModuleHandlerInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $moduleHandlerMock->expects($this->any())
      ->method('getModule')
      ->willReturn($moduleExtensionMock);


    $this->handler = new ColorpickerHandler($moduleHandlerMock);

    parent::setUp();
  }


  /**
   * @group Cohesionz
   *
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\ColorpickerHandler::getStaticSchema
   */
  public function testGetStaticSchema() {
    $this->assertIsArray($this->handler->getStaticSchema());
  }

  /**
   * @group Cohesionz
   *
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\ColorpickerHandler::getData
   */
  public function testGetData() {
    $layoutCanvas = new LayoutCanvas(self::JSON_VALUES);
    $component_config = new Component(self::COMPONENT, 'cohesion_component');

    foreach ($layoutCanvas->iterateCanvas() as $element) {
      if ($element->isComponent() && $element->getModel()) {
        foreach ($component_config->getLayoutCanvasInstance()->iterateComponentForm() as $form_field) {
          if (is_string($form_field->getProperty('uid'))) {
            $field_data = $this->handler->getData($form_field, $element->getModel());
          }
        }
      }
    }

    $result = [
      'type' => 'form-field',
      'id' => 'ed299839-006b-4e38-a368-d4e0c674ca38',
      'data' => [
        'uid' => 'form-colorpicker',
        'title' => 'Color picker',
        'value' => json_decode('{"value": {"hex": "#3899ec", "rgba": "rgba(56, 153, 236, 1)"}}'),
      ],
      'machine_name' => 'color-picker',
    ];

    $this->assertEquals($result, $field_data);
    $validator = new JsonSchemaValidator($this->handler->getStaticSchema(), new Validator(), Constraint::CHECK_MODE_TYPE_CAST);
    $this->assertTrue($validator->isValid($field_data));
  }


}
