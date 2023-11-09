<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel;

use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\cohesion_elements\Entity\Component;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\RangeHandler;
use Drupal\Tests\UnitTestCase;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use Shaper\Validator\JsonSchemaValidator;

/**
 * Test for input form field handler.
 *
 * @group Cohesionz
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel\RangeHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\RangeHandler
 */
class RangeHandlerTest extends UnitTestCase {

  const JSON_VALUES = '{"canvas":[{"uid":"cpt_range_component","type":"component","title":"Range component","enabled":true,"category":"category-10","componentId":"cpt_range_component","uuid":"647c5bc4-c689-4b95-b4be-7862248fda9f","parentUid":"root","status":{},"children":[]}],"mapper":{"647c5bc4-c689-4b95-b4be-7862248fda9f":{}},"model":{"647c5bc4-c689-4b95-b4be-7862248fda9f":{"7bb62938-77e5-4ca5-b4e6-ab4a749ee7b3":7,"settings":{"title":"Range component"}}},"previewModel":{"647c5bc4-c689-4b95-b4be-7862248fda9f":{}},"variableFields":{"647c5bc4-c689-4b95-b4be-7862248fda9f":[]},"meta":{}}';
  const COMPONENT = [
    'uuid' => '819f52be-463d-4b6d-8e46-7a1e4d0a902a',
    'langcode' => 'en',
    'status' => true,
    'label' => 'Range component',
    'id' => 'cpt_range_component',
    'json_values' => '{"canvas":[],"componentForm":[{"type":"form-field","uid":"form-range-slider","title":"Range slider","status":{"collapsed":false},"uuid":"7bb62938-77e5-4ca5-b4e6-ab4a749ee7b3","parentUid":"root","children":[]}],"mapper":{"7bb62938-77e5-4ca5-b4e6-ab4a749ee7b3":{}},"model":{"7bb62938-77e5-4ca5-b4e6-ab4a749ee7b3":{"settings":{"type":"cohRange","title":"Range slider","min":0,"max":10,"step":1,"schema":{"type":"number"},"machineName":"range-slider","tooltipPlacement":"auto right"}}},"previewModel":{"7bb62938-77e5-4ca5-b4e6-ab4a749ee7b3":{}},"variableFields":{"7bb62938-77e5-4ca5-b4e6-ab4a749ee7b3":[]},"disabledNodes":[],"meta":{}}',
    'json_mapper' => '{}',
    'category' => 'cpt_cat_test',
  ];
  const RESULT = [
    'type' => 'form-field',
    'id' => '7bb62938-77e5-4ca5-b4e6-ab4a749ee7b3',
    'data' => [
      'uid' => 'form-range-slider',
      'title' => 'Range slider',
      'value' => 7,
    ],
    'machine_name' => 'range-slider',
  ];

  /**
   * RangeHandler.
   *
   * @var \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\RangeHandler
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


    $this->handler = new RangeHandler($moduleHandlerMock);

    parent::setUp();
  }


  /**
   * @group Cohesionz
   *
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\RangeHandler::getStaticSchema
   */
  public function testGetStaticSchema() {
    $this->assertIsArray($this->handler->getStaticSchema());
  }

  /**
   * @group Cohesionz
   *
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\RangeHandler::getData
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

    $this->assertEquals(self::RESULT, $field_data);
    $validator = new JsonSchemaValidator($this->handler->getStaticSchema(), new Validator(), Constraint::CHECK_MODE_TYPE_CAST);
    $this->assertTrue($validator->isValid($field_data));
  }


}
