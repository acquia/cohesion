<?php

namespace Drupal\Tests\cohesion_style_guide\Unit\Services;

use Drupal\cohesion\Services\JsonXss;
use Drupal\Core\Session\AccountInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Class StyleGuideManagerHandlerMock
 *
 * @package Drupal\Tests\cohesion_style_guide\Unit\Services
 */
class JsonXssMock extends JsonXss {
}

/**
 * @group Cohesion
 */
class JsonXssUnitTest extends UnitTestCase {

  protected $mockUnit;

  public function setUp() {
    // Create a mock of the classes required to init StyleGuideManagerHandler.
    $prophecy = $this->prophesize(AccountInterface::CLASS);
    $account = $prophecy->reveal();

    $this->mockUnit = new JsonXssMock($account);
  }

  /**
   * @covers \Drupal\cohesion\Services\JsonXss::buildXssPaths
   */
  public function testBuildXssPaths() {
    $paths = $this->mockUnit->buildXssPaths('{"canvas":[{"type":"item","uid":"wysiwyg","title":"WYSIWYG","status":{"collapsed":true},"uuid":"237f627e-a58e-4998-8e10-5e296b65c7e3","parentUid":"root","isContainer":false,"children":[]}],"componentForm":[],"model":{"237f627e-a58e-4998-8e10-5e296b65c7e3":{"settings":{"title":"WYSIWYG","content":"<script>x();<\/script>"},"context-visibility":{"contextVisibility":{"condition":"ALL"}},"styles":{"settings":{"element":"wysiwyg"}},"markup":{"attributes":[{"attribute":"onclick","value":"x();"}],"prefix":"<script>x();<\/script>","suffix":"<script>x();<\/script>"}}},"mapper":{"237f627e-a58e-4998-8e10-5e296b65c7e3":{"settings":{"topLevel":{"formDefinition":[{"formKey":"wysiwyg-settings","children":[{"formKey":"wysiwyg-editor","breakpoints":[],"activeFields":[{"name":"content","active":true}]}]}],"selectorType":"topLevel","form":null},"dropzone":[],"selectorType":"topLevel"},"markup":{"topLevel":{"formDefinition":[{"formKey":"tab-markup-classes-and-ids","children":[{"formKey":"tab-markup-add-classes","breakpoints":[],"activeFields":[{"name":"classes","active":true}]}]},{"formKey":"tab-markup-attributes","children":[{"formKey":"tab-markup-add-attributes-values","breakpoints":[],"activeFields":[{"name":"attributes","active":true},{"name":"attribute","active":true},{"name":0,"active":true}]}]},{"formKey":"tab-markup-prefix-and-suffix","children":[{"formKey":"tab-markup-prefix-markup-before-region","breakpoints":[],"activeFields":[{"name":"prefix","active":true}]},{"formKey":"tab-markup-suffix-markup-after-region","breakpoints":[],"activeFields":[{"name":"suffix","active":true}]}]}],"title":"Markup","selectorType":"topLevel","form":null},"dropzone":[],"title":"Markup","selectorType":"topLevel"}}},"previewModel":{"237f627e-a58e-4998-8e10-5e296b65c7e3":{"settings":{"content":{"text":"<script>x();<\/script>","textFormat":"cohesion"}}}},"variableFields":{"237f627e-a58e-4998-8e10-5e296b65c7e3":["settings.content"]}}');

    $this->assertEquals(5, count($paths));
    $this->assertArrayHasKey('237f627e-a58e-4998-8e10-5e296b65c7e3.settings.content', $paths);
    $this->assertArrayHasKey('237f627e-a58e-4998-8e10-5e296b65c7e3.markup.prefix', $paths);
    $this->assertArrayHasKey('237f627e-a58e-4998-8e10-5e296b65c7e3.markup.suffix', $paths);
    $this->assertArrayHasKey('237f627e-a58e-4998-8e10-5e296b65c7e3.markup.attributes.0.attribute', $paths);
    $this->assertArrayHasKey('237f627e-a58e-4998-8e10-5e296b65c7e3.markup.attributes.0.value', $paths);

  }
}
