<?php

namespace Drupal\Tests\cohesion\Unit;

use Drupal\cohesion\ImageBrowserPluginManager;
use Drupal\cohesion\ImageBrowserUpdateManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\file\FileInterface;
use Drupal\media\MediaInterface;
use Drupal\media\MediaSourceInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Test double combining MediaSourceInterface with the Acquia DAM module's
 * getLocalFileAssetField() method, which is not part of core's
 * MediaSourceInterface and is detected via method_exists() in the code
 * under test.
 */
interface AcquiaDamLocalAssetMediaSourceInterface extends MediaSourceInterface {
  public function getLocalFileAssetField(): string;
}

/**
 * @coversDefaultClass \Drupal\cohesion\ImageBrowserUpdateManager
 *
 * @group Cohesion
 */
class ImageBrowserUpdateManagerTest extends UnitTestCase {

  /**
   * Tests decodeToken() prefers the locally synced copy of an Acquia DAM
   * asset when "Download and sync assets" (download_assets) is enabled on
   * the media source.
   */
  public function testDecodeTokenAcquiaDamMediaWithDownloadAssetsEnabled(): void {
    $localFile = $this->prophesize(FileInterface::class);
    $localFile->getFileUri()->willReturn('public://acquia-dam/local-abc-123.jpg');

    $localFieldItemList = new class($localFile->reveal()) {
      public $entity;
      public function __construct($entity) { $this->entity = $entity; }
      public function isEmpty() { return FALSE; }
    };

    $mediaSource = $this->prophesize(AcquiaDamLocalAssetMediaSourceInterface::class);
    $mediaSource->getPluginId()->willReturn('acquia_dam_asset:image');
    $mediaSource->getConfiguration()->willReturn(['download_assets' => TRUE]);
    $mediaSource->getLocalFileAssetField()->willReturn('acquia_dam_managed_image');
    $mediaSource->getSourceFieldValue(\Prophecy\Argument::any())->willReturn([
      'asset_id' => 'abc-123',
      'version_id' => 'v456',
    ]);

    $media = $this->prophesize(MediaInterface::class);
    $media->getSource()->willReturn($mediaSource->reveal());
    $media->hasField('acquia_dam_managed_image')->willReturn(TRUE);
    $media->get('acquia_dam_managed_image')->willReturn($localFieldItemList);
    $media->label()->willReturn('My DAM asset');

    $entityRepository = $this->prophesize(EntityRepositoryInterface::class);
    $entityRepository->loadEntityByUuid('media', 'some-uuid')->willReturn($media->reveal());

    $manager = $this->createManagerWithEntityRepository($entityRepository->reveal());

    $result = $manager->decodeToken('[media:media:some-uuid]');

    $this->assertIsArray($result);
    $this->assertEquals('public://acquia-dam/local-abc-123.jpg', $result['path']);
    $this->assertEquals('My DAM asset', $result['label']);
  }

  /**
   * Tests decodeToken() falls back to the remote acquia-dam:// URI when
   * download_assets is enabled but the local asset field is empty.
   */
  public function testDecodeTokenAcquiaDamMediaWithDownloadAssetsEnabledButLocalFieldEmpty(): void {
    $emptyLocalFieldItemList = new class {
      public function isEmpty() { return TRUE; }
    };

    $mediaSource = $this->prophesize(AcquiaDamLocalAssetMediaSourceInterface::class);
    $mediaSource->getPluginId()->willReturn('acquia_dam_asset:image');
    $mediaSource->getConfiguration()->willReturn(['download_assets' => TRUE]);
    $mediaSource->getLocalFileAssetField()->willReturn('acquia_dam_managed_image');
    $mediaSource->getSourceFieldValue(\Prophecy\Argument::any())->willReturn([
      'asset_id' => 'abc-123',
      'version_id' => 'v456',
    ]);

    $media = $this->prophesize(MediaInterface::class);
    $media->getSource()->willReturn($mediaSource->reveal());
    $media->hasField('acquia_dam_managed_image')->willReturn(TRUE);
    $media->get('acquia_dam_managed_image')->willReturn($emptyLocalFieldItemList);
    $media->label()->willReturn('My DAM asset');

    $entityRepository = $this->prophesize(EntityRepositoryInterface::class);
    $entityRepository->loadEntityByUuid('media', 'some-uuid')->willReturn($media->reveal());

    $manager = $this->createManagerWithEntityRepository($entityRepository->reveal());

    $result = $manager->decodeToken('[media:media:some-uuid]');

    $this->assertIsArray($result);
    $this->assertEquals('acquia-dam://abc-123/v456', $result['path']);
    $this->assertEquals('My DAM asset', $result['label']);
  }

  /**
   * Tests decodeToken() uses the remote acquia-dam:// URI when
   * download_assets is disabled on the media source.
   */
  public function testDecodeTokenAcquiaDamMediaWithDownloadAssetsDisabled(): void {
    $mediaSource = $this->prophesize(MediaSourceInterface::class);
    $mediaSource->getPluginId()->willReturn('acquia_dam_asset:image');
    $mediaSource->getConfiguration()->willReturn(['download_assets' => FALSE]);
    $mediaSource->getSourceFieldValue(\Prophecy\Argument::any())->willReturn([
      'asset_id' => 'abc-123',
      'version_id' => 'v456',
    ]);

    $media = $this->prophesize(MediaInterface::class);
    $media->getSource()->willReturn($mediaSource->reveal());
    $media->label()->willReturn('My DAM asset');

    $entityRepository = $this->prophesize(EntityRepositoryInterface::class);
    $entityRepository->loadEntityByUuid('media', 'some-uuid')->willReturn($media->reveal());

    $manager = $this->createManagerWithEntityRepository($entityRepository->reveal());

    $result = $manager->decodeToken('[media:media:some-uuid]');

    $this->assertIsArray($result);
    $this->assertEquals('acquia-dam://abc-123/v456', $result['path']);
    $this->assertEquals('My DAM asset', $result['label']);
  }

  /**
   * Builds an ImageBrowserUpdateManager with no active image browser plugins
   * configured and the given entity repository.
   */
  protected function createManagerWithEntityRepository(EntityRepositoryInterface $entityRepository): ImageBrowserUpdateManager {
    $config = $this->prophesize(ImmutableConfig::class);
    $config->get('image_browser')->willReturn(NULL);

    $configFactory = $this->prophesize(ConfigFactoryInterface::class);
    $configFactory->get('cohesion.settings')->willReturn($config->reveal());

    $pluginManager = $this->prophesize(ImageBrowserPluginManager::class)->reveal();
    $moduleHandler = $this->prophesize(ModuleHandlerInterface::class)->reveal();

    return new ImageBrowserUpdateManager(
      $configFactory->reveal(),
      $pluginManager,
      $moduleHandler,
      $entityRepository
    );
  }

}
