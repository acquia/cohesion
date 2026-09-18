<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel;

use Drupal\cohesion\Services\CohesionUtils;
use Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\LinkHandler;

/**
 * Test for link form field handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel\LinkHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\LinkHandler
 */
class LinkHandlerTest extends FormFieldHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"uid":"cpt_link_test","type":"component","title":"Link test","enabled":true,"category":"category-15","componentId":"cpt_link_test","uuid":"42a30e04-679a-40c9-a762-9da14c6c6b81","parentUid":"root","status":{},"children":[]}],"mapper":{"42a30e04-679a-40c9-a762-9da14c6c6b81":{}},"model":{"42a30e04-679a-40c9-a762-9da14c6c6b81":{"2184a670-b39b-4af7-869a-5c8ad64012ac":"node::6","settings":{"title":"Link test - edited name"}}},"previewModel":{"42a30e04-679a-40c9-a762-9da14c6c6b81":{}},"variableFields":{"42a30e04-679a-40c9-a762-9da14c6c6b81":[]},"meta":{}}';
  const COMPONENT = [
    'uuid' => 'cc783cb9-fcb4-4d9a-a87e-0463f2a659d7',
    'langcode' => 'en',
    'status' => true,
    'label' => 'Link test',
    'id' => 'cpt_link_test',
    'json_values' => '{"canvas":[],"componentForm":[{"type":"form-field","uid":"form-link","title":"Link to page","translate":true,"status":{"collapsed":false},"uuid":"2184a670-b39b-4af7-869a-5c8ad64012ac","parentUid":"root","children":[]}],"mapper":{"2184a670-b39b-4af7-869a-5c8ad64012ac":{}},"model":{"2184a670-b39b-4af7-869a-5c8ad64012ac":{"settings":{"isStyle":true,"type":"cohTypeahead","key":"linkToPage","title":"Link to page","placeholder":"Type page name","labelProperty":"name","valueProperty":"id","endpoint":"\/cohesionapi\/link-autocomplete?q=","schema":{"type":"string"},"machineName":"link-to-page","tooltipPlacement":"auto right","entityTypes":false},"model":{"value":"node::5"}}},"previewModel":{"2184a670-b39b-4af7-869a-5c8ad64012ac":{}},"variableFields":{"2184a670-b39b-4af7-869a-5c8ad64012ac":[]},"disabledNodes":[],"meta":{}}',
    'json_mapper' => '{}',
    'category' => 'cpt_cat_test',
  ];

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
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\LinkHandler::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'form-field',
      'id' => '2184a670-b39b-4af7-869a-5c8ad64012ac',
      'data' => [
        'uid' => 'form-link',
        'title' => 'Link to page',
        'value' => json_decode('{"url": "https://path.to/node/6"}'),
      ],
      'machine_name' => 'link-to-page',
    ];
    parent::testGetData();
  }


}
