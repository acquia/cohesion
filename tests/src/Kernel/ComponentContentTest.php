<?php

namespace Drupal\Tests\cohesion\Kernel;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

/**
 * Class ComponentContentTest.
 *
 * @group Cohesion
 *
 * @requires module cohesion
 */
class ComponentContentTest extends EntityKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'cohesion',
    'cohesion_elements',
    'entity_reference_revisions',
    'cohesion_templates',
  ];

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('component_content');
    $this->installSchema('cohesion', ['coh_usage']);
    $this->installConfig('cohesion_elements');
  }

  /**
   * Tests view mode is installed on component content.
   */
  public function testViewMode() {
    $storage = \Drupal::entityTypeManager()->getStorage('entity_view_mode');
    $view_mode = $storage->load("component_content.cohesion");
    $this->assertNotEqual($view_mode, FALSE);
    $this->assertEqual($view_mode->id(), 'component_content.cohesion');
    $this->assertEqual($view_mode->get('targetEntityType'), 'component_content');
  }

}
