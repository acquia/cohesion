<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\Plugin\EntityUpdate\_0044EntityUpdate;

class _0044MockUpdateEntity extends EntityMockBase implements EntityJsonValuesInterface {}

/**
 * @group Cohesion
 */
class _0044EntityUpdateUnitTest extends EntityUpdateUnitTestCase {

  protected $unit;

  const MIGRATES_HIDE_DATA_PRE  = '{"model":[{"hideNoData":{"hideData":"token1"}}]}';
  const MIGRATES_HIDE_DATA_POST = '{"model":[{"hideNoData":{"hideDataFields":[{"hideDataField":"token1"}]}}]}';

  const EMPTY_HIDE_DATA_PRE  = '{"model":[{"hideNoData":{"hideData":"   "}}]}';
  const EMPTY_HIDE_DATA_POST = '{"model":[{"hideNoData":{}}]}';

  const NO_DUPLICATE_PRE  = '{"model":[{"hideNoData":{"hideData":"token1","hideDataFields":[{"hideDataField":"token1"}]}}]}';
  const NO_DUPLICATE_POST = '{"model":[{"hideNoData":{"hideDataFields":[{"hideDataField":"token1"}]}}]}';

  const PREPEND_PRE  = '{"model":[{"hideNoData":{"hideData":"token1","hideDataFields":[{"hideDataField":"token2"}]}}]}';
  const PREPEND_POST = '{"model":[{"hideNoData":{"hideDataFields":[{"hideDataField":"token1"},{"hideDataField":"token2"}]}}]}';

  const MULTI_PRE  = '{"model":[{"hideNoData":{"hideData":"token1"}},{"hideNoData":{"hideData":"token2"}}]}';
  const MULTI_POST = '{"model":[{"hideNoData":{"hideDataFields":[{"hideDataField":"token1"}]}},{"hideNoData":{"hideDataFields":[{"hideDataField":"token2"}]}}]}';

  const NON_SCALAR_PRE = '{"model":[{"hideNoData":{"hideData":{"nested":"value"}}}]}';
  const NO_MODEL       = '{"foo":1}';

  public function setUp(): void {
    $this->unit = new _0044EntityUpdate([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0044EntityUpdate::runUpdate
   */
  public function testRunUpdate(): void {
    $entity = new _0044MockUpdateEntity(self::MIGRATES_HIDE_DATA_PRE, TRUE);
    $this->assertionsBefore($entity->getDecodedJsonValues());
    $this->unit->runUpdate($entity);
    $this->assertionsAfter($entity->getDecodedJsonValues());
  }

  public function testEmptyHideDataRemovesPropertyOnly(): void {
    $entity = new _0044MockUpdateEntity(self::EMPTY_HIDE_DATA_PRE, TRUE);
    $this->unit->runUpdate($entity);
    $this->assertEquals(json_decode(self::EMPTY_HIDE_DATA_POST, TRUE), $entity->getDecodedJsonValues());
  }

  public function testNoDuplicateWhenValueAlreadyPresent(): void {
    $entity = new _0044MockUpdateEntity(self::NO_DUPLICATE_PRE, TRUE);
    $this->unit->runUpdate($entity);
    $this->assertEquals(json_decode(self::NO_DUPLICATE_POST, TRUE), $entity->getDecodedJsonValues());
  }

  public function testPrependsToExistingHideDataFields(): void {
    $entity = new _0044MockUpdateEntity(self::PREPEND_PRE, TRUE);
    $this->unit->runUpdate($entity);
    $this->assertEquals(json_decode(self::PREPEND_POST, TRUE), $entity->getDecodedJsonValues());
  }

  public function testMultipleElementsMigratedIndependently(): void {
    $entity = new _0044MockUpdateEntity(self::MULTI_PRE, TRUE);
    $this->unit->runUpdate($entity);
    $this->assertEquals(json_decode(self::MULTI_POST, TRUE), $entity->getDecodedJsonValues());
  }

  public function testSkipsNonScalarHideData(): void {
    $entity = new _0044MockUpdateEntity(self::NON_SCALAR_PRE, TRUE);
    $this->unit->runUpdate($entity);
    $this->assertEquals(json_decode(self::NON_SCALAR_PRE, TRUE), $entity->getDecodedJsonValues());
  }

  public function testNoModelPropertySkipsProcessing(): void {
    $entity = new _0044MockUpdateEntity(self::NO_MODEL, TRUE);
    $this->unit->runUpdate($entity);
    $this->assertEquals(json_decode(self::NO_MODEL, TRUE), $entity->getDecodedJsonValues());
  }

  public function testNonLayoutCanvasIsSkipped(): void {
    $entity = new _0044MockUpdateEntity(self::MIGRATES_HIDE_DATA_PRE, FALSE);
    $this->unit->runUpdate($entity);
    $this->assertEquals(json_decode(self::MIGRATES_HIDE_DATA_PRE, TRUE), $entity->getDecodedJsonValues());
  }

  private function assertionsBefore(array $values): void {
    $this->assertArrayHasKey('hideData', $values['model'][0]['hideNoData']);
    $this->assertArrayNotHasKey('hideDataFields', $values['model'][0]['hideNoData']);
  }

  private function assertionsAfter(array $values): void {
    $this->assertArrayNotHasKey('hideData', $values['model'][0]['hideNoData']);
    $this->assertArrayHasKey('hideDataFields', $values['model'][0]['hideNoData']);
    $this->assertCount(1, $values['model'][0]['hideNoData']['hideDataFields']);
    $this->assertEquals('token1', $values['model'][0]['hideNoData']['hideDataFields'][0]['hideDataField']);
  }

}