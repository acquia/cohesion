<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel;

use Drupal\cohesion\Services\CohesionUtils;
use Drupal\sitestudio_data_transformers\Handlers\ElementLevel\LinkHandler;

/**
 * Test for link element handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel\LinkHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\LinkHandler
 */
class LinkHandlerTest extends ElementHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"type":"container","uid":"link","title":"Link","status":{"collapsed":true},"uuid":"951bf217-50e5-4151-9b15-68e1c3d4af3f","parentUid":"root","children":[]}],"mapper":{"951bf217-50e5-4151-9b15-68e1c3d4af3f":{"settings":{"formDefinition":[{"formKey":"link-settings","children":[{"formKey":"link-link","breakpoints":[],"activeFields":[{"name":"titleAttribute","active":true}]},{"formKey":"link-modifier","breakpoints":[],"activeFields":[{"name":"modifierType","active":true}]},{"formKey":"link-animation","breakpoints":[{"name":"xl"}],"activeFields":[{"name":"animationType","active":true}]},{"formKey":"link-style","breakpoints":[],"activeFields":[{"name":"customStyle","active":true}]}]}],"selectorType":"topLevel","form":null,"items":[],"title":"Default"}}},"model":{"951bf217-50e5-4151-9b15-68e1c3d4af3f":{"settings":{"title":"Link","customStyle":[{"customStyle":""}],"type":"internal-page","target":"_self","linkText":"my link text","titleAttribute":"link title","linkToPage":"node::2"}}},"previewModel":{"951bf217-50e5-4151-9b15-68e1c3d4af3f":{}},"variableFields":{"951bf217-50e5-4151-9b15-68e1c3d4af3f":[]},"meta":{}}';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $cohesionUtils = $this->getMockBuilder(CohesionUtils::class)
      ->disableOriginalConstructor()
      ->getMock();
    $cohesionUtils->expects($this->any())->method('urlProcessor')
      ->willReturnCallback(function ($argument) {
        if (filter_var($argument, FILTER_VALIDATE_URL)) {
          return $argument;
        }
        $entity_data = explode('::', (string) $argument);
        return 'https://path.to/' . implode('/', $entity_data);
      });
    $this->handler = new LinkHandler(
      $this->moduleHandler,
      $cohesionUtils,
      $this->getUrlGeneratorMock(),
      $this->getEntityTypeManagerMock(),
      $this->getResourceTypeManagerMock()
    );
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\Link::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'link',
      'id' => '951bf217-50e5-4151-9b15-68e1c3d4af3f',
      'data' => [
        'title' => 'Link',
        'type' => 'internal-page',
        'target' => '_self',
        'text' => 'my link text',
        'value' => json_decode('{"url": "https://path.to/node/2"}'),
      ],
    ];
    parent::testGetData();
  }


}
