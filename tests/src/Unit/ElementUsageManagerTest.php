<?php

namespace Drupal\Tests\cohesion\Unit;

use Drupal\cohesion_elements\ElementUsageManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\cohesion_elements\ElementUsageManager
 * @group cohesion
 */
class ElementUsageManagerTest extends UnitTestCase {

  /**
   * The config factory mock.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configFactory;

  /**
   * The ElementUsageManager instance.
   *
   * @var \Drupal\cohesion_elements\ElementUsageManager
   */
  protected $elementUsageManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create a mock for the config factory.
    $this->configFactory = $this->createMock(ConfigFactoryInterface::class);

    // Create an instance of the ElementUsageManager with the mocked dependencies.
    $this->elementUsageManager = new ElementUsageManager(
      $this->createMock(\Drupal\Core\Database\Connection::class),
      $this->createMock(\Drupal\Core\Entity\EntityRepository::class),
      $this->createMock(\Drupal\Core\Entity\EntityTypeManagerInterface::class),
      $this->createMock(\Drupal\Core\Logger\LoggerChannelFactoryInterface::class),
      $this->createMock(\Drupal\Core\Render\RendererInterface::class),
      $this->createMock(\Drupal\Component\Datetime\TimeInterface::class),
      $this->createMock(\Drupal\Core\Queue\QueueFactory::class),
      $this->configFactory,
      $this->createMock(\Drupal\cohesion\SettingsEndpointUtils::class),
      $this->createMock(\Drupal\Core\KeyValueStore\KeyValueFactoryInterface::class)
    );
  }

  /**
   * Tests the getDisabledElements method.
   *
   * @covers ::getDisabledElements
   */
  public function testGetDisabledElements() {
    // Create a mock for the config object.
    $config = $this->createMock(ImmutableConfig::class);

    // Set up the config factory to return the mock config object.
    $this->configFactory->expects($this->any())
      ->method('getEditable')
      ->with('cohesion.settings')
      ->willReturn($config);

    // Define the test data.
    $configData = json_encode([
      'element1' => 0,
      'element2' => 1,
      'element3' => 0,
    ]);

    // Set up the config object to return the test data.
    $config->expects($this->any())
      ->method('get')
      ->with('element_toggle')
      ->willReturn($configData);

    // Call the method and assert the result.
    $result = $this->elementUsageManager->getDisabledElements();
    $expected = ['element1', 'element3'];
    $this->assertEquals($expected, $result);
  }

  /**
   * Tests the getDisabledElements method.
   *
   * @covers ::getDisabledElements
   */
  public function testGetDisabledElementsEmpty() {
    // Create a mock for the config object.
    $config = $this->createMock(ImmutableConfig::class);

    // Set up the config factory to return the mock config object.
    $this->configFactory->expects($this->any())
      ->method('getEditable')
      ->with('cohesion.settings')
      ->willReturn($config);

    // Define the empty test data.
    $emptyConfigData = json_encode([]);

    // Set up the config object to return the test data.
    $config->expects($this->any())
      ->method('get')
      ->with('element_toggle')
      ->willReturn($emptyConfigData);

    // Call the method and assert the result.
    $result = $this->elementUsageManager->getDisabledElements();
    $expected = [];
    $this->assertEquals($expected, $result);
  }
}
