<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel;

use Drupal\sitestudio_data_transformers\Handlers\ElementLevel\ButtonHandler;

/**
 * Test for button element handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel\ButtonHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\ButtonHandler
 */
class ButtonHandlerTest extends ElementHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"type":"container","uid":"button","title":"Button","status":{"collapsed":true},"uuid":"286f7727-60e8-4791-9236-c20017a3dd1b","parentUid":"root","children":[]}],"mapper":{"286f7727-60e8-4791-9236-c20017a3dd1b":{"settings":{"formDefinition":[{"formKey":"button-settings","children":[{"formKey":"button-interaction","breakpoints":[],"activeFields":[{"name":"buttonText","active":true},{"name":"titleAttribute","active":true},{"name":"type","active":true}]},{"formKey":"button-modifier","breakpoints":[],"activeFields":[{"name":"modifierType","active":true}]},{"formKey":"button-animation","breakpoints":[{"name":"xl"}],"activeFields":[{"name":"animationScope","active":true},{"name":"animationTarget","active":true},{"name":"animationType","active":true},{"name":"animationDuration","active":true}]},{"formKey":"button-style","breakpoints":[],"activeFields":[{"name":"customStyle","active":true}]}]}],"selectorType":"topLevel","form":null,"items":[],"title":"Default"}}},"model":{"286f7727-60e8-4791-9236-c20017a3dd1b":{"settings":{"title":"Button","customStyle":[{"customStyle":""}],"type":"interaction","modifier":[{"modifierType":"toggle-modifier","interactionScope":"document","interactionTarget":"#test","modifierName":"class"}],"styles":{"xl":{"buttonAnimation":[{"animationType":"none"}]}},"buttonText":"My button text","titleAttribute":"button title attribute"}}},"previewModel":{"286f7727-60e8-4791-9236-c20017a3dd1b":{}},"variableFields":{"286f7727-60e8-4791-9236-c20017a3dd1b":[]},"meta":{}}';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->handler = new ButtonHandler($this->moduleHandler);
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\Button::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'button',
      'id' => '286f7727-60e8-4791-9236-c20017a3dd1b',
      'data' => [
        'title' => 'Button',
        'buttonText' => 'My button text',
      ],
    ];
    parent::testGetData();
  }


}
