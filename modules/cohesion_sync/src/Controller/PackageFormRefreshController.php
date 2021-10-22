<?php

namespace Drupal\cohesion_sync\Controller;

use Drupal\cohesion\CohesionJsonResponse;
use Drupal\cohesion\UsagePluginManager;
use Drupal\cohesion_sync\CohesionSyncRefreshManager;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\cohesion_sync\PackagerManager;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Class PackageFormRefreshController.
 *
 * @package Drupal\cohesion_sync\Controller
 */
class PackageFormRefreshController extends ControllerBase {

  /**
   * @var \Drupal\cohesion_sync\PackagerManager
   */
  protected $packagerManager;

  /**
   * @var \Drupal\Core\Entity\EntityRepository
   */
  protected $entityRepository;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\cohesion\UsagePluginManager
   */
  protected $usagePluginManager;

  /**
   * @var \Drupal\cohesion\CohesionSyncRefreshManager
   */
  protected $cohesionRefreshSyncManager;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(PackagerManager $packagerManager,
                              EntityRepository $entityRepository,
                              EntityTypeManagerInterface $entityTypeManager,
                              UsagePluginManager $usagePluginManager,
                              CohesionSyncRefreshManager $cohesionRefreshSyncManager,
                              LoggerChannelFactoryInterface $logger_factory) {
    $this->packagerManager = $packagerManager;
    $this->entityRepository = $entityRepository;
    $this->entityTypeManager = $entityTypeManager;
    $this->usagePluginManager = $usagePluginManager;
    $this->cohesionRefreshSyncManager = $cohesionRefreshSyncManager;
    $this->loggerFactory = $logger_factory->get('cohesion_sync');
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('cohesion_sync.packager'),
      $container->get('entity.repository'),
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.usage.processor'),
      $container->get('cohesion_sync.refresh_manager'),
      $container->get('logger.factory'),
    );
  }

  /**
   * Entrypoint. The form app asked to re-calculate the requirements and contents form.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function index(Request $request) {
    // Store the Request Content in Drupal State to fetch across requests.
    $this->cohesionRefreshSyncManager->setRequestSettings($request);

    /**
     * Build the package requirements form.
     */
    $package_requirements_form = $this->initBackendProcess(CohesionSyncRefreshManager::PACKAGE_REQUIREMENTS);

    /**
     * Build the package contents form.
     */
    $package_contents_form = $this->initBackendProcess(CohesionSyncRefreshManager::PACKAGE_CONTENTS);

    /**
     * Build the excluded entity types list.
     */
    $excluded_entity_types_form = $this->initBackendProcess(CohesionSyncRefreshManager::PACKAGE_EXCLUDE_ENTITY_TYPES);

    /**
     * Return the forms to the app.
     */
    return new CohesionJsonResponse([
      'status' => 200,
      'packageRequirementsForm' => $package_requirements_form,
      'packageContentsForm' => $package_contents_form,
      'excludedEntityTypesForm' => $excluded_entity_types_form,
    ]);
  }

  /**
   * Init Backend Process to build the Package.
   */
  public function initBackendProcess(string $build_package_type) {
    $build_package_process_output = [];
    $process = new Process("drush @self sync-refresh $build_package_type");
    $process->setTimeout(1200);
    $process->setIdleTimeout(600);
    try {
      $process->mustRun();
      if ($process->isSuccessful()) {
        $build_package_process_output = json_decode($process->getOutput(), TRUE);
        $this->loggerFactory->info('Completed the Build Package of Type @build_package_type', ['@build_package_type' => $build_package_type]);
      }
    }
    catch(ProcessFailedException $process_Exception) {
      $this->loggerFactory
        ->error('Failed to Complete the Package Build Process with @type and @error', ['@type' => $build_package_type, '@error' => $process_Exception->getMessage()]);
    }

    return $build_package_process_output;
  }

}
