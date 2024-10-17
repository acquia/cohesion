<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\Plugin\EntityUpdate\_0007EntityUpdate;

/**
 * Class MockUpdateEntity.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class _0007MockUpdateEntity extends EntityMockBase implements EntityJsonValuesInterface {

}

/**
 * @group Cohesion
 */
class _0007EntityUpdateUnitTest extends EntityUpdateUnitTestCase {

  /**
   * @var unit_0005EntityUpdateMock*/
  protected $unit;

  private $fixture_layout = '{ "model": { "6d51ccd3-62d4-41f2-b146-2b8523ec251d": { "context-visibility": { "contextVisibility": { "condition": "ALL" } }, "styles": { "settings": { "element": "picture" } }, "settings": { "attributes": {}, "styles": { "xl": { "image": "[media-reference:file:188aadaa-9627-429a-b786-9f2171674467]", "displaySize": "coh-image-responsive", "imageStyle": "", "imageAlignment": "coh-image-align-left" }, "lg": { "image": "[media-reference:file:188aadaa-9627-429a-b786-9f2171674467]", "displaySize": "coh-image", "imageStyle": "", "imageAlignment": "coh-image-align-centre" }, "md": { "image": "[media-reference:file:188aadaa-9627-429a-b786-9f2171674467]", "displaySize": "coh-image-responsive", "imageStyle": "crop_thumbnail", "imageAlignment": "coh-image-align-left" }, "sm": { "image": "[media-reference:file:188aadaa-9627-429a-b786-9f2171674467]", "displaySize": "coh-image", "imageStyle": "large_landscape", "imageAlignment": "coh-image-align-right" }, "ps": { "displaySize": "coh-image-responsive", "imageStyle": "", "imageAlignment": "coh-image-align-left" }, "xs": { "displaySize": "coh-image", "imageStyle": "", "imageAlignment": "coh-image-float-right" } }, "customStyle": [ { "customStyle": "" } ] }, "isVariableMode": false }, "b12eda0b-6d0d-4e9c-a09c-e4f1ece7f90c": { "context-visibility": { "contextVisibility": { "condition": "ALL" } }, "styles": { "settings": { "element": "picture" } }, "settings": { "attributes": {}, "styles": { "xl": { "image": "", "displaySize": "coh-image-responsive", "imageStyle": "crop_thumbnail", "imageAlignment": "coh-image-align-left" }, "lg": { "image": "", "displaySize": "coh-image", "imageStyle": "hero_2000_x_1100_", "imageAlignment": "coh-image-float-left" }, "md": { "image": "[field.8421da0d-d118-44b9-b643-8681c3888bd4]", "displaySize": "coh-image-responsive", "imageStyle": "", "imageAlignment": "coh-image-align-left" } }, "customStyle": [ { "customStyle": "" } ] }, "isVariableMode": false }, "8421da0d-d118-44b9-b643-8681c3888bd4": { "settings": { "type": "cohFileBrowser", "options": { "buttonText": "Select image", "imageUploader": false, "allowedDescription": "Allowed: png, gif, jpg, jpeg \nMax file size: 2MB", "removeLabel": "Remove" }, "title": "Image uploader", "isStyle": true, "defaultActive": true, "schema": { "type": "image" } } } }, "mapper": { "6d51ccd3-62d4-41f2-b146-2b8523ec251d": { "settings": { "topLevel": { "formDefinition": [ { "formKey": "picture-settings", "children": [ { "formKey": "picture-info", "breakpoints": [], "activeFields": [ { "name": "title", "active": true }, { "name": "alt", "active": true } ] }, { "formKey": "picture-images", "breakpoints": [ { "name": "xl" }, { "name": "lg" }, { "name": "md" }, { "name": "sm" }, { "name": "ps" }, { "name": "xs" } ], "activeFields": [ { "name": "image", "active": true }, { "name": "displaySize", "active": true }, { "name": "imageAlignment", "active": true }, { "name": "imageStyle", "active": true } ] }, { "formKey": "picture-style", "breakpoints": [], "activeFields": [ { "name": "customStyle", "active": true }, { "name": "customStyle", "active": true } ] } ] } ], "selectorType": "topLevel", "form": null }, "dropzone": [], "selectorType": "topLevel" } }, "b12eda0b-6d0d-4e9c-a09c-e4f1ece7f90c": { "settings": { "topLevel": { "formDefinition": [ { "formKey": "picture-settings", "children": [ { "formKey": "picture-info", "breakpoints": [], "activeFields": [ { "name": "title", "active": true }, { "name": "alt", "active": true } ] }, { "formKey": "picture-images", "breakpoints": [ { "name": "xl" }, { "name": "lg" }, { "name": "md" } ], "activeFields": [ { "name": "image", "active": true }, { "name": "displaySize", "active": true }, { "name": "imageAlignment", "active": true }, { "name": "imageStyle", "active": true } ] }, { "formKey": "picture-style", "breakpoints": [], "activeFields": [ { "name": "customStyle", "active": true }, { "name": "customStyle", "active": true } ] } ] } ], "selectorType": "topLevel", "form": null }, "dropzone": [], "selectorType": "topLevel" } } }, "canvas": [ { "type": "item", "uid": "picture", "title": "Picture", "selected": false, "status": { "collapsed": true, "isopen": false }, "parentIndex": 3, "uuid": "6d51ccd3-62d4-41f2-b146-2b8523ec251d", "parentUid": "root", "isContainer": false }, { "type": "item", "uid": "picture", "title": "Picture", "selected": false, "status": { "collapsed": true, "isopen": false }, "parentIndex": 3, "uuid": "b12eda0b-6d0d-4e9c-a09c-e4f1ece7f90c", "parentUid": "root", "isContainer": false } ], "componentForm": [ { "type": "form-field", "uid": "form-image", "title": "Image uploader", "parentIndex": "form-fields", "status": { "collapsed": false }, "parentUid": "root", "uuid": "8421da0d-d118-44b9-b643-8681c3888bd4", "humanId": "Field 1", "isContainer": false } ] }';

  /**
   * {@inheritdoc}
   */
  public function setUp():void {
    $this->unit = new _0007EntityUpdate([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0007EntityUpdate::runUpdate
   */
  public function testRunUpdate() {
    $layout = new _0007MockUpdateEntity($this->fixture_layout, TRUE);
    $this->assertionsLayoutCanvasBefore($layout->getDecodedJsonValues());
    $this->unit->runUpdate($layout);
    $this->assertionsLayoutCanvasAfter($layout->getDecodedJsonValues());
    $this->unit->runUpdate($layout);
    $this->assertionsLayoutCanvasAfter($layout->getDecodedJsonValues());

  }

  private function assertionsLayoutCanvasBefore($layout_array_before) {
    $this->assertEquals('[media-reference:file:188aadaa-9627-429a-b786-9f2171674467]', $layout_array_before['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['xl']['image']);
    $this->assertEquals('coh-image-responsive', $layout_array_before['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['xl']['displaySize']);
    $this->assertEquals('', $layout_array_before['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['xl']['imageStyle']);
    $this->assertEquals('coh-image-align-left', $layout_array_before['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['xl']['imageAlignment']);

    $this->assertEquals('[media-reference:file:188aadaa-9627-429a-b786-9f2171674467]', $layout_array_before['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['lg']['image']);
    $this->assertEquals('coh-image', $layout_array_before['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['lg']['displaySize']);
    $this->assertEquals('', $layout_array_before['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['lg']['imageStyle']);
    $this->assertEquals('coh-image-align-centre', $layout_array_before['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['lg']['imageAlignment']);

    $this->assertEquals('[media-reference:file:188aadaa-9627-429a-b786-9f2171674467]', $layout_array_before['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['md']['image']);
    $this->assertEquals('coh-image-responsive', $layout_array_before['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['md']['displaySize']);
    $this->assertEquals('crop_thumbnail', $layout_array_before['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['md']['imageStyle']);
    $this->assertEquals('coh-image-align-left', $layout_array_before['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['md']['imageAlignment']);

    $this->assertEquals('[media-reference:file:188aadaa-9627-429a-b786-9f2171674467]', $layout_array_before['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['sm']['image']);
    $this->assertEquals('coh-image', $layout_array_before['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['sm']['displaySize']);
    $this->assertEquals('large_landscape', $layout_array_before['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['sm']['imageStyle']);
    $this->assertEquals('coh-image-align-right', $layout_array_before['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['sm']['imageAlignment']);

    $this->assertArrayNotHasKey('image', $layout_array_before['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['ps']);
    $this->assertEquals('coh-image-responsive', $layout_array_before['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['ps']['displaySize']);
    $this->assertEquals('', $layout_array_before['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['ps']['imageStyle']);
    $this->assertEquals('coh-image-align-left', $layout_array_before['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['ps']['imageAlignment']);

    $this->assertArrayNotHasKey('image', $layout_array_before['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['xs']);
    $this->assertEquals('coh-image', $layout_array_before['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['xs']['displaySize']);
    $this->assertEquals('', $layout_array_before['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['xs']['imageStyle']);
    $this->assertEquals('coh-image-float-right', $layout_array_before['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['xs']['imageAlignment']);

    $this->assertEquals('', $layout_array_before['model']['b12eda0b-6d0d-4e9c-a09c-e4f1ece7f90c']['settings']['styles']['xl']['image']);
    $this->assertEquals('coh-image-responsive', $layout_array_before['model']['b12eda0b-6d0d-4e9c-a09c-e4f1ece7f90c']['settings']['styles']['xl']['displaySize']);
    $this->assertEquals('crop_thumbnail', $layout_array_before['model']['b12eda0b-6d0d-4e9c-a09c-e4f1ece7f90c']['settings']['styles']['xl']['imageStyle']);
    $this->assertEquals('coh-image-align-left', $layout_array_before['model']['b12eda0b-6d0d-4e9c-a09c-e4f1ece7f90c']['settings']['styles']['xl']['imageAlignment']);

    $this->assertEquals('', $layout_array_before['model']['b12eda0b-6d0d-4e9c-a09c-e4f1ece7f90c']['settings']['styles']['lg']['image']);
    $this->assertEquals('coh-image', $layout_array_before['model']['b12eda0b-6d0d-4e9c-a09c-e4f1ece7f90c']['settings']['styles']['lg']['displaySize']);
    $this->assertEquals('hero_2000_x_1100_', $layout_array_before['model']['b12eda0b-6d0d-4e9c-a09c-e4f1ece7f90c']['settings']['styles']['lg']['imageStyle']);
    $this->assertEquals('coh-image-float-left', $layout_array_before['model']['b12eda0b-6d0d-4e9c-a09c-e4f1ece7f90c']['settings']['styles']['lg']['imageAlignment']);

    $this->assertEquals('[field.8421da0d-d118-44b9-b643-8681c3888bd4]', $layout_array_before['model']['b12eda0b-6d0d-4e9c-a09c-e4f1ece7f90c']['settings']['styles']['md']['image']);
    $this->assertEquals('coh-image-responsive', $layout_array_before['model']['b12eda0b-6d0d-4e9c-a09c-e4f1ece7f90c']['settings']['styles']['md']['displaySize']);
    $this->assertEquals('', $layout_array_before['model']['b12eda0b-6d0d-4e9c-a09c-e4f1ece7f90c']['settings']['styles']['md']['imageStyle']);
    $this->assertEquals('coh-image-align-left', $layout_array_before['model']['b12eda0b-6d0d-4e9c-a09c-e4f1ece7f90c']['settings']['styles']['md']['imageAlignment']);
  }

  private function assertionsLayoutCanvasAfter($layout) {
    $this->assertEquals('[media-reference:file:188aadaa-9627-429a-b786-9f2171674467]', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['xl']['pictureImagesArray'][0]['image']);
    $this->assertArrayNotHasKey('image', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['xl']);
    $this->assertEquals('coh-image-responsive', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['xl']['displaySize']);
    $this->assertEquals('', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['xl']['pictureImagesArray'][0]['imageStyle']);
    $this->assertArrayNotHasKey('imageStyle', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['xl']);
    $this->assertEquals('coh-image-align-left', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['xl']['imageAlignment']);

    $this->assertEquals('[media-reference:file:188aadaa-9627-429a-b786-9f2171674467]', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['lg']['pictureImagesArray'][0]['image']);
    $this->assertArrayNotHasKey('image', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['lg']);
    $this->assertEquals('coh-image', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['lg']['displaySize']);
    $this->assertEquals('', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['lg']['pictureImagesArray'][0]['imageStyle']);
    $this->assertArrayNotHasKey('imageStyle', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['lg']);
    $this->assertEquals('coh-image-align-centre', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['lg']['imageAlignment']);

    $this->assertEquals('[media-reference:file:188aadaa-9627-429a-b786-9f2171674467]', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['md']['pictureImagesArray'][0]['image']);
    $this->assertArrayNotHasKey('image', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['md']);
    $this->assertEquals('coh-image-responsive', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['md']['displaySize']);
    $this->assertEquals('crop_thumbnail', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['md']['pictureImagesArray'][0]['imageStyle']);
    $this->assertArrayNotHasKey('imageStyle', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['md']);
    $this->assertEquals('coh-image-align-left', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['md']['imageAlignment']);

    $this->assertEquals('[media-reference:file:188aadaa-9627-429a-b786-9f2171674467]', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['sm']['pictureImagesArray'][0]['image']);
    $this->assertArrayNotHasKey('image', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['sm']);
    $this->assertEquals('coh-image', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['sm']['displaySize']);
    $this->assertEquals('large_landscape', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['sm']['pictureImagesArray'][0]['imageStyle']);
    $this->assertArrayNotHasKey('imageStyle', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['sm']);
    $this->assertEquals('coh-image-align-right', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['sm']['imageAlignment']);

    $this->assertArrayNotHasKey('image', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['ps']['pictureImagesArray'][0]);
    $this->assertArrayNotHasKey('image', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['ps']);
    $this->assertEquals('coh-image-responsive', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['ps']['displaySize']);
    $this->assertEquals('', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['ps']['pictureImagesArray'][0]['imageStyle']);
    $this->assertArrayNotHasKey('imageStyle', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['ps']);
    $this->assertEquals('coh-image-align-left', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['ps']['imageAlignment']);

    $this->assertArrayNotHasKey('image', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['xs']['pictureImagesArray'][0]);
    $this->assertArrayNotHasKey('image', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['xs']);
    $this->assertEquals('coh-image', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['xs']['displaySize']);
    $this->assertEquals('', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['xs']['pictureImagesArray'][0]['imageStyle']);
    $this->assertArrayNotHasKey('imageStyle', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['xs']);
    $this->assertEquals('coh-image-float-right', $layout['model']['6d51ccd3-62d4-41f2-b146-2b8523ec251d']['settings']['styles']['xs']['imageAlignment']);

    $this->assertEquals('', $layout['model']['b12eda0b-6d0d-4e9c-a09c-e4f1ece7f90c']['settings']['styles']['xl']['pictureImagesArray'][0]['image']);
    $this->assertArrayNotHasKey('image', $layout['model']['b12eda0b-6d0d-4e9c-a09c-e4f1ece7f90c']['settings']['styles']['xl']);
    $this->assertEquals('coh-image-responsive', $layout['model']['b12eda0b-6d0d-4e9c-a09c-e4f1ece7f90c']['settings']['styles']['xl']['displaySize']);
    $this->assertEquals('crop_thumbnail', $layout['model']['b12eda0b-6d0d-4e9c-a09c-e4f1ece7f90c']['settings']['styles']['xl']['pictureImagesArray'][0]['imageStyle']);
    $this->assertArrayNotHasKey('imageStyle', $layout['model']['b12eda0b-6d0d-4e9c-a09c-e4f1ece7f90c']['settings']['styles']['xl']);
    $this->assertEquals('coh-image-align-left', $layout['model']['b12eda0b-6d0d-4e9c-a09c-e4f1ece7f90c']['settings']['styles']['xl']['imageAlignment']);

    $this->assertEquals('', $layout['model']['b12eda0b-6d0d-4e9c-a09c-e4f1ece7f90c']['settings']['styles']['lg']['pictureImagesArray'][0]['image']);
    $this->assertArrayNotHasKey('image', $layout['model']['b12eda0b-6d0d-4e9c-a09c-e4f1ece7f90c']['settings']['styles']['lg']);
    $this->assertEquals('coh-image', $layout['model']['b12eda0b-6d0d-4e9c-a09c-e4f1ece7f90c']['settings']['styles']['lg']['displaySize']);
    $this->assertEquals('hero_2000_x_1100_', $layout['model']['b12eda0b-6d0d-4e9c-a09c-e4f1ece7f90c']['settings']['styles']['lg']['pictureImagesArray'][0]['imageStyle']);
    $this->assertArrayNotHasKey('imageStyle', $layout['model']['b12eda0b-6d0d-4e9c-a09c-e4f1ece7f90c']['settings']['styles']['lg']);
    $this->assertEquals('coh-image-float-left', $layout['model']['b12eda0b-6d0d-4e9c-a09c-e4f1ece7f90c']['settings']['styles']['lg']['imageAlignment']);

    $this->assertEquals('[field.8421da0d-d118-44b9-b643-8681c3888bd4]', $layout['model']['b12eda0b-6d0d-4e9c-a09c-e4f1ece7f90c']['settings']['styles']['md']['pictureImagesArray'][0]['image']);
    $this->assertArrayNotHasKey('image', $layout['model']['b12eda0b-6d0d-4e9c-a09c-e4f1ece7f90c']['settings']['styles']['md']);
    $this->assertEquals('coh-image-responsive', $layout['model']['b12eda0b-6d0d-4e9c-a09c-e4f1ece7f90c']['settings']['styles']['md']['displaySize']);
    $this->assertEquals('', $layout['model']['b12eda0b-6d0d-4e9c-a09c-e4f1ece7f90c']['settings']['styles']['md']['pictureImagesArray'][0]['imageStyle']);
    $this->assertArrayNotHasKey('imageStyle', $layout['model']['b12eda0b-6d0d-4e9c-a09c-e4f1ece7f90c']['settings']['styles']['md']);
    $this->assertEquals('coh-image-align-left', $layout['model']['b12eda0b-6d0d-4e9c-a09c-e4f1ece7f90c']['settings']['styles']['md']['imageAlignment']);
  }

}
