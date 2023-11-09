<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel;

use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\cohesion_elements\Entity\Component;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\LinkHandler;
use Drupal\Tests\UnitTestCase;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use Shaper\Validator\JsonSchemaValidator;

/**
 * Test for input form field handler.
 *
 * @group Cohesionz
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel\LinkHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\LinkHandler
 */
class LinkHandlerTest extends UnitTestCase {

  const JSON_VALUES = '{"canvas":[{"uid":"cpt_link_component","type":"component","title":"Link component","enabled":true,"category":"category-10","componentId":"cpt_link_component","uuid":"de6add66-d75d-4453-9e6a-cee240ab9a34","parentUid":"root","status":{},"children":[]}],"mapper":{"de6add66-d75d-4453-9e6a-cee240ab9a34":{}},"model":{"de6add66-d75d-4453-9e6a-cee240ab9a34":{"settings":{"title":"Link component"},"5149b61c-8fd2-49d1-9127-713dc1683576":"node::1"}},"previewModel":{"de6add66-d75d-4453-9e6a-cee240ab9a34":{}},"variableFields":{"de6add66-d75d-4453-9e6a-cee240ab9a34":[]},"meta":{}}';
  const COMPONENT = [
    'uuid' => 'dfa3f357-c977-4644-a98d-27719086abc2',
    'langcode' => 'en',
    'status' => true,
    'label' => 'Link component',
    'id' => 'cpt_link_component',
    'json_values' => '{"canvas":[],"componentForm":[{"type":"form-field","uid":"form-link","title":"Link to page","translate":true,"status":{"collapsed":false},"uuid":"5149b61c-8fd2-49d1-9127-713dc1683576","parentUid":"root","children":[]}],"mapper":{"5149b61c-8fd2-49d1-9127-713dc1683576":{}},"model":{"5149b61c-8fd2-49d1-9127-713dc1683576":{"settings":{"isStyle":true,"type":"cohTypeahead","key":"linkToPage","title":"Link to page","placeholder":"Type page name","labelProperty":"name","valueProperty":"id","typeaheadEditable":true,"endpoint":"\/cohesionapi\/link-autocomplete?q=","schema":{"type":"string"},"machineName":"link-to-page","tooltipPlacement":"auto right","entityTypes":false}}},"previewModel":{"5149b61c-8fd2-49d1-9127-713dc1683576":{}},"variableFields":{"5149b61c-8fd2-49d1-9127-713dc1683576":[]},"disabledNodes":[],"meta":{}}',
    'json_mapper' => '{}',
    'category' => 'cpt_cat_test',
  ];
  const RESULT = [
    'type' => 'form-field',
    'id' => '5149b61c-8fd2-49d1-9127-713dc1683576',
    'data' => [
      'uid' => 'form-link',
      'title' => 'Link to page',
      'value' => 'node::1',
    ],
    'machine_name' => 'link-to-page',
  ];

  /**
   * LinkHandler.
   *
   * @var \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\LinkHandler
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


    $this->handler = new LinkHandler($moduleHandlerMock);

    parent::setUp();
  }


  /**
   * @group Cohesionz
   *
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\LinkHandler::getStaticSchema
   */
  public function testGetStaticSchema() {
    $this->assertIsArray($this->handler->getStaticSchema());
  }

  /**
   * @group Cohesionz
   *
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\LinkHandler::getData
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
