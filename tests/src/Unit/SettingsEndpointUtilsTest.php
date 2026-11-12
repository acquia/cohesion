<?php

namespace Drupal\Tests\cohesion\Unit;

use Drupal\cohesion\SettingsEndpointUtils;
use Drupal\Tests\UnitTestCase;

/**
 * @group Cohesion
 */
class SettingsEndpointUtilsTest extends UnitTestCase {

  /**
   * The settings endpint utils service being tested
   *
   * @var \Drupal\cohesion\SettingsEndpointUtils
   */
  protected $unit;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $color_storage;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $cohesionApiElementStorage = $this->createMock('Drupal\cohesion\CohesionApiElementStorage');
    $this->entityTypeManager = $this->createMock('\Drupal\Core\Entity\EntityTypeManagerInterface');
    $entity_group_manager = $this->createMock('\Drupal\cohesion\EntityGroupsPluginManager');

    $this->color_storage = $this->createMock('\Drupal\Core\Entity\EntityStorageInterface');

    $this->unit = new SettingsEndpointUtils($cohesionApiElementStorage, $this->entityTypeManager, $entity_group_manager);
  }

  /**
   * @dataProvider getColorsListDataProvider
   *
   * @covers \Drupal\cohesion\SettingsEndpointUtils::getColorsList
   */
  public function testGetColorsList($colors_data, $item, $expected) {

    $colors = [];

    foreach ($colors_data as $color) {
      $color_mock = $this->createMock('Drupal\cohesion_website_settings\Entity\Color');
      $color_mock->expects($this->any())
        ->method('getWeight')
        ->willReturn($color['weight']);
      $color_mock->expects($this->any())
        ->method('getDecodedJsonValues')
        ->willReturn($color['json_values']);
      $colors[] = $color_mock;
    }

    $this->color_storage->expects($this->any())
      ->method('loadMultiple')
      ->willReturn($colors);

    $this->entityTypeManager->expects($this->any())
      ->method('getStorage')
      ->willReturn($this->color_storage);


    $all_colors = $this->unit->getColorsList($item);
    $this->assertEquals($expected, $all_colors);
  }

  /**
   * Data provider for ::testGetColorsList.
   *
   * @return array
   *   Data set
   */
  public function getColorsListDataProvider() {
    $test_data = [];

    $colors = [];

    $colors['white'] = [
      'weight' => 0,
      'json_values' => [
        'variable' => '$white_var',
      ],
    ];

    $colors['black'] = [
      'weight' => 0,
      'json_values' => [
        'variable' => '$back_var',
      ],
    ];

    $colors['null'] = [
      'weight' => 2,
      'json_values' => NULL,
    ];

    $colors['yellow'] = [
      'weight' => 2,
      'json_values' => [
        'variable' => '$yellow_var',
      ],
    ];

    $expected_colors = [
      $colors['white']['json_values'],
      $colors['black']['json_values'],
      $colors['yellow']['json_values'],
    ];

    $test_data[] = [
      array_values($colors),
      NULL,
      $expected_colors,
    ];

    // One existing color.
    $test_data[] = [
      array_values($colors),
      $colors['black']['json_values']['variable'],
      [$colors['black']['json_values']],
    ];

    // One non existing color.
    $test_data[] = [
      array_values($colors),
      '$non-existing-color',
      [],
    ];

    return $test_data;
  }
}
