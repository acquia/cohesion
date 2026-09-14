<?php

namespace Drupal\Tests\cohesion\Unit\Services;

use Drupal\cohesion\Services\CohesionUtils;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\Core\Url;
use Drupal\file\FileInterface;
use Drupal\media\MediaInterface;
use Drupal\media\MediaSourceInterface;
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

    $prophecy = $this->prophesize(ModuleHandlerInterface::CLASS);
    $module_handler = $prophecy->reveal();

    $this->mockUnit = new CohesionUtils($theme_handler, $theme_manager, $entity_type_manager, $language_manager, $logger_channel, $config_factory, $module_handler);
  }

  public function testUrlProcessorWithSpaces(): void {
    $url = "http://domain.com/path/to/file with spaces.pdf";
    $this->assertEquals('http://domain.com/path/to/file%20with%20spaces.pdf', $this->mockUnit->urlProcessor($url));
  }

  public function testUrlProcessorWithRelativeSpaces(): void {
    $url = "/path/to/file with spaces.pdf";
    $this->assertEquals('/path/to/file%20with%20spaces.pdf', $this->mockUnit->urlProcessor($url));
  }

  public function testUrlProcessorWithInvalidUrl(): void {
    $url = "i am test";
    $this->assertEquals('', $this->mockUnit->urlProcessor($url));
  }

  public function testUrlProcessorWithMailto(): void {
    $url = "mailto:admin@example.com";
    $this->assertEquals('mailto:admin@example.com', $this->mockUnit->urlProcessor($url));
  }

  public function testUrlProcessorWithQuery(): void {
    $ext_url = "http://domain.com?value=test";
    $this->assertEquals('http://domain.com?value=test', $this->mockUnit->urlProcessor($ext_url));
    $int_url = "/my-page?value=test";
    $this->assertEquals('/my-page?value=test', $this->mockUnit->urlProcessor($int_url));
  }

  public function testUrlProcessorWithFragment(): void {
    $ext_url = "http://domain.com/page-1#123";
    $this->assertEquals('http://domain.com/page-1#123', $this->mockUnit->urlProcessor($ext_url));
    $int_url = "/page-1#123";
    $this->assertEquals('/page-1#123', $this->mockUnit->urlProcessor($int_url));
  }

  public function testUrlProcessorWithEncodedUrl(): void {
    $ext_url = "http://domain.com/path/to/file%20with%20spaces.pdf";
    $this->assertEquals('http://domain.com/path/to/file%20with%20spaces.pdf', $this->mockUnit->urlProcessor($ext_url));
    $int_url = "/path/to/file%20with%20spaces.pdf";
    $this->assertEquals('/path/to/file%20with%20spaces.pdf', $this->mockUnit->urlProcessor($int_url));
  }

  public function testpathRenderer(): void {
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

  /**
   * Tests pathRenderer with media entity when link_element_media setting is disabled.
   */
  public function testPathRendererMediaWithoutMediaLinkSetting(): void {
    $mediaUrl = $this->prophesize(Url::class);
    $mediaUrl->setAbsolute(FALSE)->willReturn($mediaUrl->reveal());
    $mediaUrl->toString()->willReturn('/media/1');

    $media = $this->prophesize(MediaInterface::class);
    $media->hasTranslation('en')->willReturn(FALSE);
    $media->toUrl()->willReturn($mediaUrl->reveal());

    $mediaStorage = $this->prophesize(EntityStorageInterface::class);
    $mediaStorage->load('1')->willReturn($media->reveal());

    $entityTypeManager = $this->prophesize(EntityTypeManagerInterface::class);
    $entityTypeManager->getStorage('media')->willReturn($mediaStorage->reveal());

    $language = $this->prophesize(LanguageInterface::class);
    $language->getId()->willReturn('en');

    $languageManager = $this->prophesize(LanguageManagerInterface::class);
    $languageManager->getCurrentLanguage()->willReturn($language->reveal());

    $config = $this->prophesize(\Drupal\Core\Config\ImmutableConfig::class);
    $config->get('link_element_media')->willReturn(FALSE);

    $frontendConfig = $this->prophesize(Config::class);

    $configFactory = $this->prophesize(ConfigFactoryInterface::class);
    $configFactory->get('cohesion.settings')->willReturn($config->reveal());
    $configFactory->getEditable('cohesion.frontend.settings')->willReturn($frontendConfig->reveal());

    $themeHandler = $this->prophesize(ThemeHandlerInterface::class)->reveal();
    $themeManager = $this->prophesize(ThemeManagerInterface::class)->reveal();
    $loggerChannel = $this->prophesize(LoggerChannelFactoryInterface::class)->reveal();
    $moduleHandler = $this->prophesize(ModuleHandlerInterface::class)->reveal();

    $container = new \Symfony\Component\DependencyInjection\ContainerBuilder();
    $container->set('config.factory', $configFactory->reveal());
    \Drupal::setContainer($container);

    $cohesionUtils = new CohesionUtils(
      $themeHandler,
      $themeManager,
      $entityTypeManager->reveal(),
      $languageManager->reveal(),
      $loggerChannel,
      $configFactory->reveal(),
      $moduleHandler
    );

    $result = $cohesionUtils->pathRenderer('media::1');
    $this->assertEquals('/media/1', $result);

    \Drupal::setContainer(new \Symfony\Component\DependencyInjection\ContainerBuilder());
  }

  /**
   * Tests pathRenderer with media entity when link_element_media setting is enabled.
   */
  public function testPathRendererMediaWithMediaLinkSetting(): void {
    $file = $this->prophesize(FileInterface::class);
    $file->getFileUri()->willReturn('public://test-file.pdf');

    $fieldItemList = new class($file->reveal()) {
      public $entity;
      public function __construct($entity) { $this->entity = $entity; }
      public function isEmpty() { return FALSE; }
    };

    $mediaSource = $this->prophesize(MediaSourceInterface::class);
    $mediaSource->getConfiguration()->willReturn(['source_field' => 'field_media_document']);
    $mediaSource->getPluginId()->willReturn('file');

    $media = $this->prophesize(MediaInterface::class);
    $media->getSource()->willReturn($mediaSource->reveal());
    $media->hasField('field_media_document')->willReturn(TRUE);
    $media->get('field_media_document')->willReturn($fieldItemList);

    $mediaStorage = $this->prophesize(EntityStorageInterface::class);
    $mediaStorage->load('1')->willReturn($media->reveal());

    $entityTypeManager = $this->prophesize(EntityTypeManagerInterface::class);
    $entityTypeManager->getStorage('media')->willReturn($mediaStorage->reveal());

    $config = $this->prophesize(\Drupal\Core\Config\ImmutableConfig::class);
    $config->get('link_element_media')->willReturn(TRUE);

    $frontendConfig = $this->prophesize(Config::class);

    $configFactory = $this->prophesize(ConfigFactoryInterface::class);
    $configFactory->get('cohesion.settings')->willReturn($config->reveal());
    $configFactory->getEditable('cohesion.frontend.settings')->willReturn($frontendConfig->reveal());

    $languageManager = $this->prophesize(LanguageManagerInterface::class)->reveal();
    $themeHandler = $this->prophesize(ThemeHandlerInterface::class)->reveal();
    $themeManager = $this->prophesize(ThemeManagerInterface::class)->reveal();
    $loggerChannel = $this->prophesize(LoggerChannelFactoryInterface::class)->reveal();
    $moduleHandler = $this->prophesize(ModuleHandlerInterface::class)->reveal();

    $fileUrlGenerator = $this->prophesize(\Drupal\Core\File\FileUrlGeneratorInterface::class);
    $fileUrlGenerator->generateString('public://test-file.pdf')->willReturn('/sites/default/files/test-file.pdf');

    $container = new \Symfony\Component\DependencyInjection\ContainerBuilder();
    $container->set('config.factory', $configFactory->reveal());
    $container->set('file_url_generator', $fileUrlGenerator->reveal());
    \Drupal::setContainer($container);

    $cohesionUtils = new CohesionUtils(
      $themeHandler,
      $themeManager,
      $entityTypeManager->reveal(),
      $languageManager,
      $loggerChannel,
      $configFactory->reveal(),
      $moduleHandler
    );

    $result = $cohesionUtils->pathRenderer('media::1');
    $this->assertEquals('/sites/default/files/test-file.pdf', $result);

    \Drupal::setContainer(new \Symfony\Component\DependencyInjection\ContainerBuilder());
  }

  /**
   * Tests pathRenderer with Acquia DAM media asset when link_element_media setting is enabled.
   */
  public function testPathRendererAcquiaDamMedia(): void {
    $fieldItemList = new class {
      public function isEmpty() { return FALSE; }
    };

    $mediaSource = $this->prophesize(MediaSourceInterface::class);
    $mediaSource->getConfiguration()->willReturn(['source_field' => 'field_media_acquia_dam_asset']);
    $mediaSource->getPluginId()->willReturn('acquia_dam_asset:image');
    $mediaSource->getSourceFieldValue(\Prophecy\Argument::any())->willReturn([
      'asset_id' => 'abc-123',
      'version_id' => 'v456',
    ]);

    $media = $this->prophesize(MediaInterface::class);
    $media->getSource()->willReturn($mediaSource->reveal());
    $media->hasField('field_media_acquia_dam_asset')->willReturn(TRUE);
    $media->get('field_media_acquia_dam_asset')->willReturn($fieldItemList);

    $mediaStorage = $this->prophesize(EntityStorageInterface::class);
    $mediaStorage->load('1')->willReturn($media->reveal());

    $entityTypeManager = $this->prophesize(EntityTypeManagerInterface::class);
    $entityTypeManager->getStorage('media')->willReturn($mediaStorage->reveal());

    $config = $this->prophesize(\Drupal\Core\Config\ImmutableConfig::class);
    $config->get('link_element_media')->willReturn(TRUE);

    $frontendConfig = $this->prophesize(Config::class);

    $configFactory = $this->prophesize(ConfigFactoryInterface::class);
    $configFactory->get('cohesion.settings')->willReturn($config->reveal());
    $configFactory->getEditable('cohesion.frontend.settings')->willReturn($frontendConfig->reveal());

    $languageManager = $this->prophesize(LanguageManagerInterface::class)->reveal();
    $themeHandler = $this->prophesize(ThemeHandlerInterface::class)->reveal();
    $themeManager = $this->prophesize(ThemeManagerInterface::class)->reveal();
    $loggerChannel = $this->prophesize(LoggerChannelFactoryInterface::class)->reveal();
    $moduleHandler = $this->prophesize(ModuleHandlerInterface::class)->reveal();

    $fileUrlGenerator = $this->prophesize(\Drupal\Core\File\FileUrlGeneratorInterface::class);
    $fileUrlGenerator->generateString('acquia-dam://abc-123/v456')->willReturn('https://cdn.acquia-dam.com/abc-123/v456');

    $container = new \Symfony\Component\DependencyInjection\ContainerBuilder();
    $container->set('config.factory', $configFactory->reveal());
    $container->set('file_url_generator', $fileUrlGenerator->reveal());
    \Drupal::setContainer($container);

    $cohesionUtils = new CohesionUtils(
      $themeHandler,
      $themeManager,
      $entityTypeManager->reveal(),
      $languageManager,
      $loggerChannel,
      $configFactory->reveal(),
      $moduleHandler
    );

    $result = $cohesionUtils->pathRenderer('media::1');
    $this->assertEquals('https://cdn.acquia-dam.com/abc-123/v456', $result);

    \Drupal::setContainer(new \Symfony\Component\DependencyInjection\ContainerBuilder());
  }

  /**
   * Tests pathRenderer with media entity that does not exist.
   */
  public function testPathRendererMediaNotFound(): void {
    $mediaStorage = $this->prophesize(EntityStorageInterface::class);
    $mediaStorage->load('999')->willReturn(NULL);

    $entityTypeManager = $this->prophesize(EntityTypeManagerInterface::class);
    $entityTypeManager->getStorage('media')->willReturn($mediaStorage->reveal());

    $themeHandler = $this->prophesize(ThemeHandlerInterface::class)->reveal();
    $themeManager = $this->prophesize(ThemeManagerInterface::class)->reveal();
    $languageManager = $this->prophesize(LanguageManagerInterface::class)->reveal();
    $loggerChannel = $this->prophesize(LoggerChannelFactoryInterface::class)->reveal();
    $configFactory = $this->prophesize(ConfigFactoryInterface::class)->reveal();
    $moduleHandler = $this->prophesize(ModuleHandlerInterface::class)->reveal();

    $cohesionUtils = new CohesionUtils(
      $themeHandler,
      $themeManager,
      $entityTypeManager->reveal(),
      $languageManager,
      $loggerChannel,
      $configFactory,
      $moduleHandler
    );

    $result = $cohesionUtils->pathRenderer('media::999');
    $this->assertEquals('media::999', $result);
  }

  /**
   * Data provider for testApiKeysConfigured.
   */
  public static function apiKeysConfiguredDataProvider(): array {
    return [
      'both keys set' => ['test-api-key', 'test-org-key', TRUE],
      'api key missing' => ['', 'test-org-key', FALSE],
      'api key null' => [NULL, 'test-org-key', FALSE],
      'organization key missing' => ['test-api-key', '', FALSE],
      'organization key null' => ['test-api-key', NULL, FALSE],
      'both keys missing' => ['', '', FALSE],
      'both keys null' => [NULL, NULL, FALSE],
    ];
  }

  /**
   * Tests apiKeysConfigured method.
   *
   * @dataProvider apiKeysConfiguredDataProvider
   */
  public function testApiKeysConfigured($apiKey, $orgKey, $expected): void {
    $config = $this->prophesize(\Drupal\Core\Config\ImmutableConfig::class);
    $config->get('api_key')->willReturn($apiKey);
    $config->get('organization_key')->willReturn($orgKey);

    $frontendConfig = $this->prophesize(Config::class);

    $configFactory = $this->prophesize(ConfigFactoryInterface::class);
    $configFactory->get('cohesion.settings')->willReturn($config->reveal());
    $configFactory->getEditable('cohesion.frontend.settings')->willReturn($frontendConfig->reveal());

    $cohesionUtils = new CohesionUtils(
      $this->prophesize(ThemeHandlerInterface::class)->reveal(),
      $this->prophesize(ThemeManagerInterface::class)->reveal(),
      $this->prophesize(EntityTypeManagerInterface::class)->reveal(),
      $this->prophesize(LanguageManagerInterface::class)->reveal(),
      $this->prophesize(LoggerChannelFactoryInterface::class)->reveal(),
      $configFactory->reveal(),
      $this->prophesize(ModuleHandlerInterface::class)->reveal()
    );

    $this->assertEquals($expected, $cohesionUtils->apiKeysConfigured());
  }

}
