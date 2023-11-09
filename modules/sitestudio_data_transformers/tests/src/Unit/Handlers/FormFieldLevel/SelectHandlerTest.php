<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel;

use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\cohesion_elements\Entity\Component;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\SelectHandler;
use Drupal\Tests\UnitTestCase;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use Shaper\Validator\JsonSchemaValidator;

/**
 * Test for input form field handler.
 *
 * @group Cohesionz
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel\SelectHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\SelectHandler
 */
class SelectHandlerTest extends UnitTestCase {

  const JSON_VALUES = '{"canvas":[{"uid":"cpt_select_component","type":"component","title":"Select component","enabled":true,"category":"category-10","componentId":"cpt_select_component","uuid":"548667b3-513b-4007-89d3-42a44369465c","parentUid":"root","status":{},"children":[]}],"mapper":{"548667b3-513b-4007-89d3-42a44369465c":{}},"model":{"548667b3-513b-4007-89d3-42a44369465c":{"20cb3bae-243d-4fcb-b355-0b40b31180a3":"three","settings":{"title":"Select component"}}},"previewModel":{"548667b3-513b-4007-89d3-42a44369465c":{}},"variableFields":{"548667b3-513b-4007-89d3-42a44369465c":[]},"meta":{}}';
  const COMPONENT = [
    'uuid' => 'ee1bb744-a89a-47f7-ab25-f15d7048a44d',
    'langcode' => 'en',
    'status' => true,
    'label' => 'Select component',
    'id' => 'cpt_select_component',
    'json_values' => '{"canvas":[],"componentForm":[{"type":"form-field","uid":"form-select","title":"Select","translate":false,"status":{"collapsed":false},"uuid":"20cb3bae-243d-4fcb-b355-0b40b31180a3","parentUid":"root","children":[]}],"mapper":{"20cb3bae-243d-4fcb-b355-0b40b31180a3":{}},"model":{"20cb3bae-243d-4fcb-b355-0b40b31180a3":{"settings":{"type":"cohSelect","title":"Select","selectType":"custom","machineName":"select","options":[{"label":"One","value":"one"},{"label":"Two","value":"two"},{"label":"Three","value":"three"}],"tooltipPlacement":"auto right"},"model":{"value":""}}},"previewModel":{"20cb3bae-243d-4fcb-b355-0b40b31180a3":{}},"variableFields":{"20cb3bae-243d-4fcb-b355-0b40b31180a3":[]},"disabledNodes":[],"meta":{}}',
    'json_mapper' => '{}',
    'category' => 'cpt_cat_test',
  ];
  const RESULT = [
    'type' => 'form-field',
    'id' => '20cb3bae-243d-4fcb-b355-0b40b31180a3',
    'data' => [
      'uid' => 'form-select',
      'title' => 'Select',
      'value' => 'three',
    ],
    'machine_name' => 'select',
  ];

  /**
   * SelectHandler.
   *
   * @var \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\SelectHandler
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


    $this->handler = new SelectHandler($moduleHandlerMock);

    parent::setUp();
  }


  /**
   * @group Cohesionz
   *
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\SelectHandler::getStaticSchema
   */
  public function testGetStaticSchema() {
    $this->assertIsArray($this->handler->getStaticSchema());
  }

  /**
   * @group Cohesionz
   *
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\SelectHandler::getData
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
