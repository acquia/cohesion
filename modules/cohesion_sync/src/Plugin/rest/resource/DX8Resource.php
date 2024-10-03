<?php

namespace Drupal\cohesion_sync\Plugin\rest\resource;

use Drupal\cohesion_sync\PackagerManager;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Deprecated.
 *
 * REST endpoint to GET packages from this site and POST, PATCH packages to
 * this site.
 *
 * @RestResource(
 *   id = "dx8_resource",
 *   label = @Translation("Site Studio package resource"),
 *   uri_paths = {
 *     "canonical" = "/sync/package/{entity_type}"
 *   }
 * )
 */
class DX8Resource extends ResourceBase {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\PageCache\ResponsePolicy\KillSwitch
   */
  protected $cacheKillSwitch;

  /**
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorage
   */
  protected $storage;

  /**
   * @var \Drupal\Core\Entity\EntityRepository
   */
  protected $entityRepository;

  /**
   * @var \Drupal\cohesion_sync\PackagerManager
   */
  protected $packagerManager;

  /**
   * DX8Resource constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param array $serializer_formats
   * @param \Psr\Log\LoggerInterface $logger
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\Core\PageCache\ResponsePolicy\KillSwitch $cache_kill_switch
   * @param \Drupal\Core\Entity\EntityRepository $entityRepository
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, EntityTypeManagerInterface $entity_type_manager, KillSwitch $cache_kill_switch, EntityRepository $entityRepository, PackagerManager $packager_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->serializerFormats = $serializer_formats;
    $this->logger = $logger;
    $this->entityTypeManager = $entity_type_manager;
    $this->cacheKillSwitch = $cache_kill_switch;
    $this->entityRepository = $entityRepository;
    $this->packagerManager = $packager_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest'),
      $container->get('entity_type.manager'),
      $container->get('page_cache_kill_switch'),
      $container->get('entity.repository'),
      $container->get('cohesion_sync.packager')
    );
  }

  /**
   * Get and decode the route and query parameters.
   *
   * @return array|\Drupal\rest\ResourceResponse
   */
  private function processRouteParams() {
    $params = \Drupal::request()->attributes->all();

    // Unknown entity type exception.
    try {
      /** @var \Drupal\Core\Config\Entity\ConfigEntityStorage storage */
      $this->storage = $this->entityTypeManager->getStorage($params['entity_type']);
    }
    catch (\Exception $e) {
      return new ResourceResponse(['error' => 'Entity type not found'], 404);
      // Throw new AccessDeniedHttpException();
    }

    // Add in the query string stuff.
    $query = \Drupal::request()->query->all();

    if (isset($query['uuid'])) {
      $params['uuid'] = explode(',', $query['uuid']);
    }

    return $params;
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return string
   * @throws \ReflectionException
   */
  private function getEntityCategory($entity) {
  }

  /**
   * Responds to entity GET requests.
   *
   * @return array|\Drupal\rest\ResourceResponse
   *
   * @throws \ReflectionException
   */
  public function get() {
    // Disable caching for anonymous users (internal page cache).
    $this->cacheKillSwitch->trigger();

    // Get the route and query parameters.
    $params = $this->processRouteParams();
    if ($params instanceof ResourceResponse) {
      return $params;
    }

    $response_data = [];

    // Return the list of entities.
    if (!isset($params['uuid'])) {
      foreach ($this->storage->loadMultiple() as $entity) {

        if ($entity->status()) {
          if ($label = $entity->label()) {
            $response_data[$entity->uuid()] = [
              'label' => $label,
              'id' => $entity->id(),
              'uuid' => $entity->uuid(),
              'category' => $this->getEntityCategory($entity),
            ];
          }
        }
      }

    }
    // Return the package of entities from uuids.
    else {
      // Build the list fo entity objects from the UUIDs.
      $entities = [];
      foreach ($params['uuid'] as $uuid) {
        try {
          if ($entity = $this->entityRepository->loadEntityByUuid($params['entity_type'], $uuid)) {
            $entities[] = $entity;
          }
        }
        catch (\Exception $e) {
          return new ResourceResponse(['error' => 'Entity storage exception.'], 500);
        }
      }

      // Stream the JSON to the array for output.
      try {
        foreach ($this->packagerManager->buildPackageStream($entities, FALSE) as $item) {
          $response_data[] = $item;
        }
      }
      catch (\Exception $e) {
        return new ResourceResponse(['error' => 'Unable to build export package.'], 500);
      }

    }

    // Return page response.
    $response = new ResourceResponse($response_data);
    $disable_cache = new CacheableMetadata();
    $disable_cache->setCacheMaxAge(0);
    $response->addCacheableDependency($disable_cache);
    return $response;
  }

  /**
   * Responds to entity GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   */
  public function post() {
    $response = ['message' => 'Hello, this is a rest service'];
    return new ResourceResponse($response);
  }

}
