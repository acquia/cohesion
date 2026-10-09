<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel;

use Drupal\sitestudio_data_transformers\Handlers\ElementLevel\ModalHandler;

/**
 * Test for modal element handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel\ModalHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\ModalHandler
 */
class ModalHandlerTest extends ElementHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"type":"container","uid":"modal","title":"Modal","status":{"collapsed":false},"uuid":"bc0a591f-dc0c-46e8-86c2-b76e53ddf7d4","parentUid":"root","children":[]}],"mapper":{"bc0a591f-dc0c-46e8-86c2-b76e53ddf7d4":{"settings":{"formDefinition":[{"formKey":"modal-settings","children":[{"formKey":"modal","breakpoints":[],"activeFields":[{"name":"modalPositionClass","active":true},{"name":"customStyle","active":true}]},{"formKey":"modal-close-button","breakpoints":[],"activeFields":[{"name":"showClose","active":true}]},{"formKey":"modal-animation","breakpoints":[],"activeFields":[{"name":"animationType","active":true}]},{"formKey":"modal-overlay","breakpoints":[],"activeFields":[{"name":"showOverlay","active":true}]},{"formKey":"modal-style","breakpoints":[],"activeFields":[{"name":"customStyle","active":true}]}]}],"selectorType":"topLevel","form":null,"items":[],"title":"Default"}}},"model":{"bc0a591f-dc0c-46e8-86c2-b76e53ddf7d4":{"settings":{"title":"Modal","customStyle":[{"customStyle":""}],"modalPositionClass":"coh-modal-center","autoOpen":false,"autoClose":false,"showClose":true,"animationType":"none","showOverlay":true,"modalCustomStyle":"","closePositionOutside":false,"closePositionClass":"coh-modal-close-top-right","clickToClose":true,"buttonCustomStyle":"","overlayCustomStyle":"","id":"modalid","buttonText":"button text"}}},"previewModel":{"bc0a591f-dc0c-46e8-86c2-b76e53ddf7d4":{}},"variableFields":{"bc0a591f-dc0c-46e8-86c2-b76e53ddf7d4":[]},"meta":{}}';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->handler = new ModalHandler($this->moduleHandler);
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\modal::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'modal',
      'id' => 'bc0a591f-dc0c-46e8-86c2-b76e53ddf7d4',
      'data' => [
        'title' => 'Modal',
        'id' => 'modalid',
        'buttonText' => 'button text',
      ],
    ];
    parent::testGetData();
  }


}
