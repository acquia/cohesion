<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel;

use Drupal\sitestudio_data_transformers\Handlers\ElementLevel\PictureHandler;

/**
 * Test for picture element handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel\PictureHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\PictureHandler
 */
class PictureHandlerTest extends ElementHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"type":"item","uid":"picture","title":"Picture","selected":false,"status":{"collapsed":true,"isopen":false},"uuid":"8cf0c20f-a481-4472-ac6c-4f9c90454bfe","parentUid":"root","children":[]}],"mapper":{"8cf0c20f-a481-4472-ac6c-4f9c90454bfe":{"settings":{"formDefinition":[{"formKey":"picture-settings","children":[{"formKey":"picture-info","activeFields":[{"name":"title","active":true},{"name":"alt","active":true},{"name":"lazyload","active":true}]},{"formKey":"picture-images","activeFields":[{"name":"image","active":true},{"name":"imageStyle","active":true}],"breakpoints":[{"iconName":"television","name":"xl"},{"name":"lg"}]},{"formKey":"picture-style","activeFields":[{"name":"customStyle","active":true}]}]}],"selectorType":"topLevel","form":null,"items":[],"title":"Default"}}},"model":{"8cf0c20f-a481-4472-ac6c-4f9c90454bfe":{"settings":{"title":"Picture","customStyle":[{"customStyle":""}],"lazyload":false,"styles":{"xl":{"displaySize":"coh-image-responsive","pictureImagesArray":[{"imageStyle":"","image":"[media-reference:file:3964309c-facc-4090-929a-d81890d29108]"}]},"lg":{"displaySize":"coh-image-responsive","pictureImagesArray":[{"imageStyle":"","image":"[media-reference:file:fe2369de-d627-4cc9-ac11-f9b131c638a3]"}]}},"attributes":{"title":"image title","alt":"image alt"}}}},"previewModel":{"8cf0c20f-a481-4472-ac6c-4f9c90454bfe":{}},"variableFields":{"8cf0c20f-a481-4472-ac6c-4f9c90454bfe":[]},"meta":{}}';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->handler = new PictureHandler($this->moduleHandler);
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\Picture::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'picture',
      'id' => '8cf0c20f-a481-4472-ac6c-4f9c90454bfe',
      'data' => [
        'title' => 'Picture',
        'images' => json_decode('{"xl":{"displaySize":"coh-image-responsive","pictureImagesArray":[{"imageStyle":"","image":"[media-reference:file:3964309c-facc-4090-929a-d81890d29108]"}]},"lg":{"displaySize":"coh-image-responsive","pictureImagesArray":[{"imageStyle":"","image":"[media-reference:file:fe2369de-d627-4cc9-ac11-f9b131c638a3]"}]}}'),
        'attributes' => json_decode('{"title":"image title","alt":"image alt"}'),
      ],
    ];
    parent::testGetData();
  }


}
