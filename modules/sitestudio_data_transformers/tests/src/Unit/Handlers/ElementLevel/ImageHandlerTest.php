<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel;

use Drupal\cohesion\ImageBrowserUpdateManager;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\media\MediaInterface;
use Drupal\sitestudio_data_transformers\Handlers\ElementLevel\ImageHandler;

/**
 * Test for image element handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel\ImageHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\ImageHandler
 */
class ImageHandlerTest extends ElementHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"type":"item","uid":"image","title":"Image","status":{"collapsed":true},"uuid":"557004c0-d6fb-440e-9e64-e290fb1ab4b9","parentUid":"root","children":[]}],"mapper":{"557004c0-d6fb-440e-9e64-e290fb1ab4b9":{"settings":{"formDefinition":[{"formKey":"image-settings","children":[{"formKey":"image-image","activeFields":[{"name":"image","active":true},{"name":"title","active":true},{"name":"alt","active":true},{"name":"lazyload","active":true}]},{"formKey":"image-size","activeFields":[{"name":"displaySize","active":true},{"name":"imageAlignment","active":true}],"breakpoints":[{"name":"xl"}]},{"formKey":"image-style","activeFields":[{"name":"customStyle","active":true}]}]}],"selectorType":"topLevel","form":null,"items":[],"title":"Default"}}},"model":{"557004c0-d6fb-440e-9e64-e290fb1ab4b9":{"settings":{"styles":{"xl":{"displaySize":"coh-image-responsive"}},"title":"Image","customStyle":[{"customStyle":""}],"lazyload":false,"imageStyle":"","image":"[media-reference:file:3964309c-facc-4090-929a-d81890d29108]","attributes":{"title":"image title","alt":"image alt"}}}},"previewModel":{"557004c0-d6fb-440e-9e64-e290fb1ab4b9":{}},"variableFields":{"557004c0-d6fb-440e-9e64-e290fb1ab4b9":[]},"meta":{}}';

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

    $this->handler = new ImageHandler(
      $this->moduleHandler,
      $imageBrowserUpdateManager,
      $filUrlGeneratorMock
    );
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\Image::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'image',
      'id' => '557004c0-d6fb-440e-9e64-e290fb1ab4b9',
      'data' => [
        'title' => 'Image',
        'image' => 'https://path.to/image.jpg',
        'attributes' => json_decode('{"title":"image title","alt":"image alt"}'),
      ],
    ];
    parent::testGetData();
  }


}
