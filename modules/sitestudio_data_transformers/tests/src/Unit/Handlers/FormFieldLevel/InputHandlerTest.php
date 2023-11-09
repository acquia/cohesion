<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel;

use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\cohesion_elements\Entity\Component;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\InputHandler;
use Drupal\Tests\UnitTestCase;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use Shaper\Validator\JsonSchemaValidator;

/**
 * Test for input form field handler.
 *
 * @group Cohesionz
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel\InputHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\InputHandler
 */
class InputHandlerTest extends UnitTestCase {

  const JSON_VALUES = '{"canvas":[{"uid":"cpt_input_test","type":"component","title":"Input test","enabled":true,"category":"category-1","componentId":"cpt_input_test","uuid":"ac783511-11fa-4611-9c0b-5ffee389c3f1","parentUid":"root","status":{},"children":[]}],"mapper":{"ac783511-11fa-4611-9c0b-5ffee389c3f1":{}},"model":{"ac783511-11fa-4611-9c0b-5ffee389c3f1":{"2758c170-b86d-4c7c-95f2-71db785ff827":"The Test Input","settings":{"title":"Image DAM"}}},"previewModel":{"ac783511-11fa-4611-9c0b-5ffee389c3f1":{}},"variableFields":{"ac783511-11fa-4611-9c0b-5ffee389c3f1":[]},"meta":{}}';
  const COMPONENT = [
    'uuid' => '25afa449-dc79-4cca-a5fc-f7f7e441c3fd',
    'langcode' => 'en',
    'status' => true,
    'label' => 'Input component',
    'id' => 'cpt_input_test',
    'json_values' => '{"canvas":[],"componentForm":[{"type":"form-field","uid":"form-input","title":"Input","translate":true,"status":{"collapsed":false},"uuid":"2758c170-b86d-4c7c-95f2-71db785ff827","parentUid":"root","children":[]}],"mapper":{"2758c170-b86d-4c7c-95f2-71db785ff827":{}},"model":{"2758c170-b86d-4c7c-95f2-71db785ff827":{"settings":{"type":"cohTextBox","title":"Input","schema":{"type":"string","escape":true},"machineName":"input","tooltipPlacement":"auto right"}}},"previewModel":{"2758c170-b86d-4c7c-95f2-71db785ff827":{}},"variableFields":{"2758c170-b86d-4c7c-95f2-71db785ff827":[]},"disabledNodes":[],"meta":{}}',
    'json_mapper' => '{}',
    'category' => 'cpt_cat_test',
  ];
  const RESULT = [
    'type' => 'form-field',
    'id' => '2758c170-b86d-4c7c-95f2-71db785ff827',
    'data' => [
      'uid' => 'form-input',
      'title' => 'Input',
      'value' => 'The Test Input',
    ],
    'machine_name' => 'input',
  ];

  /**
   * InputHandler.
   *
   * @var \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\InputHandler
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


    $this->handler = new InputHandler($moduleHandlerMock);

    parent::setUp();
  }


  /**
   * @group Cohesionz
   *
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\InputHandler::getStaticSchema
   */
  public function testGetStaticSchema() {
    $this->assertIsArray($this->handler->getStaticSchema());
  }

  /**
   * @group Cohesionz
   *
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\InputHandler::getData
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
