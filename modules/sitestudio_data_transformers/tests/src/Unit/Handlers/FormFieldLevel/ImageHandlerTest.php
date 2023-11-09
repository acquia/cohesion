<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel;

use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\cohesion_elements\Entity\Component;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\ImageHandler;
use Drupal\Tests\UnitTestCase;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use Shaper\Validator\JsonSchemaValidator;

/**
 * Test for input form field handler.
 *
 * @group Cohesionz
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel\ImageHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\ImageHandler
 */
class ImageHandlerTest extends UnitTestCase {

  const JSON_VALUES = '{"canvas":[{"uid":"cpt_image_component","type":"component","title":"Image component","enabled":true,"category":"category-10","componentId":"cpt_image_component","uuid":"f7751b1e-baf7-4683-8c45-47fb82120fb5","parentUid":"root","status":{},"children":[]}],"mapper":{"f7751b1e-baf7-4683-8c45-47fb82120fb5":{}},"model":{"f7751b1e-baf7-4683-8c45-47fb82120fb5":{"b1aa6a23-6268-422f-bd1e-0738b2bc412e":"[media-reference:media:94c0faac-28b5-4306-947b-60cd51e00b25]","settings":{"title":"Image component"}}},"previewModel":{"f7751b1e-baf7-4683-8c45-47fb82120fb5":{}},"variableFields":{"f7751b1e-baf7-4683-8c45-47fb82120fb5":[]},"meta":{}}';
  const COMPONENT = [
    'uuid' => '9cbf30ac-03d0-4101-8fe5-a0d11ca9c88a',
    'langcode' => 'en',
    'status' => true,
    'label' => 'Image component',
    'id' => 'cpt_image_component',
    'json_values' => '{"canvas":[],"componentForm":[{"type":"form-field","uid":"form-image","title":"Image uploader","status":{"collapsed":false},"uuid":"b1aa6a23-6268-422f-bd1e-0738b2bc412e","parentUid":"root","children":[]}],"mapper":{"b1aa6a23-6268-422f-bd1e-0738b2bc412e":{}},"model":{"b1aa6a23-6268-422f-bd1e-0738b2bc412e":{"settings":{"type":"cohFileBrowser","options":{"buttonText":"Select image","imageUploader":"false","allowedDescription":"Allowed: png, gif, jpg, jpeg \nMax file size: 2MB","removeLabel":"Remove"},"title":"Image uploader","isStyle":true,"defaultActive":true,"schema":{"type":"string"},"machineName":"image-uploader","tooltipPlacement":"auto right"}}},"previewModel":{"b1aa6a23-6268-422f-bd1e-0738b2bc412e":{}},"variableFields":{"b1aa6a23-6268-422f-bd1e-0738b2bc412e":[]},"disabledNodes":[],"meta":{}}',
    'json_mapper' => '{}',
    'category' => 'cpt_cat_test',
  ];
  const RESULT = [
    'type' => 'form-field',
    'id' => 'b1aa6a23-6268-422f-bd1e-0738b2bc412e',
    'data' => [
      'uid' => 'form-image',
      'title' => 'Image component',
      'value' => '[media-reference:media:94c0faac-28b5-4306-947b-60cd51e00b25]',
    ],
    'machine_name' => 'image-uploader',
  ];

  /**
   * ImageHandler.
   *
   * @var \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\ImageHandler
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


    $this->handler = new ImageHandler($moduleHandlerMock);

    parent::setUp();
  }


  /**
   * @group Cohesionz
   *
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\ImageHandler::getStaticSchema
   */
  public function testGetStaticSchema() {
    $this->assertIsArray($this->handler->getStaticSchema());
  }

  /**
   * @group Cohesionz
   *
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\ImageHandler::getData
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
