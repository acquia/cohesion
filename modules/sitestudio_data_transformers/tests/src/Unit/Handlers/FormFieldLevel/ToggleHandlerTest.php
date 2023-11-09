<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel;

use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\cohesion_elements\Entity\Component;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\ToggleHandler;
use Drupal\Tests\UnitTestCase;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use Shaper\Validator\JsonSchemaValidator;

/**
 * Test for input form field handler.
 *
 * @group Cohesionz
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel\ToggleHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\ToggleHandler
 */
class ToggleHandlerTest extends UnitTestCase {

  const JSON_VALUES = '{"canvas":[{"uid":"cpt_toggle_component","type":"component","title":"Toggle component","enabled":true,"category":"category-10","componentId":"cpt_toggle_component","uuid":"a794d5b5-b3e8-41ba-b8f2-5ccf96fead1b","parentUid":"root","status":{},"children":[]}],"mapper":{"a794d5b5-b3e8-41ba-b8f2-5ccf96fead1b":{}},"model":{"a794d5b5-b3e8-41ba-b8f2-5ccf96fead1b":{"9a30a966-497e-49e2-8a3b-28b1f493ee8b":true,"settings":{"title":"Toggle component"}}},"previewModel":{"a794d5b5-b3e8-41ba-b8f2-5ccf96fead1b":{}},"variableFields":{"a794d5b5-b3e8-41ba-b8f2-5ccf96fead1b":[]},"meta":{}}';
  const COMPONENT = [
    'uuid' => 'c71cc59f-6b63-446b-b068-c0394682a24b',
    'langcode' => 'en',
    'status' => true,
    'label' => 'Toggle component',
    'id' => 'cpt_toggle_component',
    'json_values' => '{"canvas":[],"componentForm":[{"type":"form-field","uid":"form-checkbox-toggle","title":"Toggle","translate":true,"status":{"collapsed":false},"uuid":"9a30a966-497e-49e2-8a3b-28b1f493ee8b","parentUid":"root","children":[]}],"mapper":{"9a30a966-497e-49e2-8a3b-28b1f493ee8b":{}},"model":{"9a30a966-497e-49e2-8a3b-28b1f493ee8b":{"settings":{"type":"checkboxToggle","title":"Toggle","schema":{"type":"string"},"machineName":"toggle","toggleType":"boolean","tooltipPlacement":"auto right"},"model":{"value":false}}},"previewModel":{"9a30a966-497e-49e2-8a3b-28b1f493ee8b":{}},"variableFields":{"9a30a966-497e-49e2-8a3b-28b1f493ee8b":[]},"disabledNodes":[],"meta":{}}',
    'json_mapper' => '{}',
    'category' => 'cpt_cat_test',
  ];
  const RESULT = [
    'type' => 'form-field',
    'id' => '9a30a966-497e-49e2-8a3b-28b1f493ee8b',
    'data' => [
      'uid' => 'form-checkbox-toggle',
      'title' => 'Toggle',
      'value' => true,
    ],
    'machine_name' => 'toggle',
  ];

  /**
   * ToggleHandler.
   *
   * @var \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\ToggleHandler
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


    $this->handler = new ToggleHandler($moduleHandlerMock);

    parent::setUp();
  }


  /**
   * @group Cohesionz
   *
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\ToggleHandler::getStaticSchema
   */
  public function testGetStaticSchema() {
    $this->assertIsArray($this->handler->getStaticSchema());
  }

  /**
   * @group Cohesionz
   *
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\ToggleHandler::getData
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
