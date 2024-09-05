<?php

namespace Drupal\cohesion_sync\Controller;

use Drupal\cohesion_sync\Entity\PackageSettingsInterface;
use Drupal\cohesion_sync\PackagerManager;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class OperationExportController.
 *
 * Export a package from a single entity from the entities operations (see
 * cohesion_sync_entity_operation_alter() in cohesion_sync.module).
 *
 * @package Drupal\cohesion_sync\Controller
 */
class OperationExportController extends ControllerBase {

  /**
   * @var \Drupal\cohesion_sync\PackagerManager
   */
  protected $packagerManager;

  /**
   * @var \Drupal\Core\Entity\EntityRepository
   */
  protected $entityRepository;

  /**
   * OperationExportController constructor.
   *
   * @param \Drupal\cohesion_sync\PackagerManager $packagerManager
   * @param \Drupal\Core\Entity\EntityRepository $entityRepository
   */
  public function __construct(PackagerManager $packagerManager, EntityRepository $entityRepository) {
    $this->packagerManager = $packagerManager;
    $this->entityRepository = $entityRepository;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new self($container->get('cohesion_sync.packager'), $container->get('entity.repository'));
  }

  /**
   * Export a package from a single entity defined in the route parameters.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function index(Request $request) {
    // Get entity information from the request path.
    $entity_type_id = $request->attributes->get('entity_type');
    $entity_uuid = $request->attributes->get('entity_uuid');

    // Attempt to load the entity passed in the url.
    if ($entity = $this->entityRepository->loadEntityByUuid($entity_type_id, $entity_uuid)) {
      // If this entity is a package load the excluded entity types list.
      if ($entity instanceof PackageSettingsInterface) {
        $excluded_entity_type_ids = $entity->getExcludedEntityTypes() ? array_keys($entity->getExcludedEntityTypes()) : [];
      }
      else {
        $excluded_entity_type_ids = [];
      }

      // Create the package and download it.
      return $this->packagerManager->sendYamlDownload($entity_type_id . '_(' . $entity->id() . ')_' . preg_replace('/[^a-z0-9]+/', '-', strtolower(substr($entity->label(), 0, 50))) . '.package.yml', [$entity], $excluded_entity_type_ids);
    }
    else {
      \Drupal::messenger()->addError($this->t('Entity could not be loaded.'));
      return $this->redirect('cohesion.settings');
    }
  }

}
