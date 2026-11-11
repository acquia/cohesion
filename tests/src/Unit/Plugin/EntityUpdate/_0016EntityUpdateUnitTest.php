<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\Plugin\EntityUpdate\_0016EntityUpdate;

/**
 * Class MockUpdateEntity.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class _0016MockUpdateEntity extends EntityMockBase implements EntityJsonValuesInterface {
}

/**
 * @group Cohesion
 */
class _0016EntityUpdateUnitTest extends EntityUpdateUnitTestCase {

  /**
   * @var unit_0016MockUpdateEntity*/
  protected $unit;

  private $fixture_layout_false = '{"model":{"8e8ef97d-455a-41df-b193-711725d7bc2f":{"settings":{"title":"Color picker","type":"cohColourPickerOpener","colourPickerOptions":{"flat":true,"showOnly":""},"schema":{"type":"object"},"allowAll":false},"contextVisibility":{"condition":"ALL"}}},"mapper":{},"previewModel":{"8e8ef97d-455a-41df-b193-711725d7bc2f":{}},"variableFields":{"8e8ef97d-455a-41df-b193-711725d7bc2f":[]},"canvas":[],"componentForm":[{"type":"form-field","uid":"form-colorpicker","title":"Color picker","parentIndex":"form-fields","status":{"collapsed":false},"parentUid":"root","uuid":"8e8ef97d-455a-41df-b193-711725d7bc2f","humanId":"Field 1","isContainer":false}]}';
  private $fixture_layout_true = '{"model":{"8e8ef97d-455a-41df-b193-711725d7bc2f":{"settings":{"title":"Color picker","type":"cohColourPickerOpener","colourPickerOptions":{"flat":true,"showOnly":""},"schema":{"type":"object"},"allowAll":true},"contextVisibility":{"condition":"ALL"}}},"mapper":{},"previewModel":{"8e8ef97d-455a-41df-b193-711725d7bc2f":{}},"variableFields":{"8e8ef97d-455a-41df-b193-711725d7bc2f":[]},"canvas":[],"componentForm":[{"type":"form-field","uid":"form-colorpicker","title":"Color picker","parentIndex":"form-fields","status":{"collapsed":false},"parentUid":"root","uuid":"8e8ef97d-455a-41df-b193-711725d7bc2f","humanId":"Field 1","isContainer":false}]}';

  /**
   * {@inheritdoc}
   */
  public function setUp():void {
    $this->unit = new _0016EntityUpdate([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0016EntityUpdate::runUpdate
   */
  public function testRunUpdate() {

    $layout = new _0016MockUpdateEntity($this->fixture_layout_true, TRUE);
    $this->unit->runUpdate($layout);
    $this->assertEquals("none", json_decode($layout->getJsonValues())->model->{"8e8ef97d-455a-41df-b193-711725d7bc2f"}->settings->restrictBy);
    $this->assertArrayNotHasKey("allowAll", (array) json_decode($layout->getJsonValues())->model->{"8e8ef97d-455a-41df-b193-711725d7bc2f"}->settings);

    $layout = new _0016MockUpdateEntity($this->fixture_layout_false, TRUE);
    $this->unit->runUpdate($layout);
    $this->assertEquals("colors", json_decode($layout->getJsonValues())->model->{"8e8ef97d-455a-41df-b193-711725d7bc2f"}->settings->restrictBy);
    $this->assertArrayNotHasKey("allowAll", (array) json_decode($layout->getJsonValues())->model->{"8e8ef97d-455a-41df-b193-711725d7bc2f"}->settings);

  }

}
