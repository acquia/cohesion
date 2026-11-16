<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\Plugin\EntityUpdate\_0006EntityUpdate;

/**
 * Class MockUpdateEntity.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class _0006MockUpdateEntity extends EntityMockBase implements EntityJsonValuesInterface {
}

/**
 * @group Cohesion
 */
class _0006EntityUpdateUnitTest extends EntityUpdateUnitTestCase {

  /**
   * @var unit_0006EntityUpdate*/
  protected $unit;

  private $fixture_layout = '{ "canvas": [ { "type": "container", "uid": "container", "title": "Container", "status": { "collapsed": false }, "children": [], "parentIndex": 2, "parentUid": "root", "uuid": "4b62d728-24b3-43ce-bba3-e3768cf1e543", "isContainer": true } ], "model": { "4b62d728-24b3-43ce-bba3-e3768cf1e543": { "settings": { "width": "fluid", "customStyle": [ { "customStyle": "" } ], "styles": { "xl": { "settings,styles,xl,linkAnimation": [ { "animationType": "none", "animationScope": "document", "animationScale": null, "animationDirection": "up", "animationOrigin": "top,center", "animationHorizontalFirst": false, "animationEasing": "swing" } ] } } }, "context-visibility": { "contextVisibility": { "condition": "ALL" } }, "styles": { "settings": { "element": "container" }, "styles": { "xl": { "background-image-settings": [ { "backgroundLayerType": { "value": "image" }, "backgroundImageSize": { "value": "auto" }, "backgroundImageRepeat": { "value": "no-repeat" }, "backgroundImageAttachment": { "value": "scroll" }, "backgroundImagePositionX": { "value": "left" }, "backgroundImagePositionXOffset": {}, "backgroundImagePositionY": { "value": "top" }, "backgroundImagePositionYOffset": {}, "backgroundImage": {} }, { "backgroundLayerType": { "value": "gradient" }, "backgroundImageSize": { "value": "auto" }, "backgroundImageRepeat": { "value": "no-repeat" }, "backgroundImageAttachment": { "value": "scroll" }, "backgroundImagePositionX": { "value": "left" }, "backgroundImagePositionXOffset": {}, "backgroundImagePositionY": { "value": "top" }, "backgroundImagePositionYOffset": {}, "backgroundImage": {}, "backgroundImageGradient": {} }, { "backgroundLayerType": { "value": "image" }, "backgroundImageSize": { "value": "auto" }, "backgroundImageRepeat": { "value": "no-repeat" }, "backgroundImageAttachment": { "value": "scroll" }, "backgroundImagePositionX": { "value": "left" }, "backgroundImagePositionXOffset": {}, "backgroundImagePositionY": { "value": "top" }, "backgroundImagePositionYOffset": {}, "backgroundImage": { "value": "[media-reference:file:188aadaa-9627-429a-b786-9f2171674467]" } } ] }, "md": { "background-image-settings": [ { "backgroundLayerType": { "value": "image" }, "backgroundImageSize": { "value": "auto" }, "backgroundImageRepeat": { "value": "no-repeat" }, "backgroundImageAttachment": { "value": "scroll" }, "backgroundImagePositionX": { "value": "left" }, "backgroundImagePositionXOffset": {}, "backgroundImagePositionY": { "value": "top" }, "backgroundImagePositionYOffset": {}, "backgroundImage": { "value": "[media-reference:file:188aadaa-9627-429a-b786-9f2171674467]" } } ] }, "xs": { "background-image-settings": [ { "backgroundLayerType": { "value": "image" }, "backgroundImageSize": { "value": "auto" }, "backgroundImageRepeat": { "value": "no-repeat" }, "backgroundImageAttachment": { "value": "scroll" }, "backgroundImagePositionX": { "value": "left" }, "backgroundImagePositionXOffset": {}, "backgroundImagePositionY": { "value": "top" }, "backgroundImagePositionYOffset": {}, "backgroundImage": {} } ] } } }, "isVariableMode": false } }, "mapper": { "4b62d728-24b3-43ce-bba3-e3768cf1e543": { "settings": { "topLevel": { "formDefinition": [ { "formKey": "container-settings", "children": [ { "formKey": "container-width", "breakpoints": [], "activeFields": [ { "name": "width", "active": true } ] }, { "formKey": "common-link-animation", "breakpoints": [ { "name": "xl" } ], "activeFields": [ { "name": "linkAnimation", "active": true }, { "name": "animationType", "active": true }, { "name": "animationScope", "active": true }, { "name": "animationParent", "active": true }, { "name": "animationTarget", "active": true }, { "name": "animationScale", "active": true }, { "name": "animationDirection", "active": true }, { "name": "animationDirection", "active": true }, { "name": "animationDirection", "active": true }, { "name": "animationDistance", "active": true }, { "name": "animationPieces", "active": true }, { "name": "animationOrigin", "active": true }, { "name": "animationFoldHeight", "active": true }, { "name": "animationHorizontalFirst", "active": true }, { "name": "animationIterations", "active": true }, { "name": "animationEasing", "active": true }, { "name": "animationDuration", "active": true } ] }, { "formKey": "container-style", "breakpoints": [], "activeFields": [ { "name": "customStyle", "active": true }, { "name": "customStyle", "active": true } ] } ] } ], "selectorType": "topLevel", "form": null }, "dropzone": [], "selectorType": "topLevel" }, "styles": { "topLevel": { "formDefinition": [ { "formKey": "background", "children": [ { "formKey": "background-image-and-gradient", "breakpoints": [ { "name": "xl" }, { "name": "md" }, { "name": "xs" } ], "activeFields": [ { "name": "background-image-settings", "active": true }, { "name": "backgroundLayerType", "active": true }, { "name": "backgroundImage", "active": true }, { "name": "backgroundImageGradient", "active": true }, { "name": "backgroundImageSize", "active": true }, { "name": "backgroundImageWidth", "active": true }, { "name": "backgroundImageHeight", "active": true }, { "name": "backgroundImageRepeat", "active": true }, { "name": "backgroundImageAttachment", "active": true }, { "name": "backgroundImagePositionX", "active": true }, { "name": "backgroundImagePositionXOffset", "active": true }, { "name": "backgroundImagePositionY", "active": true }, { "name": "backgroundImagePositionYOffset", "active": true }, { "name": "backgroundImageOrigin", "active": false }, { "name": "backgroundImageClip", "active": false } ] } ] } ], "selectorType": "topLevel", "form": null }, "dropzone": [] } } }, "componentForm": [] }';
  private $fixture_style = '{ "preview": { "text": "<p class=\"coh-preview\">Default content for \'Paragraph\'.&nbsp;Default content for \'Paragraph\'.&nbsp;Default content for \'Paragraph\'.&nbsp;Default content for \'Paragraph\'.&nbsp;Default content for \'Paragraph\'.&nbsp;Default content for \'Paragraph\'.&nbsp;Default content for \'Paragraph\'.&nbsp;Default content for \'Paragraph\'.</p>\n", "textFormat": "cohesion" }, "styles": { "settings": { "element": "p", "class": "", "combinator": "", "pseudo": "" }, "styles": { "xl": { "color": {}, "font-family": {}, "font-weight": {}, "font-size": {}, "text-transform": {}, "background-image-settings": [ { "backgroundLayerType": { "value": "image" }, "backgroundImageSize": { "value": "auto" }, "backgroundImageRepeat": { "value": "no-repeat" }, "backgroundImageAttachment": { "value": "scroll" }, "backgroundImagePositionX": { "value": "left" }, "backgroundImagePositionY": { "value": "top" } } ] }, "styles": { "xl": { "background-image-settings": [ { "backgroundLayerType": { "value": "image" } } ] } } }, "pseudos": [ { "settings": { "element": "", "class": "", "combinator": "", "pseudo": ":active" }, "styles": { "xl": { "background-image-settings": [ { "backgroundLayerType": { "value": "image" }, "backgroundImageSize": { "value": "auto" }, "backgroundImageRepeat": { "value": "no-repeat" }, "backgroundImageAttachment": { "value": "scroll" }, "backgroundImagePositionX": { "value": "left" }, "backgroundImagePositionY": { "value": "top" } } ] } }, "children": [ { "settings": { "element": "seond-level", "class": "", "combinator": "", "pseudo": "" }, "styles": { "xl": { "background-image-settings": [ { "backgroundLayerType": { "value": "image" }, "backgroundImageSize": { "value": "auto" }, "backgroundImageRepeat": { "value": "no-repeat" }, "backgroundImageAttachment": { "value": "scroll" }, "backgroundImagePositionX": { "value": "left" }, "backgroundImagePositionY": { "value": "top" } } ] } }, "children": [], "pseudos": [], "modifiers": [], "prefix": [] } ], "pseudos": [], "modifiers": [], "prefix": [] } ], "modifiers": [ { "settings": { "element": "", "class": ".modif", "combinator": "", "pseudo": "" }, "styles": { "xl": { "background-image-settings": [ { "backgroundLayerType": { "value": "image" }, "backgroundImageSize": { "value": "auto" }, "backgroundImageRepeat": { "value": "no-repeat" }, "backgroundImageAttachment": { "value": "scroll" }, "backgroundImagePositionX": { "value": "left" }, "backgroundImagePositionY": { "value": "top" } } ] } }, "children": [], "pseudos": [], "modifiers": [], "prefix": [] } ], "prefix": [ { "settings": { "element": "", "class": "pref", "combinator": "", "pseudo": "" }, "styles": { "xl": { "background-image-settings": [ { "backgroundLayerType": { "value": "image" }, "backgroundImageSize": { "value": "auto" }, "backgroundImageRepeat": { "value": "no-repeat" }, "backgroundImageAttachment": { "value": "scroll" }, "backgroundImagePositionX": { "value": "left" }, "backgroundImagePositionY": { "value": "top" } } ] } }, "children": [], "pseudos": [], "modifiers": [], "prefix": [] } ], "children": [ { "settings": { "element": "child", "class": "", "combinator": "", "pseudo": "" }, "styles": { "xl": { "background-image-settings": [ { "backgroundLayerType": { "value": "image" }, "backgroundImageSize": { "value": "auto" }, "backgroundImageRepeat": { "value": "no-repeat" }, "backgroundImageAttachment": { "value": "scroll" }, "backgroundImagePositionX": { "value": "left" }, "backgroundImagePositionY": { "value": "top" } } ] } }, "children": [], "pseudos": [], "modifiers": [], "prefix": [] } ] }, "sBackgroundColour": "#FFFFFF" }';

  /**
   * {@inheritdoc}
   */
  public function setUp():void {
    $this->unit = new _0006EntityUpdate([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0006EntityUpdate::runUpdate
   */
  public function testRunUpdate() {

    // Layout canvas.
    $layout = new _0006MockUpdateEntity($this->fixture_layout, TRUE);
    $this->assertionsLayoutCanvasBefore($layout->getDecodedJsonValues());
    $this->unit->runUpdate($layout);
    $this->assertionsLayoutCanvasAfter($layout->getDecodedJsonValues());

    // Styles.
    $style = new _0006MockUpdateEntity($this->fixture_style, FALSE);
    $this->assertBeforeStyle($style->getDecodedJsonValues());
    $this->unit->runUpdate($style);
    $this->assertAfterStyle($style->getDecodedJsonValues());
  }

  private function assertionsLayoutCanvasBefore($layout_array_before) {
    // XL breakpoint.
    $this->assertEquals("image", $layout_array_before['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['xl']['background-image-settings'][0]['backgroundLayerType']['value']);
    $this->assertEquals("auto", $layout_array_before['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['xl']['background-image-settings'][0]['backgroundImageSize']['value']);
    $this->assertEquals("no-repeat", $layout_array_before['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['xl']['background-image-settings'][0]['backgroundImageRepeat']['value']);
    $this->assertEquals("scroll", $layout_array_before['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['xl']['background-image-settings'][0]['backgroundImageAttachment']['value']);
    $this->assertEquals("left", $layout_array_before['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['xl']['background-image-settings'][0]['backgroundImagePositionX']['value']);
    $this->assertEmpty($layout_array_before['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['xl']['background-image-settings'][0]['backgroundImagePositionXOffset']);
    $this->assertEquals("top", $layout_array_before['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['xl']['background-image-settings'][0]['backgroundImagePositionY']['value']);
    $this->assertEmpty($layout_array_before['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['xl']['background-image-settings'][0]['backgroundImagePositionYOffset']);
    $this->assertEmpty($layout_array_before['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['xl']['background-image-settings'][0]['backgroundImage']);

    $this->assertEquals("gradient", $layout_array_before['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['xl']['background-image-settings'][1]['backgroundLayerType']['value']);
    $this->assertArrayHasKey('backgroundImage', $layout_array_before['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['xl']['background-image-settings'][1]);

    $this->assertEquals("image", $layout_array_before['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['xl']['background-image-settings'][2]['backgroundLayerType']['value']);
    $this->assertArrayHasKey('backgroundImage', $layout_array_before['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['xl']['background-image-settings'][2]);
    $this->assertEquals('[media-reference:file:188aadaa-9627-429a-b786-9f2171674467]', $layout_array_before['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['xl']['background-image-settings'][2]['backgroundImage']['value']);

    // MD breakpoint.
    $this->assertEquals("image", $layout_array_before['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['md']['background-image-settings'][0]['backgroundLayerType']['value']);
    $this->assertEquals("[media-reference:file:188aadaa-9627-429a-b786-9f2171674467]", $layout_array_before['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['md']['background-image-settings'][0]['backgroundImage']['value']);

    // XS breakpoint.
    $this->assertEquals("image", $layout_array_before['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['xs']['background-image-settings'][0]['backgroundLayerType']['value']);
    $this->assertArrayHasKey('backgroundImage', $layout_array_before['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['xs']['background-image-settings'][0]);
  }

  private function assertionsLayoutCanvasAfter($layout_array_after) {
    // XL breakpoint.
    $this->assertEquals("none", $layout_array_after['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['xl']['background-image-settings'][0]['backgroundLayerType']['value']);
    $this->assertEmpty($layout_array_after['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['xl']['background-image-settings'][0]['backgroundImageSize']);
    $this->assertEmpty($layout_array_after['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['xl']['background-image-settings'][0]['backgroundImageRepeat']);
    $this->assertEmpty($layout_array_after['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['xl']['background-image-settings'][0]['backgroundImageAttachment']);
    $this->assertEmpty($layout_array_after['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['xl']['background-image-settings'][0]['backgroundImagePositionX']);
    $this->assertEmpty($layout_array_after['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['xl']['background-image-settings'][0]['backgroundImagePositionXOffset']);
    $this->assertEmpty($layout_array_after['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['xl']['background-image-settings'][0]['backgroundImagePositionY']);
    $this->assertEmpty($layout_array_after['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['xl']['background-image-settings'][0]['backgroundImagePositionYOffset']);
    $this->assertArrayNotHasKey('backgroundImage', $layout_array_after['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['xl']['background-image-settings'][0]);

    $this->assertEquals("gradient", $layout_array_after['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['xl']['background-image-settings'][1]['backgroundLayerType']['value']);
    $this->assertArrayHasKey('backgroundImage', $layout_array_after['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['xl']['background-image-settings'][1]);

    $this->assertEquals("image", $layout_array_after['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['xl']['background-image-settings'][2]['backgroundLayerType']['value']);
    $this->assertArrayHasKey('backgroundImage', $layout_array_after['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['xl']['background-image-settings'][2]);
    $this->assertEquals('[media-reference:file:188aadaa-9627-429a-b786-9f2171674467]', $layout_array_after['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['xl']['background-image-settings'][2]['backgroundImage']['value']);

    // MD breakpoint.
    $this->assertEquals("image", $layout_array_after['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['md']['background-image-settings'][0]['backgroundLayerType']['value']);
    $this->assertEquals("[media-reference:file:188aadaa-9627-429a-b786-9f2171674467]", $layout_array_after['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['md']['background-image-settings'][0]['backgroundImage']['value']);

    // XS breakpoint.
    $this->assertEquals("none", $layout_array_after['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['xs']['background-image-settings'][0]['backgroundLayerType']['value']);
    $this->assertArrayNotHasKey('backgroundImage', $layout_array_after['model']['4b62d728-24b3-43ce-bba3-e3768cf1e543']['styles']['styles']['xs']['background-image-settings'][0]);
  }

  private function assertBeforeStyle($style_array_before) {
    $this->assertEquals("image", $style_array_before['styles']['styles']['xl']['background-image-settings'][0]['backgroundLayerType']['value']);
    $this->assertEquals("image", $style_array_before['styles']['pseudos'][0]['styles']['xl']['background-image-settings'][0]['backgroundLayerType']['value']);
    $this->assertEquals("image", $style_array_before['styles']['pseudos'][0]['children'][0]['styles']['xl']['background-image-settings'][0]['backgroundLayerType']['value']);
    $this->assertEquals("image", $style_array_before['styles']['modifiers'][0]['styles']['xl']['background-image-settings'][0]['backgroundLayerType']['value']);
    $this->assertEquals("image", $style_array_before['styles']['prefix'][0]['styles']['xl']['background-image-settings'][0]['backgroundLayerType']['value']);
    $this->assertEquals("image", $style_array_before['styles']['children'][0]['styles']['xl']['background-image-settings'][0]['backgroundLayerType']['value']);
  }

  private function assertAfterStyle($style_array_after) {
    $this->assertEquals("none", $style_array_after['styles']['styles']['xl']['background-image-settings'][0]['backgroundLayerType']['value']);
    $this->assertEquals("none", $style_array_after['styles']['pseudos'][0]['styles']['xl']['background-image-settings'][0]['backgroundLayerType']['value']);
    $this->assertEquals("none", $style_array_after['styles']['pseudos'][0]['children'][0]['styles']['xl']['background-image-settings'][0]['backgroundLayerType']['value']);
    $this->assertEquals("none", $style_array_after['styles']['modifiers'][0]['styles']['xl']['background-image-settings'][0]['backgroundLayerType']['value']);
    $this->assertEquals("none", $style_array_after['styles']['prefix'][0]['styles']['xl']['background-image-settings'][0]['backgroundLayerType']['value']);
    $this->assertEquals("none", $style_array_after['styles']['children'][0]['styles']['xl']['background-image-settings'][0]['backgroundLayerType']['value']);
  }

}
