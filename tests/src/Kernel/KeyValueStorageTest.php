<?php

namespace Drupal\Tests\cohesion\Kernel;

use Drupal\cohesion\TemplateStorage\KeyValueStorage;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests the key value template storage.
 *
 * @group Cohesion
 */
#[CoversMethod(KeyValueStorage::class, 'save')]
#[CoversMethod(KeyValueStorage::class, 'commit')]
#[CoversMethod(KeyValueStorage::class, 'getKey')]
#[CoversFunction('cohesion_theme_registry_alter')]
#[Group('Cohesion')]

class KeyValueStorageTest extends EntityKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'file',
    'cohesion',
  ];

  protected function setUp(): void {
    parent::setUp();

    // Override the service to use KeyValueStorage.
    \Drupal::getContainer()->set('cohesion.template_storage', \Drupal::service('cohesion.template_storage.key_value'));
  }

  /**
   * Tests templates are saved, committed and registered via key value storage.
   */
  public function testKeyValueStorage() {

    $keyValueStorage = \Drupal::service('keyvalue')->get('cohesion.templates');
    $templateStorage = \Drupal::service('cohesion.template_storage');

    $this->assertEquals(get_class($templateStorage), KeyValueStorage::class);

    $time = time();
    $template_name = 'component--cohesion-test.html.twig';
    $templateStorage->save('component--cohesion-test.html.twig', 'content', time());

    $keyValueReflection = new \ReflectionClass(KeyValueStorage::class);
    $method = $keyValueReflection->getMethod('getKey');
    $method->setAccessible(TRUE);
    $templateKey = $method->invokeArgs($templateStorage, [$template_name]);

    $this->assertSame($keyValueStorage->get('temporary::' . $templateKey), [
      'component--cohesion-test.html.twig',
      'content',
      $time,
    ]);

    $templateStorage->commit();

    $this->assertSame($keyValueStorage->get($templateKey), [
      'component--cohesion-test.html.twig',
      'content',
      $time,
    ]);

    $this->assertNull($keyValueStorage->get('temporary::' . $templateKey));

    $theme_registry = [];
    cohesion_theme_registry_alter($theme_registry);

    $wrapper = \Drupal::service('stream_wrapper_manager')->getViaUri(COHESION_TEMPLATE_PATH);
    $template_path = $wrapper->basePath() . '/cohesion/templates';

    $this->assertSame($theme_registry, [
      'component__cohesion_test' => [
        'template' => 'component--cohesion-test',
        'path' => $template_path,
        'base hook' => 'component',
      ],
    ]);
  }

}
