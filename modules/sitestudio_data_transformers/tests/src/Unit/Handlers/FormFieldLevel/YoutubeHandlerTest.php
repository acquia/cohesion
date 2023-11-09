<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel;

use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\cohesion_elements\Entity\Component;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\YoutubeHandler;
use Drupal\Tests\UnitTestCase;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use Shaper\Validator\JsonSchemaValidator;

/**
 * Test for input form field handler.
 *
 * @group Cohesionz
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel\YoutubeHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\YoutubeHandler
 */
class YoutubeHandlerTest extends UnitTestCase {

  const JSON_VALUES = '{"canvas":[{"uid":"cpt_youtube_component","type":"component","title":"Youtube component","enabled":true,"category":"category-10","componentId":"cpt_youtube_component","uuid":"b3f336a5-adee-4c0b-aeee-8a336ecefa70","parentUid":"root","status":{},"children":[]}],"mapper":{"b3f336a5-adee-4c0b-aeee-8a336ecefa70":{}},"model":{"b3f336a5-adee-4c0b-aeee-8a336ecefa70":{"b9a1e0eb-2f4a-4d7f-849f-415e270c349a":"https:\/\/www.youtube.com\/watch?v=dQw4w9WgXcQ","settings":{"title":"Youtube component"}}},"previewModel":{"b3f336a5-adee-4c0b-aeee-8a336ecefa70":{}},"variableFields":{"b3f336a5-adee-4c0b-aeee-8a336ecefa70":[]},"meta":{}}';
  const COMPONENT = [
    'uuid' => 'c7987e6f-0be7-4567-8f7d-42c298c1b049',
    'langcode' => 'en',
    'status' => true,
    'label' => 'Youtube component',
    'id' => 'cpt_video_component',
    'json_values' => '{"canvas":[],"componentForm":[{"type":"form-field","uid":"form-youtube-embed","title":"Youtube URL","status":{"collapsed":false},"uuid":"b9a1e0eb-2f4a-4d7f-849f-415e270c349a","parentUid":"root","children":[]}],"mapper":{"b9a1e0eb-2f4a-4d7f-849f-415e270c349a":{}},"model":{"b9a1e0eb-2f4a-4d7f-849f-415e270c349a":{"settings":{"type":"cohYoutubeEmbed","title":"Youtube URL","schema":{"type":"string","cohValidate":["youTubeUrl"]},"machineName":"youtube-url","tooltipPlacement":"auto right"}}},"previewModel":{"b9a1e0eb-2f4a-4d7f-849f-415e270c349a":{}},"variableFields":{"b9a1e0eb-2f4a-4d7f-849f-415e270c349a":[]},"disabledNodes":[],"meta":{}}',
    'json_mapper' => '{}',
    'category' => 'cpt_cat_test',
  ];
  const RESULT = [
    'type' => 'form-field',
    'id' => 'b9a1e0eb-2f4a-4d7f-849f-415e270c349a',
    'data' => [
      'uid' => 'form-youtube-embed',
      'title' => 'Youtube URL',
      'value' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    ],
    'machine_name' => 'youtube-url',
  ];

  /**
   * YoutubeHandler.
   *
   * @var \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\YoutubeHandler
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


    $this->handler = new YoutubeHandler($moduleHandlerMock);

    parent::setUp();
  }


  /**
   * @group Cohesionz
   *
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\YoutubeHandler::getStaticSchema
   */
  public function testGetStaticSchema() {
    $this->assertIsArray($this->handler->getStaticSchema());
  }

  /**
   * @group Cohesionz
   *
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\YoutubeHandler::getData
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
