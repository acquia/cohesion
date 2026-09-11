<?php

namespace Drupal\Tests\cohesion_custom_styles\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests cohesion_custom_styles_update_9000().
 *
 * @group Cohesion
 * @covers ::cohesion_custom_styles_update_9000
 */
class CustomStylesRestResourceCleanupUpdateTest extends KernelTestBase {

  protected static $modules = [
    'system',
    'user',
    'file',
    'serialization',
    'rest',
    'cohesion',
    'cohesion_elements',
    'cohesion_custom_styles',
  ];

  /**
   * The rest.resource.* config names the update hook should remove.
   *
   * @var string[]
   */
  protected const RESOURCES_TO_DELETE = [
    'rest.resource.cohesion_custom_styles',
    'rest.resource.cohesion_custom_style_type',
    'rest.resource.dx8_resource',
  ];

  protected function setUp(): void {
    parent::setUp();
    $this->installSchema('cohesion', ['coh_usage']);
    \Drupal::moduleHandler()->loadInclude('cohesion_custom_styles', 'install');
  }

  /**
   * Simulates a site where stale rest.resource.* config was reintroduced
   * (e.g. by a config import), and asserts the update hook removes it.
   */
  public function testDeletesStaleRestResourceConfig() {
    foreach (self::RESOURCES_TO_DELETE as $name) {
      $this->createRestResourceConfig($name);
    }

    // Some unrelated rest resource config should be left alone.
    $this->createRestResourceConfig('rest.resource.unrelated');

    cohesion_custom_styles_update_9000();

    $config_factory = \Drupal::configFactory();
    foreach (self::RESOURCES_TO_DELETE as $name) {
      $this->assertTrue($config_factory->get($name)->isNew(), "$name should have been deleted.");
    }
    $this->assertFalse(
      $config_factory->get('rest.resource.unrelated')->isNew(),
      'Unrelated rest resource config should not be touched.'
    );
  }

  /**
   * Asserts the update hook is a no-op on a site that's already clean.
   */
  public function testNoOpWhenConfigAlreadyAbsent() {
    cohesion_custom_styles_update_9000();

    $config_factory = \Drupal::configFactory();
    foreach (self::RESOURCES_TO_DELETE as $name) {
      $this->assertTrue($config_factory->get($name)->isNew());
    }
  }

  /**
   * Creates a minimal rest.resource.* config record for the test.
   */
  protected function createRestResourceConfig(string $name): void {
    $plugin_id = str_replace('rest.resource.', '', $name);
    \Drupal::configFactory()->getEditable($name)
      ->setData([
        'id' => $plugin_id,
        'plugin_id' => $plugin_id,
        'granularity' => 'resource',
        'configuration' => [
          'methods' => ['GET'],
          'formats' => ['json'],
          'authentication' => ['cookie'],
        ],
      ])
      ->save();
  }

}
