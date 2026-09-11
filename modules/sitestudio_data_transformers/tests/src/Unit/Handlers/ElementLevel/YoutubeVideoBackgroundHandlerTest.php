<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel;

use Drupal\cohesion\ImageBrowserUpdateManager;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\media\MediaInterface;
use Drupal\sitestudio_data_transformers\Handlers\ElementLevel\YoutubeVideoBackgroundHandler;

/**
 * Test for youtube video background element handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel\YoutubeVideoBackgroundHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\YoutubeVideoBackgroundHandler
 */
class YoutubeVideoBackgroundHandlerTest extends ElementHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"type":"container","uid":"youtube-video-background","title":"Youtube video background","status":{"collapsed":false},"iconColor":"media","uuid":"d1f322b5-64be-4c6a-a50d-d60b102df068","parentUid":"root","children":[]}],"mapper":{"d1f322b5-64be-4c6a-a50d-d60b102df068":{"settings":{"formDefinition":[{"formKey":"youtube-video-background-settings","children":[{"formKey":"youtube-video-background-video","breakpoints":[],"activeFields":[{"name":"url","active":true}]},{"formKey":"youtube-video-background-mobile-fallback","breakpoints":[],"activeFields":[{"name":"mobileImage","active":true}]},{"formKey":"youtube-video-background-position-ratio","breakpoints":[],"activeFields":[{"name":"scaleFrom","active":true}]},{"formKey":"youtube-video-background-video-controls","breakpoints":[],"activeFields":[{"name":"autoplay","active":true},{"name":"loop","active":true},{"name":"pauseWhenHidden","active":true}]},{"formKey":"youtube-video-background-audio-controls","breakpoints":[],"activeFields":[{"name":"muteAudio","active":true}]}]}],"selectorType":"topLevel","form":null,"items":[],"title":"Default"}}},"model":{"d1f322b5-64be-4c6a-a50d-d60b102df068":{"settings":{"size":{"scaleFrom":"center, center"},"videoControls":{"autoplay":true,"loop":true,"pauseWhenHidden":true},"audioControls":{"muteAudio":true},"title":"Youtube video background","url":"https:\/\/www.youtube.com\/watch?v=I95hSyocMlg&ab_channel=DrupalAssociation","mobileImage":"[media-reference:file:fe2369de-d627-4cc9-ac11-f9b131c638a3]"}}},"previewModel":{"d1f322b5-64be-4c6a-a50d-d60b102df068":{}},"variableFields":{"d1f322b5-64be-4c6a-a50d-d60b102df068":[]},"meta":{}}';

  protected function setUp(): void {
    parent::setUp();
    $mediaMock = $this->getMockBuilder(MediaInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $imageBrowserUpdateManager = $this->getMockBuilder(ImageBrowserUpdateManager::class)
      ->disableOriginalConstructor()
      ->getMock();
    $imageBrowserUpdateManager->expects($this->any())
      ->method('decodeToken')
      ->willReturn([
        'path' => '/fake/path/to/video-fallback-image.jpg',
        'entity' => $mediaMock,
        'label' => 'Image label',
      ]);

    $filUrlGeneratorMock = $this->getMockBuilder(FileUrlGeneratorInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $filUrlGeneratorMock->expects($this->any())
      ->method('generateAbsoluteString')
      ->willReturn('https://path.to/video-fallback-image.jpg');

    $this->handler = new YoutubeVideoBackgroundHandler(
      $this->moduleHandler,
      $imageBrowserUpdateManager,
      $filUrlGeneratorMock
    );
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\YoutubeVideoBackground::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'youtube-video-background',
      'id' => 'd1f322b5-64be-4c6a-a50d-d60b102df068',
      'data' => [
        'title' => 'Youtube video background',
        'videoUrl' => 'https://www.youtube.com/watch?v=I95hSyocMlg&ab_channel=DrupalAssociation',
        'mobileFallbackImage' => 'https://path.to/video-fallback-image.jpg',
      ],
    ];
    parent::testGetData();
  }


}
