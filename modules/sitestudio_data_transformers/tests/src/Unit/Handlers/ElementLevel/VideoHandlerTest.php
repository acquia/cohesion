<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel;

use Drupal\cohesion\ImageBrowserUpdateManager;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\media\MediaInterface;
use Drupal\sitestudio_data_transformers\Handlers\ElementLevel\VideoHandler;

/**
 * Test for video element handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel\VideoHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\VideoHandler
 */
class VideoHandlerTest extends ElementHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"type":"item","uid":"video","title":"Video","selected":false,"status":{"collapsed":true,"isopen":false},"uuid":"87504942-1fd5-4e69-8847-a7a4462d227e","parentUid":"root","children":[]}],"mapper":{"87504942-1fd5-4e69-8847-a7a4462d227e":{"settings":{"formDefinition":[{"formKey":"video-settings","children":[{"formKey":"video","breakpoints":[],"activeFields":[{"name":"videoUrl","active":true}]},{"formKey":"video-poster","breakpoints":[],"activeFields":[{"name":"videoPoster","active":true},{"name":"videoPosterPaused","active":true},{"name":"videoPosterEnd","active":true}]},{"formKey":"video-behaviour","breakpoints":[],"activeFields":[{"name":"videoPreload","active":true},{"name":"videoAutoplay","active":true},{"name":"videoLoop","active":true},{"name":"videoMute","active":true},{"name":"videoRewind","active":true},{"name":"videoClickPlaypause","active":true}]},{"formKey":"video-controls","breakpoints":[],"activeFields":[{"name":"videoPlaypause","active":true},{"name":"videoCurrent","active":true},{"name":"videoProgress","active":true},{"name":"videoDuration","active":true},{"name":"videoVolume","active":true},{"name":"videoFullscreen","active":true},{"name":"videoControlsLoad","active":true},{"name":"videoControlsPause","active":true}]},{"formKey":"video-style","breakpoints":[],"activeFields":[{"name":"customStyle","active":true}]}]}],"selectorType":"topLevel","form":null,"items":[],"title":"Default"}}},"model":{"87504942-1fd5-4e69-8847-a7a4462d227e":{"settings":{"title":"Video","customStyle":[{"customStyle":""}],"videoUrl":"https:\/\/www.youtube.com\/watch?v=I95hSyocMlg&ab_channel=DrupalAssociation","videoPreload":"none","videoAutoplay":false,"videoLoop":false,"videoMute":false,"videoRewind":true,"videoClickPlaypause":true,"videoPauseOthers":true,"videoPlayOnHover":false,"videoShowControls":false,"videoShowControlsLoad":true,"videoShowControlsPause":true,"videoShowPlayCenter":true,"videoShowPlayPause":true,"videoShowCurrent":true,"videoShowProgress":true,"videoShowDuration":true,"videoShowVolume":true,"videoShowFullscreen":true,"videoPoster":"[media-reference:file:fe2369de-d627-4cc9-ac11-f9b131c638a3]","videoPosterPaused":false,"videoPosterEnd":false,"videoPosterStyle":""}}},"previewModel":{"87504942-1fd5-4e69-8847-a7a4462d227e":{}},"variableFields":{"87504942-1fd5-4e69-8847-a7a4462d227e":[]},"meta":{}}';
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
        'path' => '/fake/path/to/image.jpg',
        'entity' => $mediaMock,
        'label' => 'Image label',
      ]);

    $filUrlGeneratorMock = $this->getMockBuilder(FileUrlGeneratorInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $filUrlGeneratorMock->expects($this->any())
      ->method('generateAbsoluteString')
      ->willReturn('https://path.to/image.jpg');

    $this->handler = new VideoHandler(
      $this->moduleHandler,
      $imageBrowserUpdateManager,
      $filUrlGeneratorMock
    );
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\Video::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'video',
      'id' => '87504942-1fd5-4e69-8847-a7a4462d227e',
      'data' => [
        'title' => 'Video',
        'videoUrl' => 'https://www.youtube.com/watch?v=I95hSyocMlg&ab_channel=DrupalAssociation',
        'videoPoster' => 'https://path.to/image.jpg',
      ],
    ];
    parent::testGetData();
  }


}
