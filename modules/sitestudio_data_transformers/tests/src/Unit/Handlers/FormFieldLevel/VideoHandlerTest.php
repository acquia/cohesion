<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel;

use Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\VideoHandler;

/**
 * Test for video form field handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel\VideoHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\VideoHandler
 */
class VideoHandlerTest extends FormFieldHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"uid":"cpt_video_component","type":"component","title":"Video component","enabled":true,"category":"category-10","componentId":"cpt_video_component","uuid":"20cddc11-7348-4fb4-ad75-8ba08ecd7e3c","parentUid":"root","status":{},"children":[]}],"mapper":{"20cddc11-7348-4fb4-ad75-8ba08ecd7e3c":{}},"model":{"20cddc11-7348-4fb4-ad75-8ba08ecd7e3c":{"0daf3944-efc1-47d6-9960-35e357a66b1c":"https:\/\/vimeo.com\/michaelkoenig\/earth","settings":{"title":"Video"}}},"previewModel":{"20cddc11-7348-4fb4-ad75-8ba08ecd7e3c":{}},"variableFields":{"20cddc11-7348-4fb4-ad75-8ba08ecd7e3c":[]},"meta":{}}';
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

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->handler = new VideoHandler($this->moduleHandler);
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\VideoHandler::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'form-field',
      'id' => '0daf3944-efc1-47d6-9960-35e357a66b1c',
      'data' => [
        'uid' => 'form-video-embed',
        'title' => 'Video URL',
        'value' => 'https://vimeo.com/michaelkoenig/earth',
      ],
      'machine_name' => 'video-url',
    ];
    parent::testGetData();
  }


}
