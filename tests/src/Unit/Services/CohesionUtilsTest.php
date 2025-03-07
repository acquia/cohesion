<?php

namespace Drupal\Tests\cohesion\Unit\Services;

use Drupal\cohesion\Services\CohesionUtils;
use Drupal\Core\Config\ConfigFactoryInterface;
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
  public function setUp(): void {

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

    $prophecy = $this->prophesize(ConfigFactoryInterface::CLASS);
    $config_factory = $prophecy->reveal();

    $this->mockUnit = new CohesionUtils($theme_handler, $theme_manager, $entity_type_manager, $language_manager, $logger_channel, $config_factory);
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

  public function testUrlProcessorWithMailto() {
    $url = "mailto:admin@example.com";
    $this->assertEquals('mailto:admin@example.com', $this->mockUnit->urlProcessor($url));
  }

  public function testUrlProcessorWithQuery() {
    $ext_url = "http://domain.com?value=test";
    $this->assertEquals('http://domain.com?value=test', $this->mockUnit->urlProcessor($ext_url));
    $int_url = "/my-page?value=test";
    $this->assertEquals('/my-page?value=test', $this->mockUnit->urlProcessor($int_url));
  }

  public function testUrlProcessorWithFragment() {
    $ext_url = "http://domain.com/page-1#123";
    $this->assertEquals('http://domain.com/page-1#123', $this->mockUnit->urlProcessor($ext_url));
    $int_url = "/page-1#123";
    $this->assertEquals('/page-1#123', $this->mockUnit->urlProcessor($int_url));
  }

  public function testUrlProcessorWithEncodedUrl() {
    $ext_url = "http://domain.com/path/to/file%20with%20spaces.pdf";
    $this->assertEquals('http://domain.com/path/to/file%20with%20spaces.pdf', $this->mockUnit->urlProcessor($ext_url));
    $int_url = "/path/to/file%20with%20spaces.pdf";
    $this->assertEquals('/path/to/file%20with%20spaces.pdf', $this->mockUnit->urlProcessor($int_url));
  }

  public function testpathRenderer() {
      $urls = [
        'https://example.something.com/test/a?b=123:LOGIN:::::',
        'https://www.google.com',
        'node::7',
        'view::archive::page_1',
        'mailto:test@acquia.com',
      ];

      foreach ($urls as $url) {
        $this->assertEquals($url, $this->mockUnit->pathRenderer($url));
      }
  }

}
