<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel;

use Drupal\cohesion\ImageBrowserUpdateManager;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\media\MediaInterface;
use Drupal\sitestudio_data_transformers\Handlers\ElementLevel\VideoBackgroundHandler;

/**
 * Test for video background element handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel\VideoBackgroundHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\VideoBackgroundHandler
 */
class VideoBackgroundHandlerTest extends ElementHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"type":"container","uid":"video-background","title":"Video background","status":{"collapsed":false},"uuid":"5f4f7f57-9ecb-46c7-9a8a-99a4fd236e28","parentUid":"root","children":[]}],"mapper":{"5f4f7f57-9ecb-46c7-9a8a-99a4fd236e28":{"settings":{"formDefinition":[{"formKey":"video-background-settings","children":[{"formKey":"video-background","breakpoints":[],"activeFields":[{"name":"videoBackgroundUrl","active":true}]},{"formKey":"video-background-poster","breakpoints":[],"activeFields":[{"name":"videoBackgroundPoster","active":true}]},{"formKey":"video-background-behaviour","breakpoints":[],"activeFields":[{"name":"videoBackgroundPauseHidden","active":true},{"name":"videoBackgroundDisableTouch","active":true}]},{"formKey":"video-background-position","breakpoints":[],"activeFields":[{"name":"videoBackgroundScale","active":true}]},{"formKey":"video-background-style","breakpoints":[],"activeFields":[{"name":"customStyle","active":true}]}]}],"selectorType":"topLevel","form":null,"items":[],"title":"Default"}}},"model":{"5f4f7f57-9ecb-46c7-9a8a-99a4fd236e28":{"settings":{"videoBackgroundPauseHidden":true,"videoBackgroundDisableTouch":false,"videoBackgroundScale":"coh-video-background-center","title":"Video background","customStyle":[{"customStyle":""}],"videoBackgroundUrl":"https:\/\/test-videos.co.uk\/vids\/bigbuckbunny\/mp4\/h264\/1080\/Big_Buck_Bunny_1080_10s_1MB.mp4","videoBackgroundPoster":"[media-reference:file:b67e2944-dd28-4ffd-a973-4a045083f8c7]","videoBackgroundPosterStyle":""}}},"previewModel":{"5f4f7f57-9ecb-46c7-9a8a-99a4fd236e28":{}},"variableFields":{"5f4f7f57-9ecb-46c7-9a8a-99a4fd236e28":[]},"meta":{}}';

  /**
   * {@inheritdoc}
   */
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
        'path' => '/fake/path/to/video-poster-image.jpg',
        'entity' => $mediaMock,
        'label' => 'Image label',
      ]);

    $filUrlGeneratorMock = $this->getMockBuilder(FileUrlGeneratorInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $filUrlGeneratorMock->expects($this->any())
      ->method('generateAbsoluteString')
      ->willReturn('https://path.to/video-poster-image.jpg');

    $this->handler = new VideoBackgroundHandler(
      $this->moduleHandler,
      $imageBrowserUpdateManager,
      $filUrlGeneratorMock
    );
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\VideoBackground::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'video-background',
      'id' => '5f4f7f57-9ecb-46c7-9a8a-99a4fd236e28',
      'data' => [
        'title' => 'Video background',
        'videoBackgroundUrl' => 'https://test-videos.co.uk/vids/bigbuckbunny/mp4/h264/1080/Big_Buck_Bunny_1080_10s_1MB.mp4',
        'videoBackgroundPoster' => "https://path.to/video-poster-image.jpg",
      ],
    ];
    parent::testGetData();
  }


}
