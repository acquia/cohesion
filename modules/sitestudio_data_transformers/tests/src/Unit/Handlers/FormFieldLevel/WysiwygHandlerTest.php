<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel;

use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\cohesion_elements\Entity\Component;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\WysiwygHandler;
use Drupal\Tests\UnitTestCase;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use Shaper\Validator\JsonSchemaValidator;

/**
 * Test for input form field handler.
 *
 * @group Cohesionz
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel\WysiwygHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\WysiwygHandler
 */
class WysiwygHandlerTest extends UnitTestCase {

  const JSON_VALUES = '{"canvas":[{"uid":"cpt_wysiwyg_component","type":"component","title":"Wysiwyg component","enabled":true,"category":"category-10","componentId":"cpt_wysiwyg_component","uuid":"690192aa-a9e1-425c-9faa-f9acb17420be","parentUid":"root","status":{},"children":[]}],"mapper":{"690192aa-a9e1-425c-9faa-f9acb17420be":{}},"model":{"690192aa-a9e1-425c-9faa-f9acb17420be":{"fd4282cf-10c9-47b9-afa8-5422944b00b7":{"textFormat":"cohesion","text":"<p>A very long string value in WYSIWYG editor.<\/p>"},"settings":{"title":"Wysiwyg component"}}},"previewModel":{"690192aa-a9e1-425c-9faa-f9acb17420be":{}},"variableFields":{"690192aa-a9e1-425c-9faa-f9acb17420be":[]},"meta":{}}';
  const COMPONENT = [
    'uuid' => '2c1e2da5-658d-4447-a48e-29b0274ef8d7',
    'langcode' => 'en',
    'status' => true,
    'label' => 'Wysiwyg component',
    'id' => 'cpt_wysiwyg_component',
    'json_values' => '{"canvas":[],"componentForm":[{"type":"form-field","uid":"form-wysiwyg","title":"WYSIWYG","translate":true,"status":{"collapsed":false},"uuid":"fd4282cf-10c9-47b9-afa8-5422944b00b7","parentUid":"root","children":[]}],"mapper":{"fd4282cf-10c9-47b9-afa8-5422944b00b7":{}},"model":{"fd4282cf-10c9-47b9-afa8-5422944b00b7":{"settings":{"type":"cohWysiwyg","title":"WYSIWYG","schema":{"type":"object"},"machineName":"wysiwyg","tooltipPlacement":"auto right"},"model":{"value":{"textFormat":"cohesion","text":""}}}},"previewModel":{"fd4282cf-10c9-47b9-afa8-5422944b00b7":{}},"variableFields":{"fd4282cf-10c9-47b9-afa8-5422944b00b7":[]},"disabledNodes":[],"meta":{}}',
    'json_mapper' => '{}',
    'category' => 'cpt_cat_test',
  ];

  /**
   * WysiwygHandler.
   *
   * @var \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\WysiwygHandler
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


    $this->handler = new WysiwygHandler($moduleHandlerMock);

    parent::setUp();
  }


  /**
   * @group Cohesionz
   *
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\WysiwygHandler::getStaticSchema
   */
  public function testGetStaticSchema() {
    $this->assertIsArray($this->handler->getStaticSchema());
  }

  /**
   * @group Cohesionz
   *
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\WysiwygHandler::getData
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
      'id' => 'fd4282cf-10c9-47b9-afa8-5422944b00b7',
      'data' => [
        'uid' => 'form-wysiwyg',
        'title' => 'WYSIWYG',
        'value' => json_decode('{"textFormat": "cohesion", "text": "<p>A very long string value in WYSIWYG editor.</p>"}'),
      ],
      'machine_name' => 'wysiwyg',
    ];

    $this->assertEquals($result, $field_data);
    $validator = new JsonSchemaValidator($this->handler->getStaticSchema(), new Validator(), Constraint::CHECK_MODE_TYPE_CAST);
    $this->assertTrue($validator->isValid($field_data));
  }


}
