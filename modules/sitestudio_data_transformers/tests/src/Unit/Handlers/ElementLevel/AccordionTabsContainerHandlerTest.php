<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel;

use Drupal\sitestudio_data_transformers\Handlers\ElementLevel\AccordionTabsContainerHandler;

/**
 * Test for accordion tabs container element handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel\AccordionTabsContainerHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\AccordionTabsContainerHandler
 */
class AccordionTabsContainerHandlerTest extends ElementHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"type":"container","uid":"accordion-tabs-container","title":"Accordion tabs container","status":{"collapsed":false},"uuid":"538f2680-ce2b-40db-9adf-2c83a4c453f8","parentUid":"root","children":[]}],"mapper":{"538f2680-ce2b-40db-9adf-2c83a4c453f8":{"settings":{"formDefinition":[{"formKey":"accordion-tabs-container-settings","children":[{"formKey":"accordion-tabs-container-accordion-or-tab","breakpoints":[{"name":"xl"}],"activeFields":[{"name":"accordionOrTab","active":true}]},{"formKey":"accordion-tabs-container-transition","breakpoints":[{"name":"xl"}],"activeFields":[{"name":"accordionTransition","active":true},{"name":"accordionTransitionSpeed","active":true},{"name":"accordionTransitionSpeedCustom","active":true}]},{"formKey":"accordion-tabs-container-start-state","breakpoints":[{"name":"xl"}],"activeFields":[{"name":"startCollapsed","active":true},{"name":"active","active":true}]}]},{"formKey":"accordion-tabs-container-accordion-accordion-settings","children":[{"formKey":"accordion-tabs-container-behavior","breakpoints":[],"activeFields":[{"name":"scrollToAccordion","active":true},{"name":"setHash","active":true}]},{"formKey":"accordion-tabs-container-offset-scroll-position","breakpoints":[{"name":"xl"}],"activeFields":[{"name":"offsetPositionAgainst","active":true},{"name":"accordionOffsetPx","active":true},{"name":"accordionOffsetClass","active":true}]}]},{"formKey":"accordion-tabs-container-tab-settings","children":[{"formKey":"accordion-tabs-container-position","breakpoints":[],"activeFields":[{"name":"horizontalVertical","active":true},{"name":"HorizontalPosition","active":true},{"name":"VerticalPosition","active":true},{"name":"accordionTabWidth","active":true},{"name":"accordionTabBleed","active":true}]},{"formKey":"accordion-tabs-container-width","breakpoints":[{"name":"xl"}],"activeFields":[{"name":"accordionTabWidth","active":true},{"name":"accordionTabBleed","active":true}]}]}],"selectorType":"topLevel","form":null,"items":[],"title":"Default"}}},"model":{"538f2680-ce2b-40db-9adf-2c83a4c453f8":{"settings":{"styles":{"xl":{"accordionOrTab":"accordion","collapsible":true,"startCollapsed":false,"animation":"slide","offsetPositionAgainst":"px","duration":700,"active":1,"scrollToAccordionOffset":0}},"scrollToAccordion":false,"setHash":false,"horizontalVertical":"horizontal_top","HorizontalPosition":"left_aligned","title":"Accordion tabs container"}}},"previewModel":{"538f2680-ce2b-40db-9adf-2c83a4c453f8":{}},"variableFields":{"538f2680-ce2b-40db-9adf-2c83a4c453f8":[]},"meta":{}}';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->handler = new AccordionTabsContainerHandler($this->moduleHandler);
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\AccordionTabsContainer::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'accordion-tabs-container',
      'id' => '538f2680-ce2b-40db-9adf-2c83a4c453f8',
      'data' => [
        'title' => 'Accordion tabs container'
      ],
    ];
    parent::testGetData();
  }


}
