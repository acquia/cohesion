<?php

namespace Drupal\Tests\cohesion\Unit\Controller;

use Drupal\cohesion\Controller\CohesionEndpointController;
use Drupal\cohesion\CohesionJsonResponse;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @group Cohesion
 */
class CohesionEndpointControllerUnitTest extends UnitTestCase {

  protected $entityFieldManager;
  protected $entityTypeBundleInfo;
  protected $entityRepository;
  protected $cohesionUtils;
  protected $requestStack;
  protected $customComponentsService;
  protected $user;
  protected $fileRepository;
  protected $fileUrlGenerator;
  protected $extensionPathResolver;
  protected $controller;
  protected $entityTypeManager;
  protected $stringTranslation;


  public function setUp(): void {
    parent::setUp();

    // Mock all required services and dependencies.
    $this->entityFieldManager = $this->createMock(\Drupal\Core\Entity\EntityFieldManagerInterface::class);
    $this->entityTypeBundleInfo = $this->createMock(\Drupal\Core\Entity\EntityTypeBundleInfoInterface::class);
    $this->entityRepository = $this->createMock(\Drupal\Core\Entity\EntityRepositoryInterface::class);
    $this->cohesionUtils = $this->createMock(\Drupal\cohesion\Services\CohesionUtils::class);
    $this->requestStack = $this->createMock(RequestStack::class);
    $this->customComponentsService = $this->createMock(\Drupal\cohesion_elements\CustomComponentsService::class);
    $this->user = $this->createMock(\Drupal\Core\Session\AccountInterface::class);
    $this->fileRepository = $this->createMock(\Drupal\file\FileRepositoryInterface::class);
    $this->fileUrlGenerator = $this->createMock(\Drupal\Core\File\FileUrlGeneratorInterface::class);
    $this->extensionPathResolver = $this->createMock(\Drupal\Core\Extension\ExtensionPathResolver::class);
    $this->entityTypeManager = $this->createMock(\Drupal\Core\Entity\EntityTypeManagerInterface::class);
    $this->stringTranslation = $this->createMock(\Drupal\Core\StringTranslation\TranslationInterface::class);

    $this->controller = new CohesionEndpointController(
      $this->entityFieldManager,
      $this->entityTypeBundleInfo,
      $this->entityRepository,
      $this->cohesionUtils,
      $this->requestStack,
      $this->customComponentsService,
      $this->user,
      $this->fileRepository,
      $this->fileUrlGenerator,
      $this->extensionPathResolver,
      $this->entityTypeManager,
      $this->stringTranslation,
    );

    $this->extensionPathResolver->method('getPath')
      ->with('module', 'cohesion')
      ->willReturn('modules/contrib/cohesion');
  }

  /**
   * @covers \Drupal\cohesion\Controller\CohesionEndpointController::getGroupJson
   */
  public function testGetGroupJson(): void {
    // Check for non-existing directory.
    $response = $this->controller->getGroupJson('not_existing_directory');
    $this->assertInstanceOf(CohesionJsonResponse::class, $response);
    $response_content = json_decode($response->getContent(), TRUE);
    $this->assertEquals(404, $response->getStatusCode());
    $this->assertStringContainsString('JSON directory not found', $response_content['error']);

    // Check for existing directory.
    $response = $this->controller->getGroupJson('element_categories');
    $module_path = $this->extensionPathResolver->getPath('module', 'cohesion');
    $json_directory = DRUPAL_ROOT . '/' . $module_path . '/js/react-app/assets/json/element_categories';

    $this->assertDirectoryExists(
      $json_directory,
      sprintf(
        'Resolved JSON directory missing. DRUPAL_ROOT="%s", modulePath="%s", cwd="%s"',
        defined('DRUPAL_ROOT') ? DRUPAL_ROOT : '<undefined>',
        $module_path,
        getcwd() ?: '<unknown>'
      )
    );
    $this->assertFileExists(
      $json_directory . '/content-elements.json',
      sprintf('Expected JSON fixture missing in "%s"', $json_directory)
    );
    $this->assertInstanceOf(CohesionJsonResponse::class, $response);
    $this->assertEquals(200, $response->getStatusCode());
    $data = json_decode($response->getContent(), TRUE);
    $this->assertIsArray($data);
    $this->assertCount(8, $data);
  }


}
