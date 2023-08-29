<?php

namespace Drupal\Tests\cohesion\Unit\LayoutCanvas\LayoutCanvas;

use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\Tests\UnitTestCase;

/**
 * Tests for CohesionLayout entity dependency collection.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\cohesion\Unit\LayoutCanvas\LayoutCanvas
 *
 * @covers \Drupal\cohesion\LayoutCanvas\LayoutCanvas::extractLinks
 * @covers \Drupal\cohesion\LayoutCanvas\LayoutCanvas::getEntityReferences
 */
class LayoutCanvasTest extends UnitTestCase {

  /**
   * Tests extractLinks() method.
   *
   * @dataProvider providerExtractLinks
   */
  public function testExtractLinks($json_values, $expected_links) {
    $layout_canvas = new LayoutCanvas($json_values);
    $links = $layout_canvas->getLinksReferences();

    $this->assertEquals($expected_links, $links);
  }

  /**
   * Tests getEntityReferences() method.
   *
   * @dataProvider providerGetEntityReferences
   */
  public function testGetEntityReferences($json_values, $expected_references) {
    $layout_canvas = new LayoutCanvas($json_values);
    $references = $layout_canvas->getEntityReferences(TRUE, TRUE);

    $this->assertEquals($expected_references, $references);
  }

  /**
   * Provides data for testGetEntityReferences.
   *
   * @return array[]
   *   Array of data for testing [<json_values>, [<expected links references>].
   */
  public function providerExtractLinks(): array {
    return [
      [
        '{"canvas":[{"uid":"cpt_test_component","type":"component","title":"Test Component","enabled":true,"category":"category-10","componentId":"cpt_test_component","componentType":"link","uuid":"d23a59d5-c8d7-49b8-9cda-61fa01aadf55","parentUid":"root","children":[]},{"uid":"cpt_test_component","type":"component","title":"Test Component","enabled":true,"category":"category-10","componentId":"cpt_test_component","componentType":"link","uuid":"19c96fe4-db43-4f2f-8c91-8081b7a948fb","parentUid":"root","children":[]},{"uid":"cpt_test_component","type":"component","title":"Test Component","enabled":true,"category":"category-10","componentId":"cpt_test_component","componentType":"link","uuid":"ebafa6a9-b0b8-49cb-a481-d8c123c52e1c","parentUid":"root","children":[]},{"uid":"cpt_links_pattern_repeater","type":"component","title":"Links pattern repeater","enabled":true,"category":"category-4","componentId":"cpt_links_pattern_repeater","componentType":"component-pattern-repeater","uuid":"e7d1a1ac-abef-4971-9273-9d25bcc8db79","parentUid":"root","children":[]}],"mapper":{},"model":{"d23a59d5-c8d7-49b8-9cda-61fa01aadf55":{"settings":{"title":"Test Component"},"4793e582-fe13-490c-99d2-badcce843df7":"node::64"},"19c96fe4-db43-4f2f-8c91-8081b7a948fb":{"settings":{"title":"Test Component"},"4793e582-fe13-490c-99d2-badcce843df7":"node::56"},"ebafa6a9-b0b8-49cb-a481-d8c123c52e1c":{"settings":{"title":"Test Component"},"4793e582-fe13-490c-99d2-badcce843df7":"https:\/\/www.drupal.org\/"},"e7d1a1ac-abef-4971-9273-9d25bcc8db79":{"settings":{"title":"Links pattern repeater"},"d1653d6c-68ef-4b15-aabb-fdf3daf5064e":[{"f1a9f274-f030-47d5-a831-79a23da8a59e":"External","abdc6059-1b0d-498e-ac6a-daa58cbb85fb":"https:\/\/www.drupal.org\/"},{"f1a9f274-f030-47d5-a831-79a23da8a59e":"Node 1","abdc6059-1b0d-498e-ac6a-daa58cbb85fb":"node::4"},{"f1a9f274-f030-47d5-a831-79a23da8a59e":"Node 2","abdc6059-1b0d-498e-ac6a-daa58cbb85fb":"node::3"}]}},"previewModel":{"d23a59d5-c8d7-49b8-9cda-61fa01aadf55":{},"19c96fe4-db43-4f2f-8c91-8081b7a948fb":{},"ebafa6a9-b0b8-49cb-a481-d8c123c52e1c":{},"e7d1a1ac-abef-4971-9273-9d25bcc8db79":{}},"variableFields":{"d23a59d5-c8d7-49b8-9cda-61fa01aadf55":[],"19c96fe4-db43-4f2f-8c91-8081b7a948fb":[],"ebafa6a9-b0b8-49cb-a481-d8c123c52e1c":[],"e7d1a1ac-abef-4971-9273-9d25bcc8db79":[]},"meta":{"fieldHistory":[]}}',
        [
          "d23a59d5-c8d7-49b8-9cda-61fa01aadf55" => [
            [
              "entity_type" => "node",
              "entity_id" => "64",
              "path" => [
                "4793e582-fe13-490c-99d2-badcce843df7",
              ],
            ],
          ],
          "19c96fe4-db43-4f2f-8c91-8081b7a948fb" => [
            [
              "entity_type" => "node",
              "entity_id" => "56",
              "path" => [
                "4793e582-fe13-490c-99d2-badcce843df7",
              ],
            ],
          ],
          "e7d1a1ac-abef-4971-9273-9d25bcc8db79" => [
            [
              "entity_type" => "node",
              "entity_id" => "4",
              "path" => [
                "d1653d6c-68ef-4b15-aabb-fdf3daf5064e",
                1,
                "abdc6059-1b0d-498e-ac6a-daa58cbb85fb",
              ],
            ],
            [
              "entity_type" => "node",
              "entity_id" => "3",
              "path" => [
                "d1653d6c-68ef-4b15-aabb-fdf3daf5064e",
                2,
                "abdc6059-1b0d-498e-ac6a-daa58cbb85fb",
              ],
            ],
          ],
        ],
      ],
      [
        '{"canvas":[{"type":"item","uid":"entity-browser","title":"Entity browser","status":{"collapsed":true},"parentUid":"root","uuid":"9ca8de86-7039-439c-98ee-a67d93dfbc2c","children":[]},{"type":"item","uid":"entity-reference","title":"Entity reference","status":{"collapsed":true},"parentUid":"root","uuid":"4373aaeb-75e2-4cff-b129-67db720b0250","children":[]},{"type":"container","uid":"link","title":"Link","status":{"collapsed":false},"parentUid":"root","uuid":"d5f48344-f19d-46cc-bef9-567cc73cb1f9","children":[]}],"mapper":{"9ca8de86-7039-439c-98ee-a67d93dfbc2c":{"settings":{"formDefinition":[{"formKey":"entity-browser-settings","children":[{"formKey":"entity-browser","breakpoints":[],"activeFields":[{"name":"entity","active":true},{"name":"entityViewMode","active":true}]}]}],"selectorType":"topLevel","form":null,"items":[]}},"4373aaeb-75e2-4cff-b129-67db720b0250":{"settings":{"formDefinition":[{"formKey":"entity-reference-settings","children":[{"formKey":"entity-reference","breakpoints":[],"activeFields":[]}]}],"selectorType":"topLevel","form":null,"items":[]}},"d5f48344-f19d-46cc-bef9-567cc73cb1f9":{"settings":{"formDefinition":[{"formKey":"link-settings","children":[{"formKey":"link-link","breakpoints":[],"activeFields":[{"name":"linkText","active":true},{"name":"titleAttribute","active":true},{"name":"type","active":true},{"name":"linkToPage","active":true},{"name":"url","active":true},{"name":"anchor","active":true},{"name":"scrollToSelector","active":true},{"name":"scrollToDuration","active":true},{"name":"scrollToOffsetType","active":true},{"name":"scrollToOffsetPx","active":true},{"name":"scrollToOffsetElement","active":true},{"name":"target","active":true},{"name":"helpText","active":true},{"name":"triggerId","active":true},{"name":"modalId","active":true},{"name":"noFollow","active":true},{"name":"noOpener","active":true},{"name":"noReferrer","active":true}]},{"formKey":"link-modifier","breakpoints":[],"activeFields":[{"name":"modifier","active":true},{"name":"modifierType","active":true},{"name":"interactionScope","active":true},{"name":"interactionParent","active":true},{"name":"interactionTarget","active":true},{"name":"modifierName","active":true}]},{"formKey":"link-animation","breakpoints":[{"name":"xl"}],"activeFields":[{"name":"linkAnimation","active":true},{"name":"animationType","active":true},{"name":"animationScope","active":true},{"name":"animationParent","active":true},{"name":"animationTarget","active":true},{"name":"animationScale","active":true},{"name":"animationDirection","active":true},{"name":"animationDirection","active":true},{"name":"animationDirection","active":true},{"name":"animationDistance","active":true},{"name":"animationPieces","active":true},{"name":"animationOrigin","active":true},{"name":"animationFoldHeight","active":true},{"name":"animationHorizontalFirst","active":true},{"name":"animationIterations","active":true},{"name":"animationEasing","active":true},{"name":"animationDuration","active":true}]},{"formKey":"link-style","breakpoints":[],"activeFields":[{"name":"customStyle","active":true},{"name":"customStyle","active":true}]}]}],"selectorType":"topLevel","form":null,"items":[]}}},"model":{"9ca8de86-7039-439c-98ee-a67d93dfbc2c":{"settings":{"title":"Entity browser","entity":{"entityBrowserType":"typeahead","entityType":"node","entity":{"entityType":"node","entityId":"54662300-21b6-4ed9-84e6-1d6955c6d7c9"},"bundles":{"page":true}},"entityViewMode":"node.teaser"},"context-visibility":{"contextVisibility":{"condition":"ALL"}},"styles":{"settings":{"element":"entity-browser"}}},"4373aaeb-75e2-4cff-b129-67db720b0250":{"settings":{"title":"Entity reference","entityReference":{"entity_type":"node","view_mode":"teaser","entity":"7a1b32fd-3865-4851-99e5-6b753dbe95da"}},"context-visibility":{"contextVisibility":{"condition":"ALL"}},"styles":{"settings":{"element":"entity-reference"}}},"d5f48344-f19d-46cc-bef9-567cc73cb1f9":{"settings":{"title":"Link","customStyle":[{"customStyle":""}],"settings":{"type":"internal-page","target":"_self","customStyle":[{"customStyle":""}]},"type":"internal-page","target":"_self","linkText":"Test link element","linkToPage":"node::8"},"context-visibility":{"contextVisibility":{"condition":"ALL"}},"styles":{"settings":{"element":"link"}}}},"previewModel":{"9ca8de86-7039-439c-98ee-a67d93dfbc2c":{},"4373aaeb-75e2-4cff-b129-67db720b0250":{},"d5f48344-f19d-46cc-bef9-567cc73cb1f9":{}},"variableFields":{"9ca8de86-7039-439c-98ee-a67d93dfbc2c":[],"4373aaeb-75e2-4cff-b129-67db720b0250":[],"d5f48344-f19d-46cc-bef9-567cc73cb1f9":[]},"meta":{"fieldHistory":[]}}',
        [
          "d5f48344-f19d-46cc-bef9-567cc73cb1f9" => [
            [
              "entity_type" => "node",
              "entity_id" => "8",
              "path" => [
                "settings",
                "linkToPage",
              ],
            ],
          ],
        ],
      ],
    ];
  }

  /**
   * Provides data for testGetEntityReferences.
   *
   * @return array[]
   *   Array of data for testing [<json_values>, [<expected entity references>].
   */
  public function providerGetEntityReferences() {
    return [
      [
        '{"canvas":[{"uid":"cpt_test_component","type":"component","title":"Test Component","enabled":true,"category":"category-10","componentId":"cpt_test_component","componentType":"link","uuid":"d23a59d5-c8d7-49b8-9cda-61fa01aadf55","parentUid":"root","children":[]},{"uid":"cpt_test_component","type":"component","title":"Test Component","enabled":true,"category":"category-10","componentId":"cpt_test_component","componentType":"link","uuid":"19c96fe4-db43-4f2f-8c91-8081b7a948fb","parentUid":"root","children":[]},{"uid":"cpt_test_component","type":"component","title":"Test Component","enabled":true,"category":"category-10","componentId":"cpt_test_component","componentType":"link","uuid":"ebafa6a9-b0b8-49cb-a481-d8c123c52e1c","parentUid":"root","children":[]},{"uid":"cpt_links_pattern_repeater","type":"component","title":"Links pattern repeater","enabled":true,"category":"category-4","componentId":"cpt_links_pattern_repeater","componentType":"component-pattern-repeater","uuid":"e7d1a1ac-abef-4971-9273-9d25bcc8db79","parentUid":"root","children":[]}],"mapper":{},"model":{"d23a59d5-c8d7-49b8-9cda-61fa01aadf55":{"settings":{"title":"Test Component"},"4793e582-fe13-490c-99d2-badcce843df7":"node::64"},"19c96fe4-db43-4f2f-8c91-8081b7a948fb":{"settings":{"title":"Test Component"},"4793e582-fe13-490c-99d2-badcce843df7":"node::56"},"ebafa6a9-b0b8-49cb-a481-d8c123c52e1c":{"settings":{"title":"Test Component"},"4793e582-fe13-490c-99d2-badcce843df7":"https:\/\/www.drupal.org\/"},"e7d1a1ac-abef-4971-9273-9d25bcc8db79":{"settings":{"title":"Links pattern repeater"},"d1653d6c-68ef-4b15-aabb-fdf3daf5064e":[{"f1a9f274-f030-47d5-a831-79a23da8a59e":"External","abdc6059-1b0d-498e-ac6a-daa58cbb85fb":"https:\/\/www.drupal.org\/"},{"f1a9f274-f030-47d5-a831-79a23da8a59e":"Node 1","abdc6059-1b0d-498e-ac6a-daa58cbb85fb":"node::4"},{"f1a9f274-f030-47d5-a831-79a23da8a59e":"Node 2","abdc6059-1b0d-498e-ac6a-daa58cbb85fb":"node::3"}]}},"previewModel":{"d23a59d5-c8d7-49b8-9cda-61fa01aadf55":{},"19c96fe4-db43-4f2f-8c91-8081b7a948fb":{},"ebafa6a9-b0b8-49cb-a481-d8c123c52e1c":{},"e7d1a1ac-abef-4971-9273-9d25bcc8db79":{}},"variableFields":{"d23a59d5-c8d7-49b8-9cda-61fa01aadf55":[],"19c96fe4-db43-4f2f-8c91-8081b7a948fb":[],"ebafa6a9-b0b8-49cb-a481-d8c123c52e1c":[],"e7d1a1ac-abef-4971-9273-9d25bcc8db79":[]},"meta":{"fieldHistory":[]}}',
        [
          [
            "entity_type" => "node",
            "entity_id" => "64",
          ],
          [
            "entity_type" => "node",
            "entity_id" => "56",
          ],
          [
            "entity_type" => "node",
            "entity_id" => "4",
          ],
          [
            "entity_type" => "node",
            "entity_id" => "3",
          ],
        ],
      ],
      [
        '{"canvas":[{"type":"item","uid":"entity-browser","title":"Entity browser","status":{"collapsed":true},"parentUid":"root","uuid":"9ca8de86-7039-439c-98ee-a67d93dfbc2c","children":[]},{"type":"item","uid":"entity-reference","title":"Entity reference","status":{"collapsed":true},"parentUid":"root","uuid":"4373aaeb-75e2-4cff-b129-67db720b0250","children":[]},{"type":"container","uid":"link","title":"Link","status":{"collapsed":false},"parentUid":"root","uuid":"d5f48344-f19d-46cc-bef9-567cc73cb1f9","children":[]}],"mapper":{"9ca8de86-7039-439c-98ee-a67d93dfbc2c":{"settings":{"formDefinition":[{"formKey":"entity-browser-settings","children":[{"formKey":"entity-browser","breakpoints":[],"activeFields":[{"name":"entity","active":true},{"name":"entityViewMode","active":true}]}]}],"selectorType":"topLevel","form":null,"items":[]}},"4373aaeb-75e2-4cff-b129-67db720b0250":{"settings":{"formDefinition":[{"formKey":"entity-reference-settings","children":[{"formKey":"entity-reference","breakpoints":[],"activeFields":[]}]}],"selectorType":"topLevel","form":null,"items":[]}},"d5f48344-f19d-46cc-bef9-567cc73cb1f9":{"settings":{"formDefinition":[{"formKey":"link-settings","children":[{"formKey":"link-link","breakpoints":[],"activeFields":[{"name":"linkText","active":true},{"name":"titleAttribute","active":true},{"name":"type","active":true},{"name":"linkToPage","active":true},{"name":"url","active":true},{"name":"anchor","active":true},{"name":"scrollToSelector","active":true},{"name":"scrollToDuration","active":true},{"name":"scrollToOffsetType","active":true},{"name":"scrollToOffsetPx","active":true},{"name":"scrollToOffsetElement","active":true},{"name":"target","active":true},{"name":"helpText","active":true},{"name":"triggerId","active":true},{"name":"modalId","active":true},{"name":"noFollow","active":true},{"name":"noOpener","active":true},{"name":"noReferrer","active":true}]},{"formKey":"link-modifier","breakpoints":[],"activeFields":[{"name":"modifier","active":true},{"name":"modifierType","active":true},{"name":"interactionScope","active":true},{"name":"interactionParent","active":true},{"name":"interactionTarget","active":true},{"name":"modifierName","active":true}]},{"formKey":"link-animation","breakpoints":[{"name":"xl"}],"activeFields":[{"name":"linkAnimation","active":true},{"name":"animationType","active":true},{"name":"animationScope","active":true},{"name":"animationParent","active":true},{"name":"animationTarget","active":true},{"name":"animationScale","active":true},{"name":"animationDirection","active":true},{"name":"animationDirection","active":true},{"name":"animationDirection","active":true},{"name":"animationDistance","active":true},{"name":"animationPieces","active":true},{"name":"animationOrigin","active":true},{"name":"animationFoldHeight","active":true},{"name":"animationHorizontalFirst","active":true},{"name":"animationIterations","active":true},{"name":"animationEasing","active":true},{"name":"animationDuration","active":true}]},{"formKey":"link-style","breakpoints":[],"activeFields":[{"name":"customStyle","active":true},{"name":"customStyle","active":true}]}]}],"selectorType":"topLevel","form":null,"items":[]}}},"model":{"9ca8de86-7039-439c-98ee-a67d93dfbc2c":{"settings":{"title":"Entity browser","entity":{"entityBrowserType":"typeahead","entityType":"node","entity":{"entityType":"node","entityId":"54662300-21b6-4ed9-84e6-1d6955c6d7c9"},"bundles":{"page":true}},"entityViewMode":"node.teaser"},"context-visibility":{"contextVisibility":{"condition":"ALL"}},"styles":{"settings":{"element":"entity-browser"}}},"4373aaeb-75e2-4cff-b129-67db720b0250":{"settings":{"title":"Entity reference","entityReference":{"entity_type":"node","view_mode":"teaser","entity":"7a1b32fd-3865-4851-99e5-6b753dbe95da"}},"context-visibility":{"contextVisibility":{"condition":"ALL"}},"styles":{"settings":{"element":"entity-reference"}}},"d5f48344-f19d-46cc-bef9-567cc73cb1f9":{"settings":{"title":"Link","customStyle":[{"customStyle":""}],"settings":{"type":"internal-page","target":"_self","customStyle":[{"customStyle":""}]},"type":"internal-page","target":"_self","linkText":"Test link element","linkToPage":"node::8"},"context-visibility":{"contextVisibility":{"condition":"ALL"}},"styles":{"settings":{"element":"link"}}}},"previewModel":{"9ca8de86-7039-439c-98ee-a67d93dfbc2c":{},"4373aaeb-75e2-4cff-b129-67db720b0250":{},"d5f48344-f19d-46cc-bef9-567cc73cb1f9":{}},"variableFields":{"9ca8de86-7039-439c-98ee-a67d93dfbc2c":[],"4373aaeb-75e2-4cff-b129-67db720b0250":[],"d5f48344-f19d-46cc-bef9-567cc73cb1f9":[]},"meta":{"fieldHistory":[]}}',
        [
          [
            "entity_type" => "node",
            "entity_id" => "54662300-21b6-4ed9-84e6-1d6955c6d7c9",
          ],
          [
            "entity_type" => "node",
            "entity_id" => "7a1b32fd-3865-4851-99e5-6b753dbe95da",
          ],
          [
            "entity_type" => "node",
            "entity_id" => "8",
          ],
        ],
      ],
      [
        '{"canvas":[{"uid":"cpt_field_repeater","type":"component","title":"Field repeater","enabled":true,"category":"category-10","componentId":"cpt_field_repeater","componentType":"misc","uuid":"0482d5c6-7657-4771-aab6-9499084c6711","parentUid":"root","children":[]}],"mapper":{},"model":{"0482d5c6-7657-4771-aab6-9499084c6711":{"settings":{"title":"Field repeater"},"45ed214a-8fbe-4be2-bf56-dc132ad1bc83":{"entity_type":"node","view_mode":"teaser","entity":"717be810-5ff6-4617-a76c-cc2a97e40638"},"9dcda97e-6270-48d0-9f5e-6387a6d9a880":[{"20291f5f-c25e-41b6-836b-4efcf17a454b":{"entity_type":"node","view_mode":"teaser","entity":"63e7b315-66cb-4389-bfd4-79d16f07f483"}},{"20291f5f-c25e-41b6-836b-4efcf17a454b":{"entity_type":"node","view_mode":"token","entity":"7acc6f66-be64-4822-a3fb-71974c76e8b8"}}]}},"previewModel":{"0482d5c6-7657-4771-aab6-9499084c6711":{}},"variableFields":{"0482d5c6-7657-4771-aab6-9499084c6711":[]},"meta":{"fieldHistory":[]}}',
        [
          [
            "entity_type" => "node",
            "entity_id" => "717be810-5ff6-4617-a76c-cc2a97e40638",
          ],
          [
            "entity_type" => "node",
            "entity_id" => "63e7b315-66cb-4389-bfd4-79d16f07f483",
          ],
          [
            "entity_type" => "node",
            "entity_id" => "7acc6f66-be64-4822-a3fb-71974c76e8b8",
          ],
        ],
      ],
      [
        '{"canvas":[{"uid":"cpt_entity_browser","type":"component","title":"Entity browser","enabled":true,"category":"category-10","componentId":"cpt_entity_browser","componentType":"misc","uuid":"898825e9-9427-4dd6-a250-57bb0c4164b6","parentUid":"root","children":[]}],"mapper":{},"model":{"898825e9-9427-4dd6-a250-57bb0c4164b6":{"settings":{"title":"Entity browser"},"d7cd59a9-4704-4f54-b458-28474e578b4f":{"entity":{"entityType":"media","entityId":"f038e0dd-7c3a-4064-b33c-10408c928ee9"}},"2f8eb2df-199e-49ed-bbb1-2f8b01873c72":[{"71673598-f637-467f-bed3-857bf1805520":{"entity":{"entityType":"media","entityId":"845b17f7-edd8-4125-85b7-d22372145a95"}}},{"71673598-f637-467f-bed3-857bf1805520":{"entity":{"entityType":"media","entityId":"a83e4dec-753d-4e88-bc83-48c8a9ba350e"}}}]}},"previewModel":{"898825e9-9427-4dd6-a250-57bb0c4164b6":{}},"variableFields":{"898825e9-9427-4dd6-a250-57bb0c4164b6":[]},"meta":{"fieldHistory":[]}}',
        [
          [
            "entity_type" => "media",
            "entity_id" => "f038e0dd-7c3a-4064-b33c-10408c928ee9",
          ],
          [
            "entity_type" => "media",
            "entity_id" => "845b17f7-edd8-4125-85b7-d22372145a95",
          ],
          [
            "entity_type" => "media",
            "entity_id" => "a83e4dec-753d-4e88-bc83-48c8a9ba350e",
          ],
        ],
      ],
      [
        '{"canvas":[{"uid":"cpt_entity_browser","type":"component","title":"Entity browser","enabled":true,"category":"category-10","componentId":"cpt_entity_browser","componentType":"misc","uuid":"ad7b25ff-b5c5-49db-8099-989068c47833","parentUid":"root","children":[]}],"mapper":{},"model":{"ad7b25ff-b5c5-49db-8099-989068c47833":{"settings":{"title":"Entity browser"},"d7cd59a9-4704-4f54-b458-28474e578b4f":{"entity":{"entityId":"874ebc49-318d-44f0-a44c-a43aca84848a","entityUUID":"874ebc49-318d-44f0-a44c-a43aca84848a","entityType":"media"}},"2f8eb2df-199e-49ed-bbb1-2f8b01873c72":[{"71673598-f637-467f-bed3-857bf1805520":{"entity":{"entityId":"313b3d10-8026-46c7-8ba0-0b01dadc426f","entityUUID":"313b3d10-8026-46c7-8ba0-0b01dadc426f","entityType":"media"}}},{"71673598-f637-467f-bed3-857bf1805520":{"entity":{"entityId":"b16f102e-630d-4833-b234-432408efc2d7","entityUUID":"b16f102e-630d-4833-b234-432408efc2d7","entityType":"media"}}}]}},"previewModel":{"ad7b25ff-b5c5-49db-8099-989068c47833":{}},"variableFields":{"ad7b25ff-b5c5-49db-8099-989068c47833":[]},"meta":{"fieldHistory":[]}}',
        [
          [
            "entity_type" => "media",
            "entity_id" => "874ebc49-318d-44f0-a44c-a43aca84848a",
          ],
          [
            "entity_type" => "media",
            "entity_id" => "313b3d10-8026-46c7-8ba0-0b01dadc426f",
          ],
          [
            "entity_type" => "media",
            "entity_id" => "b16f102e-630d-4833-b234-432408efc2d7",
          ],
        ],
      ],
      [
        '{"canvas":[{"uid":"cpt_image_uploader","type":"component","title":"Image uploader","enabled":true,"category":"category-10","componentId":"cpt_image_uploader","componentType":"misc","uuid":"bee603f3-8898-4506-96e7-9b8ce4f504e5","parentUid":"root","children":[]}],"mapper":{},"model":{"bee603f3-8898-4506-96e7-9b8ce4f504e5":{"settings":{"title":"Image uploader"},"78c7b4f5-b0d8-459b-b839-c4dbb4aa8bf6":"[media-reference:file:651e3fd9-c506-4a31-a10f-aa2cd0b0e4a8]","ea7e0533-c988-4b63-a461-7b336bf414c1":[{"aef481c7-b179-409d-91a6-0b7fedfc9d45":"[media-reference:file:574d64df-ae79-46c1-95b3-f256f998a76c]"},{"aef481c7-b179-409d-91a6-0b7fedfc9d45":"[media-reference:file:c5ec227e-df6e-41ac-b565-d3dfd7c20a16]"}]}},"previewModel":{"bee603f3-8898-4506-96e7-9b8ce4f504e5":{}},"variableFields":{"bee603f3-8898-4506-96e7-9b8ce4f504e5":[]},"meta":{"fieldHistory":[]}}',
        [
          [
            "entity_type" => "file",
            "entity_id" => "651e3fd9-c506-4a31-a10f-aa2cd0b0e4a8",
          ],
          [
            "entity_type" => "file",
            "entity_id" => "574d64df-ae79-46c1-95b3-f256f998a76c",
          ],
          [
            "entity_type" => "file",
            "entity_id" => "c5ec227e-df6e-41ac-b565-d3dfd7c20a16",
          ],
        ],
      ],
      [
        '{"canvas":[{"uid":"cpt_image_uploader","type":"component","title":"Image uploader","enabled":true,"category":"category-10","componentId":"cpt_image_uploader","componentType":"misc","uuid":"4af809ee-b17e-4bda-b291-c6236e367e5a","parentUid":"root","children":[]}],"mapper":{},"model":{"4af809ee-b17e-4bda-b291-c6236e367e5a":{"settings":{"title":"Image uploader"},"78c7b4f5-b0d8-459b-b839-c4dbb4aa8bf6":"[media-reference:file:8935d3c1-3308-4703-8e93-625a33e5e1d4]","ea7e0533-c988-4b63-a461-7b336bf414c1":[{"aef481c7-b179-409d-91a6-0b7fedfc9d45":"[media-reference:file:d8373487-0e35-4b05-8aec-31299e686dd3]"},{"aef481c7-b179-409d-91a6-0b7fedfc9d45":"[media-reference:file:aacc5a3f-2476-40d7-9124-8840c2badd0c]"}]}},"previewModel":{"4af809ee-b17e-4bda-b291-c6236e367e5a":{}},"variableFields":{"4af809ee-b17e-4bda-b291-c6236e367e5a":[]},"meta":{"fieldHistory":[]}}',
        [
          [
            "entity_type" => "file",
            "entity_id" => "8935d3c1-3308-4703-8e93-625a33e5e1d4",
          ],
          [
            "entity_type" => "file",
            "entity_id" => "d8373487-0e35-4b05-8aec-31299e686dd3",
          ],
          [
            "entity_type" => "file",
            "entity_id" => "aacc5a3f-2476-40d7-9124-8840c2badd0c",
          ],
        ],
      ],
    ];
  }

}
