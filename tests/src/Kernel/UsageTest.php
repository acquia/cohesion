<?php

use Drupal\cohesion_elements\Entity\Component;
use Drupal\cohesion_templates\Entity\MasterTemplates;
use Drupal\file\Entity\File;
use Drupal\file\FileInterface;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Class UsageTest.
 *
 * @group Cohesion
 * @group orca_ignore
 *
 * @requires module cohesion
 */
class UsageTest extends EntityKernelTestBase {

  use NodeCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'cohesion',
    'cohesion_elements',
    'entity_reference_revisions',
    'cohesion_templates',
    'token',
    'file',
    'node',
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
    $this->installEntitySchema('file');
    $this->installEntitySchema('user');
  }

  /**
   * Test cohesion dependencies on cohesion entities.
   *
   * @covers \Drupal\cohesion\UsageUpdateManager::getDependencies
   * @covers \Drupal\cohesion\UsageUpdateManager::buildRequires
   */
  public function testCohesionCohesionDependencies() {
    $dx8_no_send_to_api = &drupal_static('cohesion_sync_lock');
    $dx8_no_send_to_api = TRUE;

    $component = Component::create([
      'id' => '3fedc674',
      'json_values' => '{}',
    ]);
    $component->save();

    $master_template = MasterTemplates::create([
      'id' => $this->generateRandomEntityId(),
      'json_values' => '{"model":{"eebab09e-88eb-4599-9b29-17d427a048a1":{"settings":{"title":"Text"},"6b671446-cb09-46cb-b84a-7366da00be36":{"text":"<p>The European languages are members of the same family. Their separate existence is a myth. For science, music, sport, etc, Europe uses the same vocabulary. The languages only differ in their grammar, their pronunciation and their most common words. Everyone realizes why a new common language would be desirable: one could refuse to pay expensive translators. To achieve this, it would be necessary to have uniform grammar, pronunciation and more common words.<\/p>\n\n<p>If several languages coalesce, the grammar of the resulting language is more simple and regular than that of the individual languages. The new common language will be more simple and regular than the existing European languages. It will be as simple as Occidental; in fact, it will be Occidental. To an English person, it will seem like simplified English, as a skeptical Cambridge friend of mine told me what Occidental is. The European languages are members of the same family. Their separate existence is a myth.<\/p>\n\n<p>For science, music, sport, etc, Europe uses the same vocabulary. The languages only differ in their grammar, their pronunciation and their most common words. Everyone realizes why a new common language would be desirable: one could refuse to pay expensive translators. To achieve this, it would be necessary to have uniform grammar, pronunciation and more common words. If several languages coalesce, the grammar of the resulting language is more simple and regular than that of the individual languages.<\/p>\n","textFormat":"cohesion"},"fdaea1d1-6b7c-4aad-978a-e6981fb5eb7d":{"name":"White","uid":"white","value":{"hex":"#ffffff","rgba":"rgba(255, 255, 255, 1)"},"wysiwyg":true,"class":".coh-color-white","variable":"$coh-color-white","inuse":true,"link":true},"e6f07bf5-1bfa-4fef-8baa-62abb3016788":"coh-style-max-width---narrow","165f1de9-336c-42cc-bed2-28ef036ec7e3":"coh-style-padding-bottom---large","4c27d36c-a473-47ec-8d43-3b9696d45d74":""}},"mapper":{},"previewModel":{"eebab09e-88eb-4599-9b29-17d427a048a1":{}},"variableFields":{"eebab09e-88eb-4599-9b29-17d427a048a1":[]},"meta":{},"canvas":[{"uid":"3fedc674","type":"component","title":"Text","enabled":true,"category":"category-3","componentId":"3fedc674","componentType":"container","parentUid":"root","uuid":"eebab09e-88eb-4599-9b29-17d427a048a1","children":[]}],"componentForm":[]}',
    ]);
    $master_template->save();

    $dependencies_count = $this->container->get('cohesion_usage.update_manager')
      ->buildRequires($master_template);

    $dependencies = $this->container->get('database')->select('coh_usage', 'c1')
      ->fields('c1', ['requires_uuid', 'requires_type'])
      ->condition('c1.source_uuid', $master_template->uuid(), '=')
      ->execute()
      ->fetchAllKeyed();

    $this->assertEquals($dependencies_count, 1);
    $this->assertEquals(count($dependencies), 1);
    $this->assertArrayHasKey($component->uuid(), $dependencies);
    $this->assertEquals($dependencies[$component->uuid()], 'cohesion_component');
  }

  /**
   * Test core,cohesion dependencies on cohesion entities.
   *
   * @covers \Drupal\cohesion\UsageUpdateManager::getDependencies
   * @covers \Drupal\cohesion\UsageUpdateManager::buildRequires
   */
  public function testCoreCohesionCohesionDependencies() {
    $dx8_no_send_to_api = &drupal_static('cohesion_sync_lock');
    $dx8_no_send_to_api = TRUE;

    $file = File::create([
      'uid' => 1,
      'filename' => 'druplicon.txt',
      'uri' => 'public://druplicon.txt',
      'filemime' => 'text/plain',
      'created' => 1,
      'changed' => 1,
      'status' => FileInterface::STATUS_PERMANENT,
    ]);
    file_put_contents($file->getFileUri(), 'hello world');

    // Save it, inserting a new record.
    $file->save();

    $json_values = str_replace('%%FILEUUID%%', $file->uuid(), '{"model":{"d54e826d-65f9-4756-82d0-69a2a0700ab8":{"settings":{"title":"Image","styles":{"xl":{"displaySize":"coh-image-responsive","imageAlignment":"coh-image-align-left"}},"customStyle":[{"customStyle":""}],"lazyload":false,"settings":{"lazyload":false,"styles":{"xl":{"displaySize":"coh-image-responsive"}},"imageStyle":"","customStyle":[{"customStyle":""}]},"imageStyle":"","image":"[media-reference:file:%%FILEUUID%%]"},"context-visibility":{"contextVisibility":{"condition":"ALL"}},"styles":{"settings":{"element":"img"}}}},"mapper":{"d54e826d-65f9-4756-82d0-69a2a0700ab8":{}},"previewModel":{"d54e826d-65f9-4756-82d0-69a2a0700ab8":{}},"variableFields":{"d54e826d-65f9-4756-82d0-69a2a0700ab8":[]},"meta":{},"canvas":[{"type":"item","uid":"image","title":"Image","status":{"collapsed":true},"uuid":"d54e826d-65f9-4756-82d0-69a2a0700ab8","parentUid":"root"}],"componentForm":[]}');

    $component = Component::create([
      'id' => $this->generateRandomEntityId(),
      'json_values' => $json_values,
    ]);
    $component->save();

    $dependencies_count = $this->container->get('cohesion_usage.update_manager')
      ->buildRequires($component);

    $dependencies = $this->container->get('database')->select('coh_usage', 'c1')
      ->fields('c1', ['requires_uuid', 'requires_type'])
      ->condition('c1.source_uuid', $component->uuid(), '=')
      ->execute()
      ->fetchAllKeyed();

    $this->assertEquals($dependencies_count, 1);
    $this->assertEquals(count($dependencies), 1);
    $this->assertArrayHasKey($file->uuid(), $dependencies);
    $this->assertEquals($dependencies[$file->uuid()], 'file');
  }
}
