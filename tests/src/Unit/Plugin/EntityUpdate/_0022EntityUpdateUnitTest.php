<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\Plugin\EntityUpdate\_0022EntityUpdate;

/**
 * Class MockUpdateEntity.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class _0022MockUpdateEntity extends EntityMockBase implements EntityJsonValuesInterface {
}

/**
 * @group Cohesion
 */
class _0022EntityUpdateUnitTest extends EntityUpdateUnitTestCase {

  /**
   * @var unit_0022MockUpdateEntity*/
  protected $unit;

  private $fixture_json = '{"key":"uploadCustom","type":"upload","provider":"custom","name":"mdicons","fontFamilyName":"mdicons","fontFiles":{"eot":{"type":"file","uri":"cohesion:\/\/material-icons-outline_0.eot","uuid":"f8369c89-9df8-4123-b250-9f3a077d6a71"},"ttf":{"type":"file","uri":"cohesion:\/\/material-icons-outline_0.ttf","uuid":"9c70dc28-3f60-4a6e-931f-f32b9cb2abd8"},"woff":{"type":"file","uri":"cohesion:\/\/material-icons-outline_0.woff","uuid":"c6cb1364-d34c-4447-b464-cf42c58a2ea1"},"woff2":{"type":"file","uri":"cohesion:\/\/material-icons-outline_0.woff2","uuid":"18b568bd-892c-4123-ae21-0fb8a352b8f4"}},"iconJSON":{"json":"cohesion:\/\/icons_0.json"}}';

  /**
   * {@inheritdoc}
   */
  public function setUp():void {
    $this->unit = new _0022EntityUpdate([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0022EntityUpdate::runUpdate
   */
  public function testRunUpdate() {

    // WYSIWYG in layout canvas.
    $entity = new _0022MockUpdateEntity($this->fixture_json, TRUE);
    $this->assertionsBefore($entity->getDecodedJsonValues());
    $this->unit->updateLibrary($entity);
    $this->assertionsAfter($entity->getDecodedJsonValues());
    $this->unit->updateLibrary($entity);
    $this->assertionsAfter($entity->getDecodedJsonValues());
  }

  private function assertionsBefore($before) {
    $this->assertEquals("cohesion://material-icons-outline_0.eot", $before['fontFiles']['eot']['uri']);
    $this->assertEquals("cohesion://material-icons-outline_0.ttf", $before['fontFiles']['ttf']['uri']);
    $this->assertEquals("cohesion://material-icons-outline_0.woff", $before['fontFiles']['woff']['uri']);
    $this->assertEquals("cohesion://material-icons-outline_0.woff2", $before['fontFiles']['woff2']['uri']);
  }

  private function assertionsAfter($after) {
    $this->assertEquals("public://cohesion/material-icons-outline_0.eot", $after['fontFiles']['eot']['uri']);
    $this->assertEquals("public://cohesion/material-icons-outline_0.ttf", $after['fontFiles']['ttf']['uri']);
    $this->assertEquals("public://cohesion/material-icons-outline_0.woff", $after['fontFiles']['woff']['uri']);
    $this->assertEquals("public://cohesion/material-icons-outline_0.woff2", $after['fontFiles']['woff2']['uri']);
  }

}
