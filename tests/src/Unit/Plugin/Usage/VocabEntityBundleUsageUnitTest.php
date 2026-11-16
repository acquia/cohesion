<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\Usage;

use Drupal\cohesion\Entity\CohesionConfigEntityBase;
use Drupal\cohesion\Entity\CohesionSettingsInterface;
use Drupal\cohesion\Plugin\Usage\VocabEntityBundleUsage;
use Drupal\field\Entity\FieldConfig;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\Tests\cohesion\Unit\UsagePluginBaseUnitTest;

/**
 * Mock for the core taxonomy vocabulary entity.
 *
 * @package Drupal\Tests\cohesion\Unit\Plugin\Usage
 */
class TaxonomyVocabMock extends CohesionConfigEntityBase implements CohesionSettingsInterface {

  public function getApiPluginInstance() {
  }

}

class VocabEntityBundleUsageUnitTestMock extends VocabEntityBundleUsage {

  private $fixture_vocab = ['vid' => 'tags', 'uuid' => 'fca4771e-a35a-4d2f-9d5d-74bcb44b21e1', 'name' => 'Tags'];
  private $fixture_drupal_field = ['id' => 'node.page.field_tags', 'uuid' => 'de8a6765-cff0-4ef0-a7a7-db5b3df279588', 'field_name' => 'field_tags', 'entity_type' => 'node', 'bundle' => 'page', 'field_type' => 'entity_reference', 'settings' => ['handler' => 'default:taxonomy_term', 'handler_settings' => ['target_bundles' => ['tags' => 'tags']]]];

  public function taxonomyVocabularyBundleLoad($bundle) {
    $vocab = new Vocabulary($this->fixture_vocab, 'taxonomy_vocabulary');
    return $vocab->uuid();
  }

  public function drupalFieldLoad($entry_uuid) {
    return new FieldConfig($this->fixture_drupal_field, 'field_config');
  }

}

/**
 * @group Cohesion
 */
class VocabEntityBundleUsageUnitTest extends UsagePluginBaseUnitTest {

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Init the plugin.
    $this->unit = new VocabEntityBundleUsageUnitTestMock(
      $this->configuration,
      $this->plugin_id,
      $this->plugin_definition,
      $this->entity_type_manager_mock,
      $this->stream_wrapper_manager_mock,
      $this->database_connection_mock
    );
  }

  /**
   * @covers \Drupal\cohesion\Plugin\Usage\VocabEntityBundleUsage::scanForInstancesOfThisType
   */
  public function testScanForInstancesOfThisType() {

    $fixture = [
      [
        'type' => 'drupal_field',
        'uuid' => 'de8a6765-cff0-4ef0-a7a7-db5b3df279588',
      ],
    ];

    $entities = $this->unit->scanForInstancesOfThisType($fixture, new TaxonomyVocabMock([], 'taxonomy_vocabulary'));

    // Check the results.
    $this->assertEquals(count($entities), 1);
    $this->assertEquals($entities[0]['uuid'], 'fca4771e-a35a-4d2f-9d5d-74bcb44b21e1');
  }

}
