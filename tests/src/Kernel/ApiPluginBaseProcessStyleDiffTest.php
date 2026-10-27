<?php

namespace Drupal\Tests\cohesion\Kernel;

use Drupal\cohesion\ApiPluginBase;
use Drupal\cohesion_base_styles\Entity\BaseStyles;
use Drupal\cohesion_custom_styles\Entity\CustomStyle;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests processStyleDiff in ApiPluginBase.
 *
 * @group Cohesion
 * @covers \Drupal\cohesion\ApiPluginBase::processStyleDiff
 */
class ApiPluginBaseProcessStyleDiffTest extends KernelTestBase {

  protected static $modules = [
    'system',
    'user',
    'file',
    'cohesion',
    'cohesion_elements',
    'cohesion_custom_styles',
    'cohesion_base_styles',
  ];

  /** @var \Drupal\cohesion\ApiPluginBase */
  protected $plugin;

  protected function setUp(): void {
    parent::setUp();
    $this->installSchema('cohesion', ['coh_usage']);
    $this->installConfig([
      'cohesion_custom_styles',
      'cohesion_base_styles',
      'cohesion_elements',
    ]);

    // Create a dummy plugin instance.
    $this->plugin = $this->getMockForAbstractClass(ApiPluginBase::class, [
      [], 'test_plugin', ['name' => 'Test Plugin'],
      $this->container->get('entity_type.manager'),
      $this->container->get('stream_wrapper_manager'),
      $this->container->get('cohesion.local_files_manager'),
      $this->container->get('config.installer'),
      $this->container->get('cohesion.utils'),
      $this->container->get('module_handler'),
      $this->container->get('theme_handler'),
      $this->container->get('theme.manager'),
      $this->container->get('file_system'),
      $this->container->get('logger.factory'),
      $this->container->get('messenger'),
      $this->container->get('keyvalue'),
    ]);
  }

  /**
   * Testing that processStyleDiff correctly processes added/updated custom
   * styles.
   */
  public function testProcessStyleDiffWithCustomStyles() {
    // Prepare current styles and cssDiff.
    $currentStyles = [
      'cohesion_custom_style' => [
        'test_style_1' => [],
        'test_style_2' => [],
      ],
    ];
    $cssDiff = [
      'added' => [
        'cohesion_custom_style' => [
          'test_style_3' => [],
        ],
      ],
      'updated' => [
        'cohesion_custom_style' => [
          'test_style_2' => [],
        ],
      ],
      'deleted' => [
        'styles' => [
          'cohesion_custom_style' => [
            'test_style_1' => [],
          ],
        ],
      ],
    ];

    $result = $this->invokeMethod($this->plugin, 'processStyleDiff', [$currentStyles, $cssDiff]);

    $this->assertArrayHasKey('cohesion_custom_style', $result);
    $this->assertArrayNotHasKey('test_style_1', $result['cohesion_custom_style']);
    $this->assertArrayHasKey('test_style_2', $result['cohesion_custom_style']);
    $this->assertArrayHasKey('test_style_3', $result['cohesion_custom_style']);
  }

  /**
   * Testing that processStyleDiff correctly processes added/updated base
   * styles.
   */
  public function testProcessStyleDiffWithBaseStyles() {
    // Add base styles to current styles.
    $currentStyles = [
      'cohesion_base_styles' => [
        'html' => [],
        'base_custom_1' => [],
        'base_custom_2' => [],
      ],
    ];
    // Add new/updated base styles in the diff.
    $cssDiff = [
      'added' => [
        'cohesion_base_styles' => [
          'base_custom_3' => [],
        ],
      ],
      'updated' => [
        'cohesion_base_styles' => [
          'base_custom_2' => [],
        ],
      ],
      'deleted' => [],
    ];

    // Enable the module in the test container.
    $result = $this->invokeMethod($this->plugin, 'processStyleDiff', [$currentStyles, $cssDiff]);

    $expectedBaseStyles = BaseStyles::reorderStyles([
      'html' => [],
      'base_custom_1' => [],
      'base_custom_2' => [],
      'base_custom_3' => [],
    ]);

    $this->assertEquals($expectedBaseStyles, $result['cohesion_base_styles']);
  }

  /**
   * Tests that processStyleDiff correctly processes a diff that includes both
   * custom and base styles, ensuring that both are updated and reordered.
   */
  public function testProcessStyleDiffWithCustomAndBaseStyles() {
    // Create custom styles.
    $parent = CustomStyle::create([
      'id' => 'parent_style',
      'label' => 'Parent Style',
      'class_name' => 'parent-class',
      'weight' => 0,
    ]);
    $parent->save();

    $child = CustomStyle::create([
      'id' => 'child_style',
      'label' => 'Child Style',
      'class_name' => 'child-class',
      'parent' => 'parent-class',
      'weight' => 1,
    ]);
    $child->save();

    // Current styles with both custom and base styles.
    $currentStyles = [
      'cohesion_custom_style' => [
        'parent_style_' . $parent->getConfigItemId() => [],
        'child_style_' . $child->getConfigItemId() => [],
      ],
      'cohesion_base_styles' => [
        'body' => [],
        'base_custom_1' => [],
      ],
    ];

    // Diff with added/updated custom and base styles.
    $cssDiff = [
      'added' => [
        'cohesion_custom_style' => [
          'new_custom' => [],
        ],
        'cohesion_base_styles' => [
          'base_custom_2' => [],
        ],
      ],
      'updated' => [
        'cohesion_custom_style' => [
          'child_style_' . $child->getConfigItemId() => [],
        ],
        'cohesion_base_styles' => [
          'base_custom_1' => [],
        ],
      ],
      'deleted' => [],
    ];

    $result = $this->invokeMethod($this->plugin, 'processStyleDiff', [$currentStyles, $cssDiff]);

    // Build expected custom styles order using sortOrder().
    $expectedCustomOrder = CustomStyle::sortOrder();
    $expectedCustom = [];
    foreach ($expectedCustomOrder as $key) {
      if (isset($result['cohesion_custom_style'][$key])) {
        $expectedCustom[$key] = $result['cohesion_custom_style'][$key];
      }
    }
    if (isset($result['cohesion_custom_style']['new_custom'])) {
      $expectedCustom['new_custom'] = $result['cohesion_custom_style']['new_custom'];
    }

    $expectedBase = BaseStyles::reorderStyles([
      'body' => [],
      'base_custom_1' => [],
      'base_custom_2' => [],
    ]);

    $this->assertEquals($expectedCustom, $result['cohesion_custom_style']);
    $this->assertEquals($expectedBase, $result['cohesion_base_styles']);
  }

  // Helper to invoke protected methods.
  protected function invokeMethod(&$object, $methodName, array $parameters = []) {
    $reflection = new \ReflectionClass(get_class($object));
    $method = $reflection->getMethod($methodName);
    $method->setAccessible(true);
    return $method->invokeArgs($object, $parameters);
  }
}
