<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\Usage;

use Drupal\cohesion\Entity\CohesionConfigEntityBase;
use Drupal\cohesion\Entity\CohesionSettingsInterface;
use Drupal\cohesion\Plugin\Usage\MediaEntityBundleUsage;
use Drupal\field\Entity\FieldConfig;
use Drupal\media\Entity\MediaType;
use Drupal\Tests\cohesion\Unit\UsagePluginBaseUnitTest;

/**
 * Mock for the core media type entity.
 *
 * @package Drupal\Tests\cohesion\Unit\Plugin\Usage
 */
class MediaTypeMock extends CohesionConfigEntityBase implements CohesionSettingsInterface {

  public function getApiPluginInstance() {
  }

}

class MediaEntityBundleUsageUnitTestMock extends MediaEntityBundleUsage {

  private $fixture_media_type = ['id' => 'image', 'uuid' => '9c55f394-382b-4003-850c-97a003ede066', 'label' => 'Image', 'source' => 'image'];
  private $fixture_drupal_field = ['id' => 'node.page.field_media', 'uuid' => 'de8a6765-cff0-4ef0-a7a7-db5b3df2795d6', 'field_name' => 'field_media', 'entity_type' => 'node', 'bundle' => 'page', 'field_type' => 'entity_reference', 'settings' => ['handler' => 'default:media', 'handler_settings' => ['target_bundles' => ['image' => 'image']]]];

  public function mediaEntityBundleLoad($bundle) {
    $media_type = new MediaType($this->fixture_media_type, 'media_type');
    return $media_type->uuid();
  }

  public function drupalFieldLoad($entry_uuid) {
    return new FieldConfig($this->fixture_drupal_field, 'field_config');
  }

}

/**
 * @group Cohesion
 */
class MediaEntityBundleUsageUnitTest extends UsagePluginBaseUnitTest {

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Init the plugin.
    $this->unit = new MediaEntityBundleUsageUnitTestMock(
      $this->configuration,
      $this->plugin_id,
      $this->plugin_definition,
      $this->entity_type_manager_mock,
      $this->stream_wrapper_manager_mock,
      $this->database_connection_mock
    );
  }

  /**
   * @covers \Drupal\cohesion\Plugin\Usage\MediaEntityBundleUsage::scanForInstancesOfThisType
   */
  public function testScanForInstancesOfThisType() {

    $fixture = [
      [
        'type' => 'drupal_field',
        'uuid' => 'de8a6765-cff0-4ef0-a7a7-db5b3df2795d6',
      ],
    ];

    $entities = $this->unit->scanForInstancesOfThisType($fixture, new MediaTypeMock([], 'media_type'));

    // Check the results.
    $this->assertEquals(count($entities), 1);
    $this->assertEquals($entities[0]['uuid'], '9c55f394-382b-4003-850c-97a003ede066');
  }

}
