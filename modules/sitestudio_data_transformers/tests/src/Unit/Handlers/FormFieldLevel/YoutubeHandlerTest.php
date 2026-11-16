<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel;

use Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\YoutubeHandler;

/**
 * Test for YouTube form field handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel\YoutubeHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\YoutubeHandler
 */
class YoutubeHandlerTest extends FormFieldHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"uid":"cpt_youtube_component","type":"component","title":"Youtube component","enabled":true,"category":"category-10","componentId":"cpt_youtube_component","uuid":"b3f336a5-adee-4c0b-aeee-8a336ecefa70","parentUid":"root","status":{},"children":[]}],"mapper":{"b3f336a5-adee-4c0b-aeee-8a336ecefa70":{}},"model":{"b3f336a5-adee-4c0b-aeee-8a336ecefa70":{"b9a1e0eb-2f4a-4d7f-849f-415e270c349a":"https:\/\/www.youtube.com\/watch?v=dQw4w9WgXcQ","settings":{"title":"Youtube URL"}}},"previewModel":{"b3f336a5-adee-4c0b-aeee-8a336ecefa70":{}},"variableFields":{"b3f336a5-adee-4c0b-aeee-8a336ecefa70":[]},"meta":{}}';
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

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->handler = new YoutubeHandler($this->moduleHandler);
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\YoutubeHandler::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'form-field',
      'id' => 'b9a1e0eb-2f4a-4d7f-849f-415e270c349a',
      'machine_name' => 'youtube-url',
      'data' => [
        'uid' => 'form-youtube-embed',
        'title' => 'Youtube URL',
        'value' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
      ],
    ];
    parent::testGetData();
  }


}
