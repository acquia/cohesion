<?php

namespace Drupal\Tests\cohesion_custom_styles\Kernel;

use Drupal\cohesion_custom_styles\Entity\CustomStyle;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests that the sortOrder method returns custom styles in the correct order
 * based on parent/child relationships and weight.
 *
 * @group Cohesion
 * @covers \Drupal\cohesion_custom_styles\Entity\CustomStyle::sortOrder
 */
class CustomStyleSortOrderTest extends KernelTestBase {

  protected static $modules = [
    'system',
    'user',
    'file',
    'cohesion',
    'cohesion_elements',
    'cohesion_custom_styles',
  ];

  protected function setUp(): void {
    parent::setUp();
    $this->installSchema('cohesion', ['coh_usage']);
    $this->installConfig(['cohesion_elements', 'cohesion_custom_styles']);
  }

  /**
   * Tests that the sortOrder method returns custom styles in the correct order
   * based on parent/child relationships and weight.
   *
   * @covers \Drupal\cohesion_custom_styles\Entity\CustomStyle::sortOrder
   */
  public function testSortOrder() {
    // First parent and child.
    $parent1 = CustomStyle::create([
      'id' => 'parent_style_1',
      'label' => 'Parent Style 1',
      'class_name' => '.coh-style-parent-class-1',
      'custom_style_type' => 'generic',
      'weight' => 0,
    ]);
    $parent1->save();

    $child1 = CustomStyle::create([
      'id' => 'child_style_1',
      'label' => 'Child Style 1',
      'class_name' => '.coh-style-child-class-1',
      'parent' => '.coh-style-parent-class-1',
      'custom_style_type' => 'generic',
      'weight' => 1,
    ]);
    $child1->save();

    // Second parent and child.
    $parent2 = CustomStyle::create([
      'id' => 'parent_style_2',
      'label' => 'Parent Style 2',
      'class_name' => '.coh-style-parent-class-2',
      'custom_style_type' => 'generic',
      'weight' => 2,
    ]);
    $parent2->save();

    $child2 = CustomStyle::create([
      'id' => 'child_style_2',
      'label' => 'Child Style 2',
      'class_name' => '.coh-style-child-class-2',
      'parent' => '.coh-style-parent-class-2',
      'custom_style_type' => 'generic',
      'weight' => 3,
    ]);
    $child2->save();

    $order = CustomStyle::sortOrder();

    $expected = [
      'parent_style_1_' . $parent1->getConfigItemId(),
      'child_style_1_' . $child1->getConfigItemId(),
      'parent_style_2_' . $parent2->getConfigItemId(),
      'child_style_2_' . $child2->getConfigItemId(),
    ];
    $this->assertEquals($expected, $order, 'Custom styles are ordered by parent/child and weight.');
  }
}
