<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel;

use Drupal\sitestudio_data_transformers\Handlers\ElementLevel\SliderContainerHandler;

/**
 * Test for slider container element handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel\SliderContainerHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\SliderContainerHandler
 */
class SliderContainerHandlerTest extends ElementHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"type":"container","uid":"slider-container","title":"Slider container","status":{"collapsed":false},"uuid":"82c64900-dacb-42c1-acf0-fafc44f6da59","parentUid":"root","children":[]}],"mapper":{"82c64900-dacb-42c1-acf0-fafc44f6da59":{"settings":{"formDefinition":[{"formKey":"slider-container-settings","children":[{"formKey":"slider-container-slide-container-style","breakpoints":[],"activeFields":[{"name":"customStyle","active":true}]}]},{"formKey":"slider-container-slide-layout","children":[{"formKey":"slider-container-width","breakpoints":[],"activeFields":[{"name":"containerWidth","active":true}]},{"formKey":"slider-container-bleed-and-overflow","breakpoints":[{"name":"xl"}],"activeFields":[{"name":"bleed","active":true},{"name":"overflowVisibility","active":true}]},{"formKey":"slider-container-slides-to-show-and-scroll","breakpoints":[{"name":"xl"}],"activeFields":[{"name":"slidesToShow","active":true},{"name":"slidesToScroll","active":true}]},{"formKey":"slider-container-slide-container-height","breakpoints":[{"name":"xl"}],"activeFields":[{"name":"slideContainerHeight","active":true}]},{"formKey":"slider-container-match-heights-of-children","breakpoints":[{"name":"xl"}],"activeFields":[{"name":"matchHeights.targetElement","active":true},{"name":"matchHeights.class","active":true},{"name":"matchHeights.targetLevel","active":true}]}]},{"formKey":"slider-container-navigation","children":[{"formKey":"slider-container-navigation-visibility","breakpoints":[{"name":"xl"}],"activeFields":[{"name":"showNavigation","active":true},{"name":"navigationPositionOutside","active":true},{"name":"navigationPositionInsideClass","active":true},{"name":"navigationPositionOutsideClass","active":true}]},{"formKey":"slider-container-navigation-style","breakpoints":[],"activeFields":[{"name":"navigationPreviousStyle","active":true},{"name":"navigationPreviousLabel","active":true},{"name":"navigationNextStyle","active":true},{"name":"navigationNextLabel","active":true}]},{"formKey":"slider-container-pagination-visibility","breakpoints":[{"name":"xl"}],"activeFields":[{"name":"showPagination","active":true},{"name":"paginationPositionClass","active":true}]},{"formKey":"slider-container-pagination-style","breakpoints":[],"activeFields":[{"name":"paginationCustomStyle","active":true},{"name":"paginationNumbers","active":true}]},{"formKey":"slider-container-drag-and-swipe","breakpoints":[{"name":"xl"}],"activeFields":[{"name":"enableDrag","active":true},{"name":"enableSwipe","active":true}]},{"formKey":"slider-container-keyboard-navigation","breakpoints":[],"activeFields":[{"name":"keyboardNavigation","active":true}]}]},{"formKey":"slider-container-transition-group","children":[{"formKey":"slider-container-transition","breakpoints":[{"name":"xl"}],"activeFields":[{"name":"transitionDirection","active":true},{"name":"transitionLoop","active":true},{"name":"transitionSpeed","active":true},{"name":"transitionSpeedCustom","active":true}]},{"formKey":"slider-container-easing","breakpoints":[{"name":"xl"}],"activeFields":[{"name":"easing","active":true},{"name":"easingCubicBezier","active":true},{"name":"easingSteps","active":true},{"name":"easingStepsTiming","active":true}]},{"formKey":"slider-container-autoplay","breakpoints":[{"name":"xl"}],"activeFields":[{"name":"autoplay","active":true},{"name":"autoplaySpeed","active":true},{"name":"autoplayPauseOnHover","active":true}]}]}],"selectorType":"topLevel","form":null,"items":[],"title":"Default"}}},"model":{"82c64900-dacb-42c1-acf0-fafc44f6da59":{"settings":{"containerWidth":"fluid","styles":{"xl":{"bleed":"retain","overflowVisibility":"hidden","slideContainerHeight":"tallestSlide","matchHeights":{"target":"none"},"showNavigation":true,"showPagination":true,"enableDrag":true,"enableSwipe":true,"transitionDirection":"leftRight","transitionLoop":"loop","transitionSpeed":700,"easing":"ease","autoplay":false,"navigationPositionOutside":false,"navigationPositionInsideClass":"coh-slider-container-nav-inside-top-left-right","paginationPositionOutside":false,"paginationPositionInsideClass":"coh-slider-container-pager-inside-bottom-middle","slidesToShow":1,"slidesToScroll":1}},"paginationNumbers":true,"title":"Slider container","customStyle":[{"customStyle":""}],"keyboardNavigation":true,"navigationPreviousStyle":"","navigationNextStyle":"","paginationCustomStyle":""}}},"previewModel":{"82c64900-dacb-42c1-acf0-fafc44f6da59":{}},"variableFields":{"82c64900-dacb-42c1-acf0-fafc44f6da59":[]},"meta":{}}';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->handler = new SliderContainerHandler($this->moduleHandler);
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\SliderContainer::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'slider-container',
      'id' => '82c64900-dacb-42c1-acf0-fafc44f6da59',
      'data' => [
        'title' => 'Slider container',
      ],
    ];
    parent::testGetData();
  }


}
