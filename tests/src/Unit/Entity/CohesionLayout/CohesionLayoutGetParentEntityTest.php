<?php

namespace Drupal\Tests\cohesion\Unit\Entity\CohesionLayout;

use Drupal\cohesion_elements\Entity\CohesionLayout;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Entity\RevisionableStorageInterface;
use Drupal\Core\Language\Language;
use Drupal\Tests\UnitTestCase;

/**
 * Unit tests for CohesionLayout::getParentEntity().
 *
 * @coversDefaultClass \Drupal\cohesion_elements\Entity\CohesionLayout
 * @group Cohesion
 */
class CohesionLayoutGetParentEntityTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  protected function tearDown(): void {
    parent::tearDown();
    drupal_static_reset();
    \Drupal::setContainer(new ContainerBuilder());
  }

  /**
   * Builds a CohesionLayout mock with the given field values.
   *
   * @param array $fields
   *   Map of field name to scalar value, read via get($field)->value.
   * @param int|string $id
   *   The layout entity id.
   * @param int|string $revisionId
   *   The layout revision id.
   * @param string $langcode
   *   The layout entity's own langcode.
   *
   * @return \Drupal\cohesion_elements\Entity\CohesionLayout
   */
  protected function createLayoutMock(array $fields, $id, $revisionId, string $langcode = 'en'): CohesionLayout {
    $layout = $this->getMockBuilder(CohesionLayout::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['get', 'id', 'getRevisionId', 'language', 'entityTypeManager'])
      ->getMock();

    $layout->method('get')->willReturnCallback(function ($field) use ($fields) {
      return new class($fields[$field] ?? NULL) {
        public function __construct(public $value) {
        }
      };
    });

    $layout->method('id')->willReturn($id);
    $layout->method('getRevisionId')->willReturn($revisionId);
    $layout->method('language')->willReturn(new Language(['id' => $langcode]));

    return $layout;
  }

  /**
   * Builds a stub field item list exposing first()->target_id /
   * ->target_revision_id, the way a host's entity_reference_revisions field
   * is read by the cheap-path check in getParentEntity().
   *
   * @param int|string|null $targetId
   *   The target_id of the first field item, or NULL for an empty list.
   * @param int|string|null $targetRevisionId
   *   The target_revision_id of the first field item.
   *
   * @return object
   */
  protected function createFieldItemList($targetId, $targetRevisionId = NULL): object {
    return new class($targetId, $targetRevisionId) {
      public function __construct(private $targetId, private $targetRevisionId) {
      }

      public function first() {
        if ($this->targetId === NULL) {
          return NULL;
        }
        return new class($this->targetId, $this->targetRevisionId) {
          public function __construct(public $target_id, public $target_revision_id) {
          }
        };
      }

    };
  }

  /**
   * Tests that getParentEntity() takes the cheap path - loading the stored
   * parent_id directly and confirming it already references this revision -
   * without ever issuing the allRevisions() query, when the stored pointer
   * is still correct (the common, non-shared case).
   *
   * @covers ::getParentEntity
   */
  public function testGetParentEntityUsesCheapPathWhenStoredParentMatchesRevision(): void {
    $matchedNode = $this->prophesize(ContentEntityInterface::class);
    $matchedNode->id()->willReturn(7656);
    $matchedNode->hasField('field_layout_canvas')->willReturn(TRUE);
    $matchedNode->get('field_layout_canvas')->willReturn($this->createFieldItemList(2176, 82301));
    $matchedNode->hasTranslation('en')->willReturn(FALSE);

    $storage = $this->prophesize(RevisionableStorageInterface::class);
    $storage->load(7656)->willReturn($matchedNode->reveal());
    $storage->getQuery()->shouldNotBeCalled();

    $entityTypeManager = $this->prophesize(EntityTypeManagerInterface::class);
    $entityTypeManager->getStorage('node')->willReturn($storage->reveal());

    $layout = $this->createLayoutMock([
      'parent_type' => 'node',
      'parent_id' => 7656,
      'parent_field_name' => 'field_layout_canvas',
    ], 2176, 82301);

    $layout->method('entityTypeManager')->willReturn($entityTypeManager->reveal());

    $this->assertSame($matchedNode->reveal(), $layout->getParentEntity());
  }

  /**
   * Tests that getParentEntity() resolves the host that references THIS
   * specific layout revision, even when another host has since overwritten
   * the shared (non-revisionable) parent_id/parent_type fields.
   *
   * This reproduces ACMS-6479: two nodes (one published, one archived) each
   * hold a distinct revision of the same cohesion_layout entity. Because
   * parent_id/parent_type live on the base table and are shared by every
   * revision, the stale pointer alone would incorrectly resolve the
   * published node's layout revision to the archived node - so the cheap
   * path's revision check must reject it and fall through to the query.
   *
   * @covers ::getParentEntity
   */
  public function testGetParentEntityResolvesOwnerOfThisRevisionNotStaleSharedPointer(): void {
    // Entity 2176's shared parent_id was last stamped by node 7661, which
    // holds revision 112666 - not the revision (82301) under test.
    $staleNode = $this->prophesize(ContentEntityInterface::class);
    $staleNode->id()->willReturn(7661);
    $staleNode->hasField('field_layout_canvas')->willReturn(TRUE);
    $staleNode->get('field_layout_canvas')->willReturn($this->createFieldItemList(2176, 112666));

    $publishedNode = $this->prophesize(ContentEntityInterface::class);
    $publishedNode->hasTranslation('en')->willReturn(FALSE);

    $query = $this->prophesize(QueryInterface::class);
    $query->allRevisions()->willReturn($query->reveal());
    $query->accessCheck(FALSE)->willReturn($query->reveal());
    $query->condition('field_layout_canvas.target_id', 2176)->willReturn($query->reveal());
    $query->condition('field_layout_canvas.target_revision_id', 82301)->willReturn($query->reveal());
    $query->sort('vid', 'DESC')->willReturn($query->reveal());
    $query->range(0, 1)->willReturn($query->reveal());
    // The revision-field lookup correctly finds node 7656 as the owner of
    // THIS revision (82301), regardless of entity 2176's shared parent_id
    // currently pointing at node 7661.
    $query->execute()->willReturn([9001 => 7656]);

    $entityType = $this->prophesize(EntityTypeInterface::class);
    $entityType->getKey('revision')->willReturn('vid');

    $storage = $this->prophesize(RevisionableStorageInterface::class);
    $storage->load(7661)->willReturn($staleNode->reveal());
    $storage->getQuery()->willReturn($query->reveal());
    $storage->getEntityType()->willReturn($entityType->reveal());
    $storage->loadRevision(9001)->willReturn($publishedNode->reveal());

    $entityTypeManager = $this->prophesize(EntityTypeManagerInterface::class);
    $entityTypeManager->getStorage('node')->willReturn($storage->reveal());

    $layout = $this->createLayoutMock([
      'parent_type' => 'node',
      // Stale: entity 2176's shared parent_id was last stamped by node 7661.
      'parent_id' => 7661,
      'parent_field_name' => 'field_layout_canvas',
    ], 2176, 82301);

    $layout->method('entityTypeManager')->willReturn($entityTypeManager->reveal());

    $this->assertSame($publishedNode->reveal(), $layout->getParentEntity());
  }

  /**
   * Tests that getParentEntity() falls back to the stale entity-level
   * parent_id when neither the cheap path nor the revision-field query can
   * find a host referencing this specific (orphaned) revision.
   *
   * @covers ::getParentEntity
   */
  public function testGetParentEntityFallsBackToStoredParentIdWhenNoRevisionMatchFound(): void {
    $fallbackNode = $this->prophesize(ContentEntityInterface::class);
    $fallbackNode->id()->willReturn(7661);
    $fallbackNode->hasField('field_layout_canvas')->willReturn(TRUE);
    // The stale parent's current field value points at a different
    // revision (112666), so the cheap path can't confirm revision 999999.
    $fallbackNode->get('field_layout_canvas')->willReturn($this->createFieldItemList(2176, 112666));
    $fallbackNode->hasTranslation('en')->willReturn(FALSE);

    $query = $this->prophesize(QueryInterface::class);
    $query->allRevisions()->willReturn($query->reveal());
    $query->accessCheck(FALSE)->willReturn($query->reveal());
    $query->condition('field_layout_canvas.target_id', 2176)->willReturn($query->reveal());
    $query->condition('field_layout_canvas.target_revision_id', 999999)->willReturn($query->reveal());
    $query->sort('vid', 'DESC')->willReturn($query->reveal());
    $query->range(0, 1)->willReturn($query->reveal());
    // No host currently references this exact (orphaned) revision.
    $query->execute()->willReturn([]);

    $entityType = $this->prophesize(EntityTypeInterface::class);
    $entityType->getKey('revision')->willReturn('vid');

    $storage = $this->prophesize(RevisionableStorageInterface::class);
    $storage->load(7661)->willReturn($fallbackNode->reveal());
    $storage->getQuery()->willReturn($query->reveal());
    $storage->getEntityType()->willReturn($entityType->reveal());

    $entityTypeManager = $this->prophesize(EntityTypeManagerInterface::class);
    $entityTypeManager->getStorage('node')->willReturn($storage->reveal());

    $layout = $this->createLayoutMock([
      'parent_type' => 'node',
      'parent_id' => 7661,
      'parent_field_name' => 'field_layout_canvas',
    ], 2176, 999999);

    $layout->method('entityTypeManager')->willReturn($entityTypeManager->reveal());

    $this->assertSame($fallbackNode->reveal(), $layout->getParentEntity());
  }

  /**
   * Tests that getParentEntity() returns NULL when parent_type/parent_id are
   * not set.
   *
   * @covers ::getParentEntity
   */
  public function testGetParentEntityReturnsNullWhenParentNotSet(): void {
    $layout = $this->createLayoutMock([], 2176, 82301);

    $this->assertNull($layout->getParentEntity());
  }

  /**
   * Tests that getParentEntity() returns the resolved parent's translation
   * when one exists for the layout's own langcode.
   *
   * @covers ::getParentEntity
   */
  public function testGetParentEntityReturnsTranslationWhenAvailable(): void {
    $translation = $this->prophesize(ContentEntityInterface::class);

    $parentNode = $this->prophesize(ContentEntityInterface::class);
    $parentNode->id()->willReturn(7656);
    $parentNode->hasField('field_layout_canvas')->willReturn(TRUE);
    $parentNode->get('field_layout_canvas')->willReturn($this->createFieldItemList(2176, 82301));
    $parentNode->hasTranslation('fr')->willReturn(TRUE);
    $parentNode->getTranslation('fr')->willReturn($translation->reveal());

    $storage = $this->prophesize(RevisionableStorageInterface::class);
    $storage->load(7656)->willReturn($parentNode->reveal());

    $entityTypeManager = $this->prophesize(EntityTypeManagerInterface::class);
    $entityTypeManager->getStorage('node')->willReturn($storage->reveal());

    $layout = $this->createLayoutMock([
      'parent_type' => 'node',
      'parent_id' => 7656,
      'parent_field_name' => 'field_layout_canvas',
    ], 2176, 82301, 'fr');

    $layout->method('entityTypeManager')->willReturn($entityTypeManager->reveal());

    $this->assertSame($translation->reveal(), $layout->getParentEntity());
  }

  /**
   * Tests that getParentEntity() caches the resolved parent pointer per
   * entity id + revision id, so a second call for the same revision does not
   * repeat the resolution (field check / query) and only reloads the parent
   * through the storage.
   *
   * @covers ::getParentEntity
   */
  public function testGetParentEntityCachesResultPerRevision(): void {
    $parentNode = $this->prophesize(ContentEntityInterface::class);
    $parentNode->id()->willReturn(7656);
    $parentNode->hasField('field_layout_canvas')->shouldBeCalledTimes(1)->willReturn(TRUE);
    $parentNode->get('field_layout_canvas')->shouldBeCalledTimes(1)->willReturn($this->createFieldItemList(2176, 82301));
    $parentNode->hasTranslation('en')->shouldBeCalledTimes(2)->willReturn(FALSE);

    $storage = $this->prophesize(RevisionableStorageInterface::class);
    $storage->load(7656)->shouldBeCalledTimes(3)->willReturn($parentNode->reveal());

    $entityTypeManager = $this->prophesize(EntityTypeManagerInterface::class);
    $entityTypeManager->getStorage('node')->willReturn($storage->reveal());

    $layout = $this->createLayoutMock([
      'parent_type' => 'node',
      'parent_id' => 7656,
      'parent_field_name' => 'field_layout_canvas',
    ], 2176, 82301);

    $layout->method('entityTypeManager')->willReturn($entityTypeManager->reveal());

    $layout->getParentEntity();
    $layout->getParentEntity();
  }

}
