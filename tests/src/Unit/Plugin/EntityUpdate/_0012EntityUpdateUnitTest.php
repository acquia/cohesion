<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\Plugin\EntityUpdate\_0012EntityUpdate;

/**
 * Class MockUpdateEntity.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class _0012MockUpdateEntity extends EntityMockBase implements EntityJsonValuesInterface {

}

/**
 * @group Cohesion
 */
class _0012EntityUpdateUnitTest extends EntityUpdateUnitTestCase {

  /**
   * @var unit_0012MockUpdateEntity*/
  protected $unit;

  private $fixture_layout = '{"model":{"24f63711-0e8b-49fa-b32c-474b69cc8eaa":{"settings":{"title":"Link","customStyle":[{"customStyle":""}],"type":"modifier","scrollToDuration":450,"interactionScope":"document","modifierType":"toggle-modifier-accessible-collapsed","styles":{"xl":{"settings,styles,xl,linkAnimation":[{"animationType":"none","animationScope":"document","animationScale":null,"animationDirection":"up","animationOrigin":"top,center","animationHorizontalFirst":false,"animationEasing":"swing"}]}},"target":"_self","modifier":[{"interactionScope":"document","modifierType":"toggle-modifier-accessible-collapsed","interactionTarget":"target","modifierName":"modif"}],"linkText":"ssss"},"context-visibility":{"contextVisibility":{"condition":"ALL"}},"styles":{"settings":{"element":"link"}}},"8a277676-bc4f-4bd7-ade9-01887623b237":{"settings":{"title":"Container","width":"fluid","customStyle":[{"customStyle":""}],"styles":{"xl":{"settings,styles,xl,linkAnimation":[{"animationType":"none","animationScope":"document","animationScale":null,"animationDirection":"up","animationOrigin":"top,center","animationHorizontalFirst":false,"animationEasing":"swing"}],"linkAnimation":[{"animationType":"none"}]}},"type":"animation","scrollToDuration":450,"interactionScope":"document","modifierType":"toggle-modifier-accessible-collapsed","target":null},"context-visibility":{"contextVisibility":{"condition":"ALL"}},"styles":{"settings":{"element":"container"}}},"25526c76-3dc2-4461-8888-8a9727f61ab4":{"settings":{"title":"Column","styles":{"xl":{"col":-2,"pull":-1,"push":-1,"offset":-1,"settings,styles,xl,linkAnimation":[{"animationType":"none","animationScope":"document","animationScale":null,"animationDirection":"up","animationOrigin":"top,center","animationHorizontalFirst":false,"animationEasing":"swing"}]}},"type":"modifier","scrollToDuration":450,"interactionScope":"document","modifierType":"toggle-modifier-accessible-collapsed","target":null,"modifier":[{"interactionScope":"document","modifierType":"toggle-modifier-accessible-collapsed"}]},"context-visibility":{"contextVisibility":{"condition":"ALL"}},"styles":{"settings":{"element":"column"}}},"cb586ea4-4cbd-4e31-8d4e-901b4bc9fba4":{"settings":{"title":"Button","customStyle":[{"customStyle":""}],"type":"modifier","scrollToDuration":450,"styles":{"xl":{"settings,styles,xl,buttonAnimation":[{"animationType":"none","animationScope":"document","animationScale":null,"animationDirection":"up","animationOrigin":"top,center","animationHorizontalFirst":false,"animationEasing":"swing"}]}},"modifier":[{"interactionScope":"document","modifierType":"toggle-modifier-accessible-collapsed"}]},"context-visibility":{"contextVisibility":{"condition":"ALL"}},"styles":{"settings":{"element":"button"}}},"2523c7ac-76e6-4084-999b-d65dd733eef8":{"settings":{"title":"Slide","customStyle":[{"customStyle":""}],"styles":{"xl":{"settings,styles,xl,linkAnimation":[{"animationType":"none","animationScope":"document","animationScale":null,"animationDirection":"up","animationOrigin":"top,center","animationHorizontalFirst":false,"animationEasing":"swing"}],"linkAnimation":[{"animationType":"none"}]}},"type":"animation","scrollToDuration":450,"interactionScope":"document","modifierType":"toggle-modifier-accessible-collapsed","target":null},"context-visibility":{"contextVisibility":{"condition":"ALL"}},"styles":{"settings":{"element":"slide"}}}},"mapper":{"24f63711-0e8b-49fa-b32c-474b69cc8eaa":{"settings":{"topLevel":{"formDefinition":[{"formKey":"link-settings","children":[{"formKey":"link-link","breakpoints":[],"activeFields":[{"name":"linkText","active":true},{"name":"titleAttribute","active":true},{"name":"type","active":true},{"name":"linkToPage","active":true},{"name":"url","active":true},{"name":"anchor","active":true},{"name":"scrollToSelector","active":true},{"name":"scrollToDuration","active":true},{"name":"scrollToOffsetType","active":true},{"name":"scrollToOffsetPx","active":true},{"name":"scrollToOffsetElement","active":true},{"name":"target","active":true},{"name":"modifier","active":true},{"name":"interactionScope","active":true},{"name":"interactionParent","active":true},{"name":"interactionTarget","active":true},{"name":"modifierType","active":true},{"name":"modifierName","active":true}]},{"formKey":"link-animation","breakpoints":[{"name":"xl"}],"activeFields":[{"name":"linkAnimation","active":true},{"name":"animationType","active":true},{"name":"animationScope","active":true},{"name":"animationParent","active":true},{"name":"animationTarget","active":true},{"name":"animationScale","active":true},{"name":"animationDirection","active":true},{"name":"animationDirection","active":true},{"name":"animationDirection","active":true},{"name":"animationDistance","active":true},{"name":"animationPieces","active":true},{"name":"animationOrigin","active":true},{"name":"animationFoldHeight","active":true},{"name":"animationHorizontalFirst","active":true},{"name":"animationIterations","active":true},{"name":"animationEasing","active":true},{"name":"animationDuration","active":true}]},{"formKey":"link-style","breakpoints":[],"activeFields":[{"name":"customStyle","active":true},{"name":"customStyle","active":true}]}]}],"selectorType":"topLevel","form":null},"dropzone":[],"selectorType":"topLevel"}},"8a277676-bc4f-4bd7-ade9-01887623b237":{"settings":{"topLevel":{"formDefinition":[{"formKey":"container-settings","children":[{"formKey":"container-width","breakpoints":[],"activeFields":[{"name":"width","active":true}]},{"formKey":"common-link-animation","breakpoints":[{"name":"xl"}],"activeFields":[{"name":"linkAnimation","active":true},{"name":"animationType","active":true},{"name":"animationScope","active":true},{"name":"animationParent","active":true},{"name":"animationTarget","active":true},{"name":"animationScale","active":true},{"name":"animationDirection","active":true},{"name":"animationDirection","active":true},{"name":"animationDirection","active":true},{"name":"animationDistance","active":true},{"name":"animationPieces","active":true},{"name":"animationOrigin","active":true},{"name":"animationFoldHeight","active":true},{"name":"animationHorizontalFirst","active":true},{"name":"animationIterations","active":true},{"name":"animationEasing","active":true},{"name":"animationDuration","active":true}]},{"formKey":"container-style","breakpoints":[],"activeFields":[{"name":"customStyle","active":true},{"name":"customStyle","active":true}]},{"formKey":"common-link","breakpoints":[],"activeFields":[{"name":"titleAttribute","active":true},{"name":"type","active":true},{"name":"linkToPage","active":true},{"name":"url","active":true},{"name":"anchor","active":true},{"name":"scrollToSelector","active":true},{"name":"scrollToDuration","active":true},{"name":"scrollToOffsetType","active":true},{"name":"scrollToOffsetPx","active":true},{"name":"scrollToOffsetElement","active":true},{"name":"target","active":true},{"name":"modifier","active":true},{"name":"interactionScope","active":true},{"name":"interactionParent","active":true},{"name":"interactionTarget","active":true},{"name":"modifierType","active":true},{"name":"modifierName","active":true}]}]}],"selectorType":"topLevel","form":null},"dropzone":[],"selectorType":"topLevel"}},"25526c76-3dc2-4461-8888-8a9727f61ab4":{"settings":{"topLevel":{"formDefinition":[{"formKey":"column-settings","children":[{"formKey":"column-width-and-position","breakpoints":[{"name":"xl"}],"activeFields":[{"name":"col","active":true},{"name":"pull","active":false},{"name":"push","active":false},{"name":"offset","active":false}]},{"formKey":"common-link-animation","breakpoints":[{"name":"xl"}],"activeFields":[{"name":"linkAnimation","active":true},{"name":"animationType","active":true},{"name":"animationScope","active":true},{"name":"animationParent","active":true},{"name":"animationTarget","active":true},{"name":"animationScale","active":true},{"name":"animationDirection","active":true},{"name":"animationDirection","active":true},{"name":"animationDirection","active":true},{"name":"animationDistance","active":true},{"name":"animationPieces","active":true},{"name":"animationOrigin","active":true},{"name":"animationFoldHeight","active":true},{"name":"animationHorizontalFirst","active":true},{"name":"animationIterations","active":true},{"name":"animationEasing","active":true},{"name":"animationDuration","active":true}]},{"formKey":"common-link","breakpoints":[],"activeFields":[{"name":"titleAttribute","active":true},{"name":"type","active":true},{"name":"linkToPage","active":true},{"name":"url","active":true},{"name":"anchor","active":true},{"name":"scrollToSelector","active":true},{"name":"scrollToDuration","active":true},{"name":"scrollToOffsetType","active":true},{"name":"scrollToOffsetPx","active":true},{"name":"scrollToOffsetElement","active":true},{"name":"target","active":true},{"name":"modifier","active":true},{"name":"interactionScope","active":true},{"name":"interactionParent","active":true},{"name":"interactionTarget","active":true},{"name":"modifierType","active":true},{"name":"modifierName","active":true}]}]}],"items":[],"title":"Settings","selectorType":"topLevel","form":null},"dropzone":[],"selectorType":"topLevel"}},"cb586ea4-4cbd-4e31-8d4e-901b4bc9fba4":{"settings":{"topLevel":{"formDefinition":[{"formKey":"button-settings","children":[{"formKey":"button-interaction","breakpoints":[],"activeFields":[{"name":"buttonText","active":true},{"name":"titleAttribute","active":true},{"name":"type","active":true},{"name":"scrollToSelector","active":true},{"name":"scrollToDuration","active":true},{"name":"scrollToOffsetType","active":true},{"name":"scrollToOffsetPx","active":true},{"name":"scrollToOffsetElement","active":true},{"name":"modifier","active":true},{"name":"interactionScope","active":true},{"name":"interactionParent","active":true},{"name":"interactionTarget","active":true},{"name":"modifierType","active":true},{"name":"modifierName","active":true}]},{"formKey":"button-animation","breakpoints":[{"name":"xl"}],"activeFields":[{"name":"buttonAnimation","active":true},{"name":"animationType","active":true},{"name":"animationScope","active":true},{"name":"animationParent","active":true},{"name":"animationTarget","active":true},{"name":"animationScale","active":true},{"name":"animationDirection","active":true},{"name":"animationDirection","active":true},{"name":"animationDirection","active":true},{"name":"animationDistance","active":true},{"name":"animationPieces","active":true},{"name":"animationOrigin","active":true},{"name":"animationFoldHeight","active":true},{"name":"animationHorizontalFirst","active":true},{"name":"animationIterations","active":true},{"name":"animationEasing","active":true},{"name":"animationDuration","active":true}]},{"formKey":"button-style","breakpoints":[],"activeFields":[{"name":"customStyle","active":true},{"name":"customStyle","active":true}]}]}],"selectorType":"topLevel","form":null},"dropzone":[],"selectorType":"topLevel"}},"2523c7ac-76e6-4084-999b-d65dd733eef8":{"settings":{"topLevel":{"formDefinition":[{"formKey":"slide-settings","children":[{"formKey":"common-link-animation","breakpoints":[{"name":"xl"}],"activeFields":[{"name":"linkAnimation","active":true},{"name":"animationType","active":true},{"name":"animationScope","active":true},{"name":"animationParent","active":true},{"name":"animationTarget","active":true},{"name":"animationScale","active":true},{"name":"animationDirection","active":true},{"name":"animationDirection","active":true},{"name":"animationDirection","active":true},{"name":"animationDistance","active":true},{"name":"animationPieces","active":true},{"name":"animationOrigin","active":true},{"name":"animationFoldHeight","active":true},{"name":"animationHorizontalFirst","active":true},{"name":"animationIterations","active":true},{"name":"animationEasing","active":true},{"name":"animationDuration","active":true}]},{"formKey":"slide-style","breakpoints":[],"activeFields":[{"name":"customStyle","active":true},{"name":"customStyle","active":true}]},{"formKey":"common-link","breakpoints":[],"activeFields":[{"name":"titleAttribute","active":true},{"name":"type","active":true},{"name":"linkToPage","active":true},{"name":"url","active":true},{"name":"anchor","active":true},{"name":"scrollToSelector","active":true},{"name":"scrollToDuration","active":true},{"name":"scrollToOffsetType","active":true},{"name":"scrollToOffsetPx","active":true},{"name":"scrollToOffsetElement","active":true},{"name":"target","active":true},{"name":"modifier","active":true},{"name":"interactionScope","active":true},{"name":"interactionParent","active":true},{"name":"interactionTarget","active":true},{"name":"modifierType","active":true},{"name":"modifierName","active":true}]}]}],"selectorType":"topLevel","form":null},"dropzone":[],"selectorType":"topLevel"}}},"previewModel":{"2523c7ac-76e6-4084-999b-d65dd733eef8":{},"cb586ea4-4cbd-4e31-8d4e-901b4bc9fba4":{},"25526c76-3dc2-4461-8888-8a9727f61ab4":{},"8a277676-bc4f-4bd7-ade9-01887623b237":{},"24f63711-0e8b-49fa-b32c-474b69cc8eaa":{}},"canvas":[{"type":"container","uid":"slide","title":"Slide","status":{"collapsed":false},"children":[],"parentIndex":3,"parentUid":"root","uuid":"2523c7ac-76e6-4084-999b-d65dd733eef8","isContainer":true},{"type":"item","uid":"button","title":"Button","status":{"collapsed":true},"parentIndex":3,"parentUid":"root","uuid":"cb586ea4-4cbd-4e31-8d4e-901b4bc9fba4","isContainer":false},{"type":"container","uid":"column","title":"Column","status":{"collapsed":false},"children":[],"breakpointClasses":"coh-layout-col-xl coh-layout-column-width","parentIndex":1,"parentUid":"root","uuid":"25526c76-3dc2-4461-8888-8a9727f61ab4","isContainer":true},{"type":"container","uid":"container","title":"Container","status":{"collapsed":false},"children":[],"parentIndex":1,"parentUid":"root","uuid":"8a277676-bc4f-4bd7-ade9-01887623b237","isContainer":true},{"type":"item","uid":"link","title":"Link","status":{"collapsed":true},"parentIndex":0,"parentUid":"root","uuid":"24f63711-0e8b-49fa-b32c-474b69cc8eaa","isContainer":false}]}';

  /**
   * {@inheritdoc}
   */
  public function setUp():void {
    $this->unit = new _0012EntityUpdate([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0012EntityUpdate::runUpdate
   */
  public function testRunUpdate() {

    // WYSIWYG in layout canvas.
    $layout = new _0012MockUpdateEntity($this->fixture_layout, TRUE);
    $this->assertionsLayoutCanvasBefore($layout->getDecodedJsonValues());
    $this->unit->runUpdate($layout);
    $this->assertionsLayoutCanvasAfter($layout->getDecodedJsonValues());
    $this->unit->runUpdate($layout);
    $this->assertionsLayoutCanvasAfter($layout->getDecodedJsonValues());
  }

  private function assertionsLayoutCanvasBefore($layout_array_before) {
    $this->assertEquals("animation", $layout_array_before['model']['2523c7ac-76e6-4084-999b-d65dd733eef8']['settings']['type'], 'Slide');
    $this->assertEquals("modifier", $layout_array_before['model']['cb586ea4-4cbd-4e31-8d4e-901b4bc9fba4']['settings']['type'], 'Button');
    $this->assertEquals("modifier", $layout_array_before['model']['25526c76-3dc2-4461-8888-8a9727f61ab4']['settings']['type'], 'Column');
    $this->assertEquals("animation", $layout_array_before['model']['8a277676-bc4f-4bd7-ade9-01887623b237']['settings']['type'], 'Container');
    $this->assertEquals("modifier", $layout_array_before['model']['24f63711-0e8b-49fa-b32c-474b69cc8eaa']['settings']['type'], 'Link');
  }

  private function assertionsLayoutCanvasAfter($layout_array_after) {
    $this->assertEquals("interaction", $layout_array_after['model']['2523c7ac-76e6-4084-999b-d65dd733eef8']['settings']['type'], 'Slide');
    $this->assertEquals("interaction", $layout_array_after['model']['cb586ea4-4cbd-4e31-8d4e-901b4bc9fba4']['settings']['type'], 'Button');
    $this->assertEquals("interaction", $layout_array_after['model']['25526c76-3dc2-4461-8888-8a9727f61ab4']['settings']['type'], 'Column');
    $this->assertEquals("interaction", $layout_array_after['model']['8a277676-bc4f-4bd7-ade9-01887623b237']['settings']['type'], 'Container');
    $this->assertEquals("interaction", $layout_array_after['model']['24f63711-0e8b-49fa-b32c-474b69cc8eaa']['settings']['type'], 'Link');

    $expected_slide_modifier = [
      'formKey' => 'common-link-modifier',
      'breakpoints' => [],
      'activeFields' => [
        [
          'name' => 'modifierType',
          'active' => TRUE,
        ],
      ],
    ];

    $this->assertEquals($expected_slide_modifier, $layout_array_after['mapper']['2523c7ac-76e6-4084-999b-d65dd733eef8']['settings']['topLevel']['formDefinition'][0]['children'][3], 'Slide');

    $expected_button = [
      'formKey' => 'button-modifier',
      'breakpoints' => [],
      'activeFields' => [
        [
          'name' => 'modifierType',
          'active' => TRUE,
        ],
      ],
    ];
    $this->assertEquals($expected_button, $layout_array_after['mapper']['cb586ea4-4cbd-4e31-8d4e-901b4bc9fba4']['settings']['topLevel']['formDefinition'][0]['children'][3], 'Button');

    $expected_col = [
      'formKey' => 'common-link-modifier',
      'breakpoints' => [],
      'activeFields' => [
        [
          'name' => 'modifierType',
          'active' => TRUE,
        ],
      ],
    ];
    $this->assertEquals($expected_col, $layout_array_after['mapper']['25526c76-3dc2-4461-8888-8a9727f61ab4']['settings']['topLevel']['formDefinition'][0]['children'][3], 'Column');

    $expected_container = [
      'formKey' => 'common-link-modifier',
      'breakpoints' => [],
      'activeFields' => [
        [
          'name' => 'modifierType',
          'active' => TRUE,
        ],
      ],
    ];
    $this->assertEquals($expected_container, $layout_array_after['mapper']['8a277676-bc4f-4bd7-ade9-01887623b237']['settings']['topLevel']['formDefinition'][0]['children'][4], 'Container');

    $expected_link = [
      'formKey' => 'link-modifier',
      'breakpoints' => [],
      'activeFields' => [
        [
          'name' => 'modifierType',
          'active' => TRUE,
        ],
      ],
    ];
    $this->assertEquals($expected_link, $layout_array_after['mapper']['24f63711-0e8b-49fa-b32c-474b69cc8eaa']['settings']['topLevel']['formDefinition'][0]['children'][3], 'Link');

  }

}
