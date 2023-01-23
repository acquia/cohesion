<?php

namespace Drupal\Tests\cohesion\Unit\Services;

use Drupal\cohesion\Services\CohesionUtils;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\Tests\UnitTestCase;

/**
 * @group Cohesion
 */
class CohesionUtilsTest extends UnitTestCase {

  /**
   * @var \Drupal\cohesion\Services\CohesionUtils
   */
  protected $mockUnit;

  /**
   *
   */
  public function setUp() {

    $prophecy = $this->prophesize(ThemeHandlerInterface::CLASS);
    $theme_handler = $prophecy->reveal();

    $prophecy = $this->prophesize(ThemeManagerInterface::CLASS);
    $theme_manager = $prophecy->reveal();

    $prophecy = $this->prophesize(EntityTypeManagerInterface::CLASS);
    $entity_type_manager = $prophecy->reveal();

    $prophecy = $this->prophesize(LanguageManagerInterface::CLASS);
    $language_manager = $prophecy->reveal();

    $prophecy = $this->prophesize(LoggerChannelFactoryInterface::CLASS);
    $logger_channel = $prophecy->reveal();

    $this->mockUnit = new CohesionUtils($theme_handler, $theme_manager, $entity_type_manager, $language_manager, $logger_channel);
  }

  public function testUrlProcessorWithSpaces() {
    $url = "http://domain.com/path/to/file with spaces.pdf";
    $this->assertEquals('http://domain.com/path/to/file%20with%20spaces.pdf', $this->mockUnit->urlProcessor($url));
  }

  public function testUrlProcessorWithRelativeSpaces() {
    $url = "/path/to/file with spaces.pdf";
    $this->assertEquals('/path/to/file%20with%20spaces.pdf', $this->mockUnit->urlProcessor($url));
  }

  public function testUrlProcessorWithInvalidUrl() {
    $url = "i am test";
    $this->assertEquals('', $this->mockUnit->urlProcessor($url));
  }

}
