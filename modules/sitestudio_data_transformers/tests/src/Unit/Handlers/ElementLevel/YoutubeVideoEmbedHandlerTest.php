<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel;

use Drupal\sitestudio_data_transformers\Handlers\ElementLevel\YoutubeVideoEmbedHandler;

/**
 * Test for youtube video embed element handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel\YoutubeVideoEmbedHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\YoutubeVideoEmbedHandler
 */
class YoutubeVideoEmbedHandlerTest extends ElementHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"type":"item","uid":"youtube-video-embed","title":"Youtube video embed","status":{"collapsed":true},"uuid":"df6b4d55-2aa6-4f9b-8aaa-3b007a62699c","parentUid":"root","children":[]}],"mapper":{"df6b4d55-2aa6-4f9b-8aaa-3b007a62699c":{"settings":{"formDefinition":[{"formKey":"youtube-video-embed-settings","children":[{"formKey":"youtube-video-embed-video","breakpoints":[],"activeFields":[]},{"formKey":"youtube-video-embed-controls","breakpoints":[],"activeFields":[{"name":"autoplay","active":true},{"name":"loop","active":true},{"name":"rel","active":true},{"name":"controls","active":true},{"name":"fs","active":true},{"name":"showinfo","active":true},{"name":"annotations","active":true}]},{"formKey":"youtube-video-embed-aspect-ratio","breakpoints":[],"activeFields":[{"name":"aspectRatio","active":true}]}]}],"selectorType":"topLevel","form":null,"items":[],"title":"Default"}}},"model":{"df6b4d55-2aa6-4f9b-8aaa-3b007a62699c":{"settings":{"videoControls":{"autoplay":false,"loop":false,"rel":false,"controls":true,"fs":true,"showinfo":true,"annotations":false},"size":{"aspectRatio":"16by9"},"title":"Youtube video embed","url":"https:\/\/www.youtube.com\/watch?v=I95hSyocMlg&ab_channel=DrupalAssociation"}}},"previewModel":{"df6b4d55-2aa6-4f9b-8aaa-3b007a62699c":{}},"variableFields":{"df6b4d55-2aa6-4f9b-8aaa-3b007a62699c":[]},"meta":{}}';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->handler = new YoutubeVideoEmbedHandler($this->moduleHandler);
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\YoutubeVideoEmbed::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'youtube-video-embed',
      'id' => 'df6b4d55-2aa6-4f9b-8aaa-3b007a62699c',
      'data' => [
        'title' => 'Youtube video embed',
        'videoUrl' => 'https://www.youtube.com/watch?v=I95hSyocMlg&ab_channel=DrupalAssociation',
      ],
    ];
    parent::testGetData();
  }


}
