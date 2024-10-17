<?php

namespace Drupal\Tests\cohesion\Kernel;

use Drupal\cohesion\TemplateStorage\KeyValueStorage;
use Drupal\Core\Site\Settings;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

/**
 * Class ComponentContentTest.
 *
 * @group Cohesion
 *
 * @requires module cohesion
 *
 */

class KeyValueStorageTest extends EntityKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'file',
    'cohesion',
  ];

  /**
   * Tests view mode is installed on component content.
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
