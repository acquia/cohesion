<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel;

use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\cohesion_elements\Entity\Component;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\VideoHandler;
use Drupal\Tests\UnitTestCase;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use Shaper\Validator\JsonSchemaValidator;

/**
 * Test for input form field handler.
 *
 * @group Cohesionz
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel\VideoHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\VideoHandler
 */
class VideoHandlerTest extends UnitTestCase {

  const JSON_VALUES = '{"canvas":[{"uid":"cpt_video_component","type":"component","title":"Video component","enabled":true,"category":"category-10","componentId":"cpt_video_component","uuid":"20cddc11-7348-4fb4-ad75-8ba08ecd7e3c","parentUid":"root","status":{},"children":[]}],"mapper":{"20cddc11-7348-4fb4-ad75-8ba08ecd7e3c":{}},"model":{"20cddc11-7348-4fb4-ad75-8ba08ecd7e3c":{"0daf3944-efc1-47d6-9960-35e357a66b1c":"https:\/\/vimeo.com\/michaelkoenig\/earth","settings":{"title":"Video component"}}},"previewModel":{"20cddc11-7348-4fb4-ad75-8ba08ecd7e3c":{}},"variableFields":{"20cddc11-7348-4fb4-ad75-8ba08ecd7e3c":[]},"meta":{}}';
  const COMPONENT = [
    'uuid' => 'bc8e65ed-9b1e-4dfc-8ec4-fd09576f33d0',
    'langcode' => 'en',
    'status' => true,
    'label' => 'Video component',
    'id' => 'cpt_video_component',
    'json_values' => '{"canvas":[],"componentForm":[{"type":"form-field","uid":"form-video-embed","title":"Video","status":{"collapsed":false},"uuid":"0daf3944-efc1-47d6-9960-35e357a66b1c","parentUid":"root","children":[]}],"mapper":{"0daf3944-efc1-47d6-9960-35e357a66b1c":{}},"model":{"0daf3944-efc1-47d6-9960-35e357a66b1c":{"settings":{"type":"cohMediaEmbed","options":{"noPlugin":true},"title":"Video URL","schema":{"type":"string"},"machineName":"video-url","tooltipPlacement":"auto right"},"model":{"value":""}}},"previewModel":{"0daf3944-efc1-47d6-9960-35e357a66b1c":{}},"variableFields":{"0daf3944-efc1-47d6-9960-35e357a66b1c":[]},"disabledNodes":[],"meta":{}}',
    'json_mapper' => '{}',
    'category' => 'cpt_cat_test',
  ];
  const RESULT = [
    'type' => 'form-field',
    'id' => '0daf3944-efc1-47d6-9960-35e357a66b1c',
    'data' => [
      'uid' => 'form-video-embed',
      'title' => 'Video',
      'value' => 'https://vimeo.com/michaelkoenig/earth',
    ],
    'machine_name' => 'video-url',
  ];

  /**
   * VideoHandler.
   *
   * @var \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\VideoHandler
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


    $this->handler = new VideoHandler($moduleHandlerMock);

    parent::setUp();
  }


  /**
   * @group Cohesionz
   *
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\VideoHandler::getStaticSchema
   */
  public function testGetStaticSchema() {
    $this->assertIsArray($this->handler->getStaticSchema());
  }

  /**
   * @group Cohesionz
   *
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\VideoHandler::getData
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
