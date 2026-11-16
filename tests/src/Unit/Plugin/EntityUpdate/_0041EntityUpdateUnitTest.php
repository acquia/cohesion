<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\Plugin\EntityUpdate\_0041EntityUpdate;

/**
 * Class MockUpdateEntity.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class _0041MockUpdateEntity extends EntityMockBase implements EntityJsonValuesInterface {
}

/**
 * @group Cohesion
 */
class _0041EntityUpdateUnitTest extends EntityUpdateUnitTestCase {


  protected $unit;

  /**
   * Layout canvas fixture.
   *
   * @var string
   */
  private $fixture_layout = '{"model":{"2999306c-1bab-4537-9a20-9719dc7e19bc":{"settings":{"title":"Heading","element":"[field.e8f77e4e-3de9-47cc-b229-7040a4f78719]","customStyle":[{"customStyle":"[field.77d003da-6145-481e-95c8-2f6017361e26]"},{"customStyle":"[field.fa5d575b-1d6e-44f0-a99e-1a2f7688bc27]"},{"customStyle":"[field.42c2324a-e7ee-45b6-ab1e-f5dfba48bfb2]"},{"customStyle":"[field.fb51822d-28f0-4f47-8776-f7d715c910f5]"}],"settings":{"element":"h1","customStyle":[{"customStyle":""}]},"content":"[field.e73fdb4c-6fe8-4c41-a238-e4438b4e369b][field.50bae73f-99e4-446b-a738-9939a3dade60]"},"context-visibility":{"contextVisibility":{"condition":"ALL"}},"styles":{"settings":{"element":"heading"},"d23629b7-b212-4f61-900a-f0c5f8bee0f8":{"settings":{"element":"","class":".light-heading","combinator":"","pseudo":""},"styles":{"xl":{"color":{"value":{"name":"White","uid":"white","value":{"hex":"#ffffff","rgba":"rgba(255, 255, 255, 1)"},"wysiwyg":true,"class":".coh-color-white","variable":"$coh-color-white","inuse":true,"link":true,"tags":["Light"]}}}}},"42495060-522e-43b9-b7b6-23062c7e7827":{"settings":{"element":"","class":".color-heading","combinator":"","pseudo":""},"styles":{"xl":{"color":{"value":{"link":true,"name":"Brand color","uid":"brand-color","class":".coh-color-brand-color","variable":"$coh-color-brand-color","value":{"hex":"#26a3dd","rgba":"rgba(38, 163, 221, 1)"},"inuse":true,"wysiwyg":true,"tags":["Color"]}}}}},"d010d229-4b6b-4420-bf27-7bbbbb5516dd":{"settings":{"element":"","class":".dark-heading","combinator":"","pseudo":""},"styles":{"xl":{"color":{"value":{"value":{"hex":"#000000","rgba":"rgba(0, 0, 0, 1)"},"name":"Black","uid":"black","class":".coh-color-black","variable":"$coh-color-black","wysiwyg":true,"inuse":true,"link":true,"tags":["Dark"]}}}}},"styles":{"xl":{"display":{"value":"block"}}},"5014d526-de7b-4b04-90c5-b948655ddd87":{"settings":{"element":"","class":".align-text-left","combinator":"","pseudo":""},"styles":{"xl":{"text-align":{"value":"left"}}}},"6ae7508e-086d-4b91-bb25-4b2879008560":{"settings":{"element":"","class":".align-text-center","combinator":"","pseudo":""},"styles":{"xl":{"text-align":{"value":"center"}}}},"0b9e1620-5f28-4496-a9cc-6dbf0af7715f":{"settings":{"element":"","class":".align-text-right","combinator":"","pseudo":""},"styles":{"xl":{"text-align":{"value":"right"}}}}}},"42178f42-66bd-4303-b109-02c44d858af2":{"settings":{"type":"cohTabContainer","title":"Tab container","responsiveMode":true}},"0c9871e5-a70f-4c24-af0c-0126a3c9a878":{"settings":{"type":"cohTabItem","title":"Content","breakpointIcon":""},"contextVisibility":{"condition":"ALL"}},"85e04ce3-5885-4531-a707-5df0bbc0c19d":{"settings":{"title":"Heading","type":"cohSection","hideRowHeading":0,"columnCount":"1","breakpoints":false,"propertiesMenu":false,"disableScrollbar":true,"disableEllipsisMenu":true,"isOpen":true,"removePadding":0,"breakpointIcon":""},"contextVisibility":{"condition":"ALL"}},"ad025039-96c5-4cce-a27b-48778043471c":{"settings":{"title":"Use page title","type":"checkboxToggle","schema":{"type":"string"},"toggleType":"boolean","machineName":"use-page-title","tooltipPlacement":"auto right"},"contextVisibility":{"condition":"ALL"},"model":{"value":"{{falseValue}}"}},"e73fdb4c-6fe8-4c41-a238-e4438b4e369b":{"settings":{"title":"Page title","type":"cohHidden","schema":{"type":"string","escape":true},"machineName":"page-title","showCondition":"[field.ad025039-96c5-4cce-a27b-48778043471c]"},"contextVisibility":{"condition":"ALL"},"model":{"value":"[node:title]"}},"50bae73f-99e4-446b-a738-9939a3dade60":{"settings":{"title":"Heading","type":"cohTextarea","schema":{"type":"string","escape":true,"required":true},"machineName":"heading","tooltipPlacement":"auto right","validationMessage":{"302":"Please enter a heading."},"showCondition":"![field.ad025039-96c5-4cce-a27b-48778043471c]"},"contextVisibility":{"condition":"ALL"},"model":{"value":"Medium length placeholder heading."}},"e8f77e4e-3de9-47cc-b229-7040a4f78719":{"settings":{"title":"Heading element","type":"cohSelect","selectType":"custom","schema":{"type":"string"},"options":[{"label":"H1","value":"h1"},{"label":"H2","value":"h2"},{"label":"H3","value":"h3"},{"label":"H4","value":"h4"},{"label":"H5","value":"h5"},{"label":"H6","value":"h6"},{"label":"Span","value":"span"}],"machineName":"heading-element","tooltipPlacement":"auto right"},"contextVisibility":{"condition":"ALL"},"model":{"value":"h2"}},"332d6af2-66b8-4862-ba3b-0af4843bd94b":{"settings":{"title":"Layout and style","type":"cohTabItem","breakpointIcon":""},"contextVisibility":{"condition":"ALL"}},"817fbe40-927f-44c2-af1e-3fab74c11226":{"settings":{"title":"Heading size and alignment","type":"cohSection","hideRowHeading":0,"removePadding":0,"columnCount":"auto","breakpointIcon":"","breakpoints":false,"propertiesMenu":false,"disableScrollbar":true,"disableEllipsisMenu":true,"isOpen":true},"contextVisibility":{"condition":"ALL"}},"77d003da-6145-481e-95c8-2f6017361e26":{"settings":{"title":"Heading size override","type":"cohSelect","selectType":"custom","schema":{"type":"string"},"options":[{"label":"No override","value":""},{"label":"Pre heading","value":"coh-style-pre-heading"},{"label":"XL heading","value":"coh-style-heading-xl"},{"label":"Heading 1 size","value":"coh-style-heading-1-size"},{"label":"Heading 2 size","value":"coh-style-heading-2-size"},{"label":"Heading 3 size","value":"coh-style-heading-3-size"},{"label":"Heading 4 size","value":"coh-style-heading-4-size"},{"label":"Heading 5 size","value":"coh-style-heading-5-size"},{"label":"Heading 6 size","value":"coh-style-heading-6-size"}],"machineName":"heading-size-override","tooltipPlacement":"auto right"},"contextVisibility":{"condition":"ALL"},"model":{"value":""}},"42c2324a-e7ee-45b6-ab1e-f5dfba48bfb2":{"settings":{"title":"Align heading text","type":"cohSelect","selectType":"custom","schema":{"type":"string"},"options":[{"label":"Left","value":"align-text-left"},{"label":"Center","value":"align-text-center"},{"label":"Right","value":"align-text-right"}],"machineName":"align-heading-text","tooltipPlacement":"auto right"},"contextVisibility":{"condition":"ALL"},"model":{"value":"align-text-left"}},"cba4ddc5-275f-43fb-866d-97af512c8c1e":{"settings":{"title":"Heading color","type":"cohSection","hideRowHeading":0,"removePadding":0,"columnCount":"1","breakpointIcon":"","breakpoints":false,"propertiesMenu":false,"disableScrollbar":true,"disableEllipsisMenu":true,"isOpen":true},"contextVisibility":{"condition":"ALL"}},"fa5d575b-1d6e-44f0-a99e-1a2f7688bc27":{"settings":{"title":"Heading color","type":"cohSelect","selectType":"custom","schema":{"type":"string"},"options":[{"label":"Dark heading","value":"dark-heading"},{"label":"Light heading","value":"light-heading"},{"label":"Color heading","value":"color-heading"}],"tooltipPlacement":"auto right","machineName":"heading-color","showCondition":""},"contextVisibility":{"condition":"ALL"},"model":{"value":"dark-heading"}},"aafb1daa-4062-47f2-88b5-c57390f630b3":{"settings":{"title":"Space below","type":"cohSection","hideRowHeading":0,"removePadding":0,"columnCount":"1","breakpointIcon":"","breakpoints":false,"propertiesMenu":false,"disableScrollbar":true,"disableEllipsisMenu":true,"isOpen":true},"contextVisibility":{"condition":"ALL"}},"fb51822d-28f0-4f47-8776-f7d715c910f5":{"settings":{"title":"Add space below","type":"cohSelect","selectType":"custom","schema":{"type":"string"},"options":[{"label":"None","value":""},{"label":"Add space below","value":"coh-style-padding-bottom-small"}],"machineName":"add-space-below","tooltipPlacement":"auto right"},"contextVisibility":{"condition":"ALL"},"model":{"value":""}},"91d65346-6aa8-436d-807e-bb8d2f3fe499":{"settings":{"type":"cohTabItem","title":"Help","breakpointIcon":""},"contextVisibility":{"condition":"ALL"}},"2313fdf9-5ebd-4f72-97f8-7010ff53cd30":{"settings":{"title":"Help text","type":"cohHelpText","options":{"helpText":"# Using the Heading\n- Use the heading component to add a heading to a layout.\n- The heading component is best placed within a Layout or other container component.\n\n# Heading\n- **Use page title -** Toggling this **ON** will hide the **Heading** field and use the page title as the heading text.\n- **Heading -** Enter a heading. This field is required.\n- **Heading element -** Select the heading type from H1 to H6. You can also select **Span** if you require text to look like a heading but not use a heading element.\n\n# Heading size and alignment\n- **Heading size override -** Override the style of the heading with the style of a different heading element. For example, you can make a Heading 3 element look like a Heading 2 element.\n- **Align heading text -** Set the alignment of the heading text.\n\n# Heading color\n- **Heading color -** Select the heading color theme.\n\n# Space below\n- **Add space below -** Add additional space below the heading.","showClose":false,"helpType":"help"}},"contextVisibility":{"condition":"ALL"},"model":{}}}}';

  /**
   * {@inheritdoc}
   */
  public function setUp() : void {
    $this->unit = new _0041EntityUpdate([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0041EntityUpdate::runUpdate
   */
  public function testRunUpdate() {
    $component = new _0041MockUpdateEntity($this->fixture_layout, TRUE);
    $this->assertionsComponentBefore($component->getDecodedJsonValues());
    $this->unit->runUpdate($component);
    $this->assertionsComponentAfter($component->getDecodedJsonValues());
  }

  private function assertionsComponentBefore($layout_array_before) {
    $this->assertArrayHasKey("required", $layout_array_before['model']['50bae73f-99e4-446b-a738-9939a3dade60']['settings']['schema']);
  }

  private function assertionsComponentAfter($layout_array_after) {
    $this->assertArrayHasKey('required', $layout_array_after['model']['50bae73f-99e4-446b-a738-9939a3dade60']['settings']);
    $this->assertArrayNotHasKey('required', $layout_array_after['model']['50bae73f-99e4-446b-a738-9939a3dade60']['settings']['schema']);
  }
}
