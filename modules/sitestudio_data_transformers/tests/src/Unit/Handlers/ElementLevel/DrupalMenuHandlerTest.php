<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel;

use Drupal\sitestudio_data_transformers\Handlers\ElementLevel\DrupalMenuHandler;

/**
 * Test for drupal menu element handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel\DrupalMenuHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\DrupalMenuHandler
 */
class DrupalMenuHandlerTest extends ElementHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"type":"item","uid":"drupal-menu","title":"Menu","status":{"collapsed":true},"uuid":"0aa0479c-2b01-4ae2-a872-ba5ef6fc34c6","parentUid":"root","children":[]}],"mapper":{"0aa0479c-2b01-4ae2-a872-ba5ef6fc34c6":{"settings":{"formDefinition":[{"formKey":"drupal-menu-settings","children":[{"formKey":"drupal-menu-menu","breakpoints":[],"activeFields":[{"name":"content","active":true}]},{"formKey":"drupal-menu-template","breakpoints":[],"activeFields":[]}]}],"selectorType":"topLevel","form":null,"items":[],"title":"Default"}}},"model":{"0aa0479c-2b01-4ae2-a872-ba5ef6fc34c6":{"settings":{"title":"Menu","settings":{"menu":{"id":"main","template":"menu_tpl_main_navigation","onlyRenderActiveTrail":true,"startLevel":"1"}}}}},"previewModel":{"0aa0479c-2b01-4ae2-a872-ba5ef6fc34c6":{}},"variableFields":{"0aa0479c-2b01-4ae2-a872-ba5ef6fc34c6":[]},"meta":{}}';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->handler = new DrupalMenuHandler($this->moduleHandler);
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\DrupalMenu::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'drupal-menu',
      'id' => '0aa0479c-2b01-4ae2-a872-ba5ef6fc34c6',
      'data' => [
        'title' => 'Menu',
        'settings' =>  json_decode('{"menu":{"id":"main","template":"menu_tpl_main_navigation","onlyRenderActiveTrail":true,"startLevel":"1"}}'),
      ],
    ];
    parent::testGetData();
  }


}
