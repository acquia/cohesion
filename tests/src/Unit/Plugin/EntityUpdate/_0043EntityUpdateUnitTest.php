<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\Plugin\EntityUpdate\_0043EntityUpdate;
use Drupal\cohesion_website_settings\Entity\Color;

/**
 * Class MockUpdateEntity.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class _0043MockUpdateEntity extends EntityMockBase implements EntityJsonValuesInterface {
}

/**
 * @group Cohesion
 */
class _0043EntityUpdateUnitTest extends EntityUpdateUnitTestCase {

  protected $unit;

  /**
   * Color entity JSON values.
   *
   * @var string
   */
  private $jsonValues = '{"link":true,"name":"Maroon","uid":"maroon","class":".coh-color-maroon","variable":"$coh-color-maroon","value":{"value":{"hex":"#c30e2e","rgba":"rgba(195, 14, 46, 1)"}},"tags":[{"label":"bg-color","value":"bg-color"},{"label":"h2-h3-h4-h5-font-color","value":"h-2-h-3-h-4-h-5-font-color"},{"label":"interaction-color","value":"interaction-color"},{"label":"h1-font-color","value":"h-1-font-color"},{"label":"p-font-color","value":"p-font-color"},{"label":"pre-heading-font-color","value":"pre-heading-font-color"},{"label":"border-color","value":"border-color"}],"wysiwyg":true,"inuse":true}';

  /**
   * {@inheritdoc}
   */
  public function setUp() : void {
    $this->unit = new _0043EntityUpdate([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0043EntityUpdate::runUpdate
   */
  public function testRunUpdate() {

    $colorEntity = new Color([
      'label' => 'Maroon',
      'id' => 'maroon',
      'json_values' => $this->jsonValues,
      'json_mapper' => '{}',
      'last_entity_update' => 'entityupdate_0042',
      'locked' => false,
      'modified' => true,
      'selectable' => true,
      'weight' => 5,
    ], 'cohesion_color');

    $this->assertionsColorBefore($colorEntity->getDecodedJsonValues());
    $this->unit->runUpdate($colorEntity);
    $this->assertionsColorAfter($colorEntity->getDecodedJsonValues());
  }

  /**
   * @param $jsonValues
   * @return void
   */
  private function assertionsColorBefore($jsonValues) {
    $this->assertEquals('h2-h3-h4-h5-font-color', $jsonValues['tags'][1]['label']);
    $this->assertEquals('h-2-h-3-h-4-h-5-font-color', $jsonValues['tags'][1]['value']);
    $this->assertEquals('h1-font-color', $jsonValues['tags'][3]['label']);
    $this->assertEquals('h-1-font-color', $jsonValues['tags'][3]['value']);
  }

  /**
   * @param $jsonValues
   * @return void
   */
  private function assertionsColorAfter($jsonValues) {
    $this->assertEquals('h-2-h-3-h-4-h-5-font-color', $jsonValues['tags'][1]['label']);
    $this->assertEquals('h-2-h-3-h-4-h-5-font-color', $jsonValues['tags'][1]['value']);
    $this->assertEquals('h-1-font-color', $jsonValues['tags'][3]['label']);
    $this->assertEquals('h-1-font-color', $jsonValues['tags'][3]['value']);
  }

}
