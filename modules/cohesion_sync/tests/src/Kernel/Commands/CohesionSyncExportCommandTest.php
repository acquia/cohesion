<?php

namespace Drupal\Tests\cohesion_sync\Kernel;

use Drupal\cohesion_sync\Drush\Commands\CohesionSyncExportCommand;
use Drupal\cohesion_sync\Config\CohesionFileStorage;
use Drupal\cohesion_sync\Config\CohesionFullPackageStorage;
use Drupal\cohesion_sync\Config\CohesionPackageStorage;
use Drupal\Component\Serialization\Yaml;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

/**
 * Test for the Drush site studio export command.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\cohesion_sync\Kernel
 */
class CohesionSyncExportCommandTest extends EntityKernelTestBase {

  public $configStorage;

  public $command;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'cohesion',
    'cohesion_elements',
    'cohesion_templates',
    'cohesion_sync',
    'file',
    'context',
    'image',
  ];

  /**
   * {@inheritdoc}
   *
   * @throws \Exception
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installSchema('cohesion', ['coh_usage']);
    $this->installConfig('cohesion_elements');
    $this->installConfig('cohesion_templates');
    $this->installConfig('context');
    $this->installConfig('image');
    $this->installEntitySchema('file');

    $config_manager = $this->container->get('config.manager');
    $this->configStorage = $this->container->get('config.storage');
    // Add two exportable entity type and one non exportable entity type.
    $enabled_entities = [
      'cohesion_content_templates' => 1,
      'cohesion_component' => 1,
      'context' => 1,
      'image_style' => 1,
    ];
    $this->configStorage->write('cohesion.sync.settings', ['enabled_entity_types' => $enabled_entities]);
    $usage_manager = $this->container->get('plugin.manager.usage.processor');
    $connection = $this->container->get('database');
    $cohesionFullStorage = new CohesionFullPackageStorage($this->configStorage, $config_manager, $usage_manager);
    $cohesionPackageStorage = new CohesionPackageStorage($this->configStorage, $config_manager, $connection);
    $this->command = new CohesionSyncExportCommand(
      $this->container->get('entity_type.manager'),
      $this->configStorage,
      $cohesionFullStorage,
      $cohesionPackageStorage,
      $config_manager
    );
  }

  /**
   * Test site studio export command.
   *
   * @covers \Drupal\cohesion_sync\Drush\Commands\CohesionSyncExportCommand::siteStudioExport
   */
  public function testSiteStudioExport() {
    $path = 'temporary://test-sync';
    /** @var \Drupal\Core\File\FileSystemInterface $file_system */
    $file_system = \Drupal::service('file_system');
    $file_system->deleteRecursive($path);

    // Cohesion template should be included.
    $template_name = 'cohesion_templates.cohesion_content_templates.test_config';
    $template_data = ['coh_some' => 'coh_data'];
    $this->configStorage->write($template_name, $template_data);

    // Add a config with a file dependency.
    $contents = $this->randomMachineName(8);
    $filename = 'dependency_file.txt';
    $result = \Drupal::service('file.repository')->writeData($contents, 'public://' . $filename);

    // Add a component ( should be included as well as dependencies)
    $component_name = 'cohesion_elements.cohesion_component.test_config';
    $component_data = [
      'coh_some' => 'coh_data',
      'dependencies' => [
        'content' => [
          $result->getConfigDependencyName(),
        ],
      ],
    ];
    $this->configStorage->write($component_name, $component_data);

    // Add a component with a dependency that
    // should be included and one that should not.
    // Add a context (should not be exported even if it's in $enabled_entities).
    $context_name = 'context.context.config_id';
    $context_data = ['coh_some' => 'coh_data'];
    $this->configStorage->write($context_name, $context_data);
    // Add an image style as a dependency (should be exported)
    $image_style_name = 'image.style.config_id';
    $image_style_data = ['coh_some' => 'coh_data'];
    $this->configStorage->write($image_style_name, $image_style_data);
    // Add the component with a dependencies.
    $component_config_dep_name = 'cohesion_elements.cohesion_component.config_dependencies';
    $component_config_dep_data = [
      'coh_some' => 'coh_data',
      'dependencies' => [
        'config' => [
          $context_name,
          $image_style_name,
        ],
      ],
    ];
    $this->configStorage->write($component_config_dep_name, $component_config_dep_data);

    // Add a config (image style) standalone that should not be exported.
    // Add an image style as a dependency (should be exported)
    $image_style_standalone_name = 'image.style.standalone_config_id';
    $image_style_standalone_data = ['coh_some' => 'coh_data'];
    $this->configStorage->write($image_style_standalone_name, $image_style_standalone_data);

    // Execute the command.
    $this->command->siteStudioExport(['path' => $path, 'package' => NULL]);

    // Template.
    $this->assertFileExists($path . '/' . $template_name . '.yml', 'Template yml should exist');
    $this->assertEquals(Yaml::decode(file_get_contents($path . '/' . $template_name . '.yml')), $template_data);

    // Component.
    $this->assertFileExists($path . '/' . $component_name . '.yml', 'Component yml should exist');
    $this->assertEquals(Yaml::decode(file_get_contents($path . '/' . $component_name . '.yml')), $component_data);
    // Component dependency file.
    $this->assertFileExists($path . '/' . $filename, 'File dependency should exist');
    $this->assertEquals(file_get_contents($path . '/' . $filename), $contents);

    // Component with config dependencies.
    $this->assertFileExists($path . '/' . $component_config_dep_name . '.yml', 'Component yml should exist');
    $this->assertEquals(Yaml::decode(file_get_contents($path . '/' . $component_config_dep_name . '.yml')), $component_config_dep_data);
    // Context.
    $this->assertFileDoesNotExist($path . '/' . $context_name . '.yml', 'Context yml not should exist');
    // Image style.
    $this->assertFileExists($path . '/' . $image_style_name . '.yml', 'Image style yml should exist as it is a dependency of a SS config');
    $this->assertEquals(Yaml::decode(file_get_contents($path . '/' . $image_style_name . '.yml')), $image_style_data);

    // Standalone image style.
    $this->assertFileDoesNotExist($path . '/' . $image_style_standalone_name . '.yml', 'Standalone image style should not exist');

    $this->assertFileExists($path . '/' . CohesionFileStorage::FILE_METADATA_PREFIX . '.' . $result->uuid() . '.yml', 'YML metadata files should exist');
    $metadata = Yaml::decode(file_get_contents($path . '/' . CohesionFileStorage::FILE_METADATA_PREFIX . '.' . $result->uuid() . '.yml'));
    $this->assertEquals($result->uuid(), $metadata['uuid']);
  }

}
