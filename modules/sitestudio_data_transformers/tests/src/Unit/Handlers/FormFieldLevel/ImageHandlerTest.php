<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel;

use Drupal\cohesion\ImageBrowserUpdateManager;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\media\MediaInterface;
use Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\ImageHandler;

/**
 * Test for image form field handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel\ImageHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\ImageHandler
 */
class ImageHandlerTest extends FormFieldHandlerTestBase {

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
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\ImageHandler::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'form-field',
      'id' => 'b1aa6a23-6268-422f-bd1e-0738b2bc412e',
      'data' => [
        'uid' => 'form-image',
        'title' => 'Image uploader',
        'value' => 'https://path.to/image.jpg',
      ],
      'machine_name' => 'image-uploader',
    ];
    parent::testGetData();
  }


}
