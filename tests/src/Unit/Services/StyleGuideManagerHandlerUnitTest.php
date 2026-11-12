<?php

namespace Drupal\Tests\cohesion_style_guide\Unit\Services;

use Drupal\cohesion\Services\CohesionUtils;
use Drupal\cohesion\UsageUpdateManager;
use Drupal\cohesion_style_guide\Services\StyleGuideManagerHandler;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Tests\UnitTestCase;

/**
 * @group Cohesion
 */
class StyleGuideManagerHandlerUnitTest extends UnitTestCase {

  /**
   * @var \Drupal\cohesion_style_guide\Services\StyleGuideManagerHandler
   */
  protected $mockUnit;

  /**
   *
   */
  public function setUp(): void {
    // Create a mock of the classes required to init StyleGuideManagerHandler.
    $prophecy = $this->prophesize(EntityTypeManagerInterface::CLASS);
    $entity_type_manager = $prophecy->reveal();

    $prophecy = $this->prophesize(EntityRepositoryInterface::CLASS);
    $entity_repository = $prophecy->reveal();

    $prophecy = $this->prophesize(UsageUpdateManager::CLASS);
    $usage_update_manager = $prophecy->reveal();

    $prophecy = $this->prophesize(ThemeHandlerInterface::CLASS);
    $theme_handler = $prophecy->reveal();

    $prophecy = $this->prophesize(CohesionUtils::CLASS);
    $cohesion_utils = $prophecy->reveal();

    $this->mockUnit = new StyleGuideManagerHandler($entity_type_manager, $entity_repository, $usage_update_manager, $theme_handler, $cohesion_utils);
  }

  /**
   * @covers \Drupal\cohesion_style_guide\Services\StyleGuideManagerHandler::mergeChildJson
   */
  public function testMergeChildJson() {
    $results = $this->mockUnit->mergeChildJson(json_decode('{"model":{"d73489ab-80cc-4a11-8fdf-e60a100f3ba6":{"266bbf5a-1ab2-40c3-9088-6e02a1d48666":"test input","dadd7a1a-b3d7-4523-87e2-1865f27e001c":true,"3919984d-07d7-40c6-9c3c-accb42d7fbdf":{"name":"Gray","uid":"gray","value":{"hex":"#bbbbbb","rgba":"rgba(128, 128, 128, 1)"},"wysiwyg":true,"class":".coh-color-gray","variable":"$coh-color-gray","inuse":false},"3baa5bff-d568-4fa7-8d77-928c22e3bbee":2,"bfcc1633-f2af-4ec1-b1fa-1e8c08003101":"$coh-font-arial"}}}'), json_decode('{"model":{"d73489ab-80cc-4a11-8fdf-e60a100f3ba6":{"dadd7a1a-b3d7-4523-87e2-1865f27e001c":false,"3baa5bff-d568-4fa7-8d77-928c22e3bbee":5}},"changedFields":["model.d73489ab-80cc-4a11-8fdf-e60a100f3ba6.dadd7a1a-b3d7-4523-87e2-1865f27e001c","model.d73489ab-80cc-4a11-8fdf-e60a100f3ba6.3baa5bff-d568-4fa7-8d77-928c22e3bbee"]}'));
    $this->assertEquals('{"model":{"d73489ab-80cc-4a11-8fdf-e60a100f3ba6":{"266bbf5a-1ab2-40c3-9088-6e02a1d48666":"test input","dadd7a1a-b3d7-4523-87e2-1865f27e001c":false,"3919984d-07d7-40c6-9c3c-accb42d7fbdf":{"name":"Gray","uid":"gray","value":{"hex":"#bbbbbb","rgba":"rgba(128, 128, 128, 1)"},"wysiwyg":true,"class":".coh-color-gray","variable":"$coh-color-gray","inuse":false},"3baa5bff-d568-4fa7-8d77-928c22e3bbee":5,"bfcc1633-f2af-4ec1-b1fa-1e8c08003101":"$coh-font-arial"}},"changedFields":["model.d73489ab-80cc-4a11-8fdf-e60a100f3ba6.dadd7a1a-b3d7-4523-87e2-1865f27e001c","model.d73489ab-80cc-4a11-8fdf-e60a100f3ba6.3baa5bff-d568-4fa7-8d77-928c22e3bbee"]}', json_encode($results));
  }

}
