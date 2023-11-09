<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel;

use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\cohesion_elements\Entity\Component;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\TextareaHandler;
use Drupal\Tests\UnitTestCase;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use Shaper\Validator\JsonSchemaValidator;

/**
 * Test for input form field handler.
 *
 * @group Cohesionz
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel\TextareaHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\TextareaHandler
 */
class TextareaHandlerTest extends UnitTestCase {

  const JSON_VALUES = '{"canvas":[{"uid":"cpt_textarea_component","type":"component","title":"Textarea component","enabled":true,"category":"category-10","componentId":"cpt_textarea_component","uuid":"e9ece08f-2c9c-45b0-b761-e8bf83aaf9ae","parentUid":"root","status":{},"children":[]}],"mapper":{"e9ece08f-2c9c-45b0-b761-e8bf83aaf9ae":{}},"model":{"e9ece08f-2c9c-45b0-b761-e8bf83aaf9ae":{"b2c96013-a09c-46b8-a0f9-bb18afd827b4":"Some plain text.","settings":{"title":"Textarea component"}}},"previewModel":{"e9ece08f-2c9c-45b0-b761-e8bf83aaf9ae":{}},"variableFields":{"e9ece08f-2c9c-45b0-b761-e8bf83aaf9ae":[]},"meta":{}}';
  const COMPONENT = [
    'uuid' => '2768d34f-87aa-482f-b26c-084f8a873b92',
    'langcode' => 'en',
    'status' => true,
    'label' => 'Textarea component',
    'id' => 'cpt_textarea_component',
    'json_values' => '{"canvas":[],"componentForm":[{"type":"form-field","uid":"form-textarea","title":"Plain text area","translate":true,"status":{"collapsed":false},"uuid":"b2c96013-a09c-46b8-a0f9-bb18afd827b4","parentUid":"root","children":[]}],"mapper":{"b2c96013-a09c-46b8-a0f9-bb18afd827b4":{}},"model":{"b2c96013-a09c-46b8-a0f9-bb18afd827b4":{"settings":{"type":"cohTextarea","title":"Plain text area","schema":{"type":"string","escape":true},"machineName":"plain-text-area","tooltipPlacement":"auto right"}}},"previewModel":{"b2c96013-a09c-46b8-a0f9-bb18afd827b4":{}},"variableFields":{"b2c96013-a09c-46b8-a0f9-bb18afd827b4":[]},"disabledNodes":[],"meta":{}}',
    'json_mapper' => '{}',
    'category' => 'cpt_cat_test',
  ];
  const RESULT = [
    'type' => 'form-field',
    'id' => 'b2c96013-a09c-46b8-a0f9-bb18afd827b4',
    'data' => [
      'uid' => 'form-textarea',
      'title' => 'Plain text area',
      'value' => 'Some plain text.',
    ],
    'machine_name' => 'plain-text-area',
  ];

  /**
   * TextareaHandler.
   *
   * @var \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\TextareaHandler
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


    $this->handler = new TextareaHandler($moduleHandlerMock);

    parent::setUp();
  }


  /**
   * @group Cohesionz
   *
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\TextareaHandler::getStaticSchema
   */
  public function testGetStaticSchema() {
    $this->assertIsArray($this->handler->getStaticSchema());
  }

  /**
   * @group Cohesionz
   *
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\TextareaHandler::getData
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
