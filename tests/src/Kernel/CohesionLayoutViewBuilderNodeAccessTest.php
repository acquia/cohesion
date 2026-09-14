<?php

namespace Drupal\Tests\cohesion\Kernel;

use Drupal\cohesion_elements\Entity\CohesionLayout;
use Drupal\Core\Database\DatabaseExceptionWrapper;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/**
 * Tests CohesionLayoutViewBuilder with node access restrictions.
 *
 * This test verifies that rendering a cohesion_layout entity does not fail
 * when node access modules are enabled. The content_access module is known
 * to cause SQL errors because it adds node access conditions that reference
 * 'nid' instead of 'entity_id' on revision field tables.
 *
 * The test module cohesion_node_access_test simulates this bug by implementing
 * hook_query_node_access_alter() to add a subquery that references the
 * non-existent 'nid' column on revision field tables.
 *
 * @group Cohesion
 *
 * @see https://www.drupal.org/project/content_access
 */
class CohesionLayoutViewBuilderNodeAccessTest extends EntityKernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'file',
    'node',
    'field',
    'cohesion',
    'cohesion_elements',
    'cohesion_templates',
    'entity_reference_revisions',
    'token',
    'cohesion_node_access_test',
  ];

  /**
   * The node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installSchema('cohesion', ['coh_usage']);
    $this->installSchema('node', ['node_access']);
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('cohesion_layout');
    $this->installConfig(['node', 'field']);

    $this->nodeStorage = $this->entityTypeManager->getStorage('node');

    // Create node type.
    NodeType::create([
      'type' => 'page',
      'name' => 'Page',
    ])->save();

    // Create the entity_reference_revisions field for cohesion_layout.
    FieldStorageConfig::create([
      'field_name' => 'field_layout',
      'entity_type' => 'node',
      'type' => 'entity_reference_revisions',
      'cardinality' => 1,
      'settings' => [
        'target_type' => 'cohesion_layout',
      ],
    ])->save();

    FieldConfig::create([
      'field_name' => 'field_layout',
      'entity_type' => 'node',
      'bundle' => 'page',
      'label' => 'Layout',
    ])->save();

  }

  /**
   * Tests view builder query doesn't fail with node access enabled.
   *
   * This test reproduces the scenario where a node access module (like
   * content_access) adds access conditions to entity queries. When
   * CohesionLayoutViewBuilder runs a revision query with accessCheck(TRUE),
   * the node access conditions should not cause SQL errors.
   *
   * The specific bug was that content_access adds a condition referencing
   * 'node_revision__field_layout.nid' but that column doesn't exist -
   * revision field tables use 'entity_id' instead of 'nid'.
   */
  public function testViewBuilderWithNodeAccess(): void {
    // Create a cohesion_layout entity.
    $layout = CohesionLayout::create([
      'json_values' => '{"canvas":[],"mapper":{},"model":{}}',
      'parent_type' => 'node',
      'parent_field_name' => 'field_layout',
    ]);
    $layout->save();

    // Create a node with the layout field.
    $node = Node::create([
      'type' => 'page',
      'title' => 'Test page',
      'field_layout' => [
        'target_id' => $layout->id(),
        'target_revision_id' => $layout->getRevisionId(),
      ],
    ]);
    $node->save();

    // Update the layout's parent reference.
    $layout->set('parent_id', $node->id());
    $layout->save();

    // Create a new revision to ensure the revision query path is exercised.
    $node->setNewRevision(TRUE);
    $node->setRevisionCreationTime(\Drupal::time()->getRequestTime());
    $node->save();

    // Get the view builder.
    $view_builder = $this->entityTypeManager->getViewBuilder('cohesion_layout');

    // Attempt to render the layout - this triggers the revision query in
    // CohesionLayoutViewBuilder::view() which uses accessCheck(TRUE).
    // With the content_access bug, this would throw:
    // DatabaseExceptionWrapper: Column not found: 1054 Unknown column
    // 'node_revision__field_layout.nid' in 'where clause'
    $build = $view_builder->view($layout);

    $this->assertIsArray($build);
    $this->assertArrayHasKey('#type', $build);
  }

  /**
   * Tests view builder with multiple revisions and node access.
   */
  public function testViewBuilderWithMultipleRevisionsAndNodeAccess(): void {
    // Create initial layout for the first revision.
    $layout_v1 = CohesionLayout::create([
      'json_values' => '{"canvas":[],"mapper":{},"model":{}}',
      'parent_type' => 'node',
      'parent_field_name' => 'field_layout',
    ]);
    $layout_v1->save();

    // Create a node with the layout.
    $node = Node::create([
      'type' => 'page',
      'title' => 'Test page with revisions',
      'field_layout' => [
        'target_id' => $layout_v1->id(),
        'target_revision_id' => $layout_v1->getRevisionId(),
      ],
    ]);
    $node->save();

    $layout_v1->set('parent_id', $node->id());
    $layout_v1->save();

    // Create a new layout for a newer revision.
    $layout_v2 = CohesionLayout::create([
      'json_values' => '{"canvas":[],"mapper":{},"model":{}}',
      'parent_type' => 'node',
      'parent_field_name' => 'field_layout',
    ]);
    $layout_v2->save();

    // Create a new node revision with the new layout.
    $node->setNewRevision(TRUE);
    $node->set('field_layout', [
      'target_id' => $layout_v2->id(),
      'target_revision_id' => $layout_v2->getRevisionId(),
    ]);
    $node->setRevisionCreationTime(\Drupal::time()->getRequestTime() + 1);
    $node->save();

    $layout_v2->set('parent_id', $node->id());
    $layout_v2->save();

    // Verify we have multiple revisions.
    $revision_ids = $this->nodeStorage->getQuery()
      ->accessCheck(FALSE)
      ->allRevisions()
      ->condition('nid', $node->id())
      ->execute();
    $this->assertGreaterThan(1, count($revision_ids));

    // Load and render from the older revision's layout.
    // This exercises the CohesionLayoutViewBuilder's revision lookup query
    // which needs to find the correct host revision for the layout.
    $view_builder = $this->entityTypeManager->getViewBuilder('cohesion_layout');

    // Render the first layout (from the older revision).
    // This should not throw an exception even with node access enabled.
    $build = $view_builder->view($layout_v1);

    $this->assertIsArray($build);
  }

  /**
   * Tests that node access is actually being applied.
   *
   * This verifies that our test module is correctly enabling node access,
   * which is a prerequisite for the actual bug to manifest.
   */
  public function testNodeAccessIsEnabled(): void {
    // Create a node.
    $node = Node::create([
      'type' => 'page',
      'title' => 'Access test node',
    ]);
    $node->save();

    // Verify node access records exist.
    $records = \Drupal::database()->select('node_access', 'na')
      ->fields('na')
      ->condition('nid', $node->id())
      ->execute()
      ->fetchAll();

    // Should have at least one record from our test module.
    $this->assertNotEmpty($records);

    // Verify our test realm is present.
    $realms = array_column($records, 'realm');
    $this->assertContains('cohesion_node_access_test', $realms);
  }

  /**
   * Tests that the buggy query alter causes an error with accessCheck(TRUE).
   *
   * This test directly exercises the entity query to demonstrate that the
   * content_access-style bug causes a DatabaseExceptionWrapper when using
   * accessCheck(TRUE) on a revision query with field table joins.
   *
   * This documents the bug that CohesionLayoutViewBuilder works around by
   * using accessCheck(FALSE).
   */
  public function testDirectQueryWithAccessCheckTrueCausesBuggyAlterError(): void {
    // Create a layout and node.
    $layout = CohesionLayout::create([
      'json_values' => '{"canvas":[],"mapper":{},"model":{}}',
      'parent_type' => 'node',
      'parent_field_name' => 'field_layout',
    ]);
    $layout->save();

    $node = Node::create([
      'type' => 'page',
      'title' => 'Test page',
      'field_layout' => [
        'target_id' => $layout->id(),
        'target_revision_id' => $layout->getRevisionId(),
      ],
    ]);
    $node->save();

    // Run a query similar to CohesionLayoutViewBuilder but with
    // accessCheck(TRUE). This triggers the buggy query alter from modules
    // like content_access that reference 'nid' on revision field tables.
    $storage = $this->entityTypeManager->getStorage('node');

    // This query joins node_revision__field_layout and with accessCheck(TRUE)
    // the test module's query alter adds a condition on 'nid' which doesn't
    // exist on revision field tables.
    $this->expectException(DatabaseExceptionWrapper::class);
    $this->expectExceptionMessageMatches('/nid/');

    $storage->getQuery()
      ->allRevisions()
      ->accessCheck(TRUE)
      ->condition('field_layout.target_id', $layout->id())
      ->condition('field_layout.target_revision_id', $layout->getRevisionId())
      ->sort('vid', 'DESC')
      ->range(0, 1)
      ->execute();
  }

  /**
   * Tests that accessCheck(FALSE) bypasses the buggy query alter.
   *
   * This demonstrates the fix: using accessCheck(FALSE) avoids the node_access
   * query alter entirely, bypassing the bug. The actual access check is then
   * performed after loading the entity via $entity->access('view').
   */
  public function testDirectQueryWithAccessCheckFalseSucceeds(): void {
    // Create a layout and node.
    $layout = CohesionLayout::create([
      'json_values' => '{"canvas":[],"mapper":{},"model":{}}',
      'parent_type' => 'node',
      'parent_field_name' => 'field_layout',
    ]);
    $layout->save();

    $node = Node::create([
      'type' => 'page',
      'title' => 'Test page',
      'field_layout' => [
        'target_id' => $layout->id(),
        'target_revision_id' => $layout->getRevisionId(),
      ],
    ]);
    $node->save();

    // Run the same query with accessCheck(FALSE) - this should succeed
    // because it bypasses the buggy node_access query alter.
    $storage = $this->entityTypeManager->getStorage('node');

    $revision_ids = $storage->getQuery()
      ->allRevisions()
      ->accessCheck(FALSE)
      ->condition('field_layout.target_id', $layout->id())
      ->condition('field_layout.target_revision_id', $layout->getRevisionId())
      ->sort('vid', 'DESC')
      ->range(0, 1)
      ->execute();

    $this->assertNotEmpty($revision_ids);
    $this->assertArrayHasKey($node->getRevisionId(), $revision_ids);
  }

}
