<?php

namespace Drupal\Tests\cohesion_templates\Kernel;

use Drupal\cohesion_templates\Entity\ContentTemplates;
use Drupal\Core\Database\Database;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

/**
 * Tests memoization in _cohesion_templates_get_template_candidate().
 *
 * @group Cohesion
 */
class TemplateCandidateCacheTest extends EntityKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'node',
    'field',
    'text',
    'file',
    'cohesion',
    'cohesion_elements',
    'cohesion_templates',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installSchema('cohesion', ['coh_usage']);
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installConfig(['cohesion', 'cohesion_templates', 'node', 'field']);

    // Create a node type for testing.
    $nodeType = NodeType::create([
      'type' => 'article',
      'name' => 'Article',
    ]);
    $nodeType->save();

    // Create a content template for the article node type.
    $template = ContentTemplates::create([
      'id' => 'article_full',
      'label' => 'Article Full',
      'entity_type' => 'node',
      'bundle' => 'article',
      'view_mode' => 'full',
      'default' => TRUE,
      'status' => TRUE,
      'modified' => TRUE,
    ]);
    $template->save();
  }

  /**
   * Tests that template candidate lookup is memoized within a request.
   *
   * Multiple calls with identical parameters should return cached results
   * and should not execute additional database queries.
   */
  public function testTemplateCandidateMemoization(): void {
    // Create multiple nodes of the same type.
    $nodes = [];
    for ($i = 0; $i < 5; $i++) {
      $nodes[] = Node::create([
        'type' => 'article',
        'title' => 'Test Article ' . $i,
      ]);
      $nodes[$i]->save();
    }

    // Start query logging to track database queries.
    Database::startLog('memoization_test_first');

    // First call should execute a query.
    $firstResult = _cohesion_templates_get_template_candidate($nodes[0], 'full');

    // Get query count after the first call.
    $queriesAfterFirst = Database::getLog('memoization_test_first');
    $queryCountAfterFirst = count($queriesAfterFirst);

    // Start a new log for subsequent calls.
    Database::startLog('memoization_test_subsequent');

    // Subsequent calls with the same cache key should use memoized result.
    $results = [$firstResult];
    for ($i = 1; $i < count($nodes); $i++) {
      $results[] = _cohesion_templates_get_template_candidate($nodes[$i], 'full');
    }

    // Get query count after all subsequent calls.
    $queriesAfterAll = Database::getLog('memoization_test_subsequent');
    $queryCountAfterAll = count($queriesAfterAll);

    // No queries should have been executed for subsequent calls (memoized).
    $this->assertEquals(
      0,
      $queryCountAfterAll,
      sprintf(
        'Memoization failed: expected no queries for subsequent calls, but %d queries were executed.',
        $queryCountAfterAll
      )
    );

    // All results should be identical.
    foreach ($results as $index => $result) {
      $this->assertEquals(
        $firstResult,
        $result,
        "Result at index $index should match the first result (cached value)."
      );
    }

    // Verify the result structure.
    $this->assertArrayHasKey('chosen_template', $firstResult);
    $this->assertArrayHasKey('candidate_template_ids', $firstResult);
  }

  /**
   * Tests that different entity types/bundles are cached separately.
   */
  public function testTemplateCandidateCacheKeysDifferByBundle(): void {
    // Create a second node type.
    $pageType = NodeType::create([
      'type' => 'page',
      'name' => 'Basic Page',
    ]);
    $pageType->save();

    // Create a content template for the page node type.
    $pageTemplate = ContentTemplates::create([
      'id' => 'page_full',
      'label' => 'Page Full',
      'entity_type' => 'node',
      'bundle' => 'page',
      'view_mode' => 'full',
      'default' => TRUE,
      'status' => TRUE,
      'modified' => TRUE,
    ]);
    $pageTemplate->save();

    // Create nodes of different types.
    $article = Node::create([
      'type' => 'article',
      'title' => 'Test Article',
    ]);
    $article->save();

    $page = Node::create([
      'type' => 'page',
      'title' => 'Test Page',
    ]);
    $page->save();

    // Get results for both types.
    $articleResult = _cohesion_templates_get_template_candidate($article, 'full');
    $pageResult = _cohesion_templates_get_template_candidate($page, 'full');

    // Results should be different (different bundles).
    $this->assertNotEquals(
      $articleResult['candidate_template_ids'],
      $pageResult['candidate_template_ids'],
      'Different bundles should return different template candidates.'
    );

    // Call again - results should match the cached values.
    $articleResult2 = _cohesion_templates_get_template_candidate($article, 'full');
    $pageResult2 = _cohesion_templates_get_template_candidate($page, 'full');

    $this->assertEquals($articleResult, $articleResult2);
    $this->assertEquals($pageResult, $pageResult2);
  }

  /**
   * Tests that different view modes are cached separately.
   */
  public function testTemplateCandidateCacheKeysDifferByViewMode(): void {
    // Create a teaser template for the article node type.
    $teaserTemplate = ContentTemplates::create([
      'id' => 'article_teaser',
      'label' => 'Article Teaser',
      'entity_type' => 'node',
      'bundle' => 'article',
      'view_mode' => 'teaser',
      'default' => TRUE,
      'status' => TRUE,
      'modified' => TRUE,
    ]);
    $teaserTemplate->save();

    $article = Node::create([
      'type' => 'article',
      'title' => 'Test Article',
    ]);
    $article->save();

    // Get results for different view modes.
    $fullResult = _cohesion_templates_get_template_candidate($article, 'full');
    $teaserResult = _cohesion_templates_get_template_candidate($article, 'teaser');

    // View mode is part of the cache key, so these should query separately.
    $this->assertArrayHasKey('candidate_template_ids', $fullResult);
    $this->assertArrayHasKey('candidate_template_ids', $teaserResult);

    // Call again to verify caching per view mode.
    $fullResult2 = _cohesion_templates_get_template_candidate($article, 'full');
    $teaserResult2 = _cohesion_templates_get_template_candidate($article, 'teaser');

    $this->assertEquals($fullResult, $fullResult2);
    $this->assertEquals($teaserResult, $teaserResult2);
  }

}
