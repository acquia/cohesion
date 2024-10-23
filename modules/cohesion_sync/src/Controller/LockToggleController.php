<?php

namespace Drupal\cohesion_sync\Controller;

use Drupal\cohesion_sync\PackagerManager;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class OperationExportController.
 *
 * Export a package from a single entity from the entities operations (see
 * cohesion_sync_entity_operation_alter() in cohesion_sync.module).
 *
 * @package Drupal\cohesion_sync\Controller
 */
class LockToggleController extends ControllerBase {

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
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function index(Request $request) {
    // Get entity information from the request path.
    $entity_type_id = $request->attributes->get('entity_type');
    $entity_uuid = $request->attributes->get('entity_uuid');

    // Attempt to load the entity passed in the url.
    if ($entity = $this->entityRepository->loadEntityByUuid($entity_type_id, $entity_uuid)) {
      // Toggle the lock status.
      $entity->setLocked(!$entity->isLocked());
      $entity->save();

      // Show success.
      $this->messenger()->addMessage($this->t('%template_name has been %status', [
        '%template_name' => $entity->label(),
        '%status' => $entity->isLocked() ? 'locked' : 'unlocked',
      ]));

      // Redirect back to the entity collection page.
      return new RedirectResponse($entity->toUrl('collection')->toString());
    }
    else {
      $this->messenger()->addError($this->t('Entity could not be loaded.'));
      return $this->redirect('cohesion.settings');
    }
  }

}
