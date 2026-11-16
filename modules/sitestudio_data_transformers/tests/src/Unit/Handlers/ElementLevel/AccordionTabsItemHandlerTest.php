<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel;

use Drupal\sitestudio_data_transformers\Handlers\ElementLevel\AccordionTabsItemHandler;

/**
 * Test for accordion tabs item element handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel\AccordionTabsItemHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\AccordionTabsItemHandler
 */
class AccordionTabsItemHandlerTest extends ElementHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"type":"container","uid":"accordion-tabs-item","title":"Accordion tabs item","status":{"collapsed":false},"iconColor":"interactive","uuid":"6ac17a66-aa43-4fcb-80fe-d2a0aaf69a3d","parentUid":"root","children":[]}],"mapper":{"6ac17a66-aa43-4fcb-80fe-d2a0aaf69a3d":{"settings":{"formDefinition":[{"formKey":"accordion-tabs-item-settings","children":[{"formKey":"accordion-tabs-item-settings-label","breakpoints":[],"activeFields":[]}]},{"formKey":"accordion-tabs-item-accordion-settings","children":[{"formKey":"accordion-tabs-item-accordion-html-element","breakpoints":[],"activeFields":[{"name":"accordionTabElement","active":true}]},{"formKey":"accordion-tabs-item-accordion-settings-style","breakpoints":[],"activeFields":[]}]},{"formKey":"accordion-tabs-item-tab-settings","children":[{"formKey":"accordion-tabs-item-tab-settings-tab-style","breakpoints":[],"activeFields":[]}]}],"selectorType":"topLevel","form":null,"items":[],"title":"Default"}}},"model":{"6ac17a66-aa43-4fcb-80fe-d2a0aaf69a3d":{"settings":{"accordionTabElement":"div","title":"Accordion tabs item","accordionStyle":{"customStyle":""},"tabStyle":{"customStyle":""},"settings":{"title":"Accordion item nav text"}}}},"previewModel":{"6ac17a66-aa43-4fcb-80fe-d2a0aaf69a3d":{}},"variableFields":{"6ac17a66-aa43-4fcb-80fe-d2a0aaf69a3d":[]},"meta":{}}';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->handler = new AccordionTabsItemHandler($this->moduleHandler);
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\AccordionTabsItem::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'accordion-tabs-item',
      'id' => '6ac17a66-aa43-4fcb-80fe-d2a0aaf69a3d',
      'data' => [
        'title' => 'Accordion tabs item'
      ],
    ];
    parent::testGetData();
  }


}
