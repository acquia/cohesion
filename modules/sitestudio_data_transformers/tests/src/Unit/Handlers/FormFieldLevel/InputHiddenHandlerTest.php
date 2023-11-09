<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel;

use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\cohesion_elements\Entity\Component;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\InputHiddenHandler;
use Drupal\Tests\UnitTestCase;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use Shaper\Validator\JsonSchemaValidator;

/**
 * Test for input form field handler.
 *
 * @group Cohesionz
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel\InputHiddenHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\InputHiddenHandler
 */
class InputHiddenHandlerTest extends UnitTestCase {

  const JSON_VALUES = '{"canvas":[{"uid":"cpt_input_hidden","type":"component","title":"Input hidden","enabled":true,"category":"category-10","componentId":"cpt_input_hidden","uuid":"30cde999-60d4-4dd3-a9bb-933444a846f2","parentUid":"root","status":{},"children":[]}],"mapper":{},"model":{"30cde999-60d4-4dd3-a9bb-933444a846f2":{"db6a56eb-9f72-49eb-8cce-14edcbd728ac":"Hidden value"}},"previewModel":{},"variableFields":{},"meta":{}}';
  const COMPONENT = [
    'uuid' => '9d38eabc-f66f-4aa3-ae0a-b1bf26d96859',
    'langcode' => 'en',
    'status' => true,
    'label' => 'Input hidden',
    'id' => 'cpt_input_hidden',
    'json_values' => '{"canvas":[],"componentForm":[{"type":"form-field","uid":"form-input-hidden","title":"Hidden input","translate":true,"status":{"collapsed":false},"uuid":"db6a56eb-9f72-49eb-8cce-14edcbd728ac","parentUid":"root","children":[]}],"mapper":{"db6a56eb-9f72-49eb-8cce-14edcbd728ac":{}},"model":{"db6a56eb-9f72-49eb-8cce-14edcbd728ac":{"settings":{"type":"cohHidden","title":"Hidden input","schema":{"type":"string","escape":true},"machineName":"hidden-input"},"model":{"value":"Hidden value"}}},"previewModel":{"db6a56eb-9f72-49eb-8cce-14edcbd728ac":{}},"variableFields":{"db6a56eb-9f72-49eb-8cce-14edcbd728ac":[]},"disabledNodes":[],"meta":{}}',
    'json_mapper' => '{}',
    'category' => 'cpt_cat_test',
  ];
  const RESULT = [
    'type' => 'form-field',
    'id' => 'db6a56eb-9f72-49eb-8cce-14edcbd728ac',
    'data' => [
      'uid' => 'form-input-hidden',
      'title' => 'Hidden input',
      'value' => 'Hidden value',
    ],
    'machine_name' => 'hidden-input',
  ];

  /**
   * InputHiddenHandler.
   *
   * @var \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\InputHiddenHandler
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


    $this->handler = new InputHiddenHandler($moduleHandlerMock);

    parent::setUp();
  }


  /**
   * @group Cohesionz
   *
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\InputHiddenHandler::getStaticSchema
   */
  public function testGetStaticSchema() {
    $this->assertIsArray($this->handler->getStaticSchema());
  }

  /**
   * @group Cohesionz
   *
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\InputHiddenHandler::getData
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
