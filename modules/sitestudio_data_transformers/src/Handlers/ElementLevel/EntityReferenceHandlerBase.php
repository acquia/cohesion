<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ElementLevel;

use Drupal\cohesion\LayoutCanvas\Element;
use Drupal\cohesion\LayoutCanvas\ElementModel;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\jsonapi\ResourceType\ResourceTypeRepositoryInterface;

/**
 * Entity reference handling in Site Studio elements.
 */
abstract class EntityReferenceHandlerBase extends ElementHandlerBase {

  /**
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\jsonapi\ResourceType\ResourceTypeRepositoryInterface
   */
  protected $resourceTypeRepository;

  /**
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $urlGenerator
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
  public function __construct(
    ModuleHandlerInterface $moduleHandler,
    UrlGeneratorInterface $urlGenerator,
    EntityTypeManagerInterface $entityTypeManager,
    ResourceTypeRepositoryInterface $resourceTypeRepository,
  ) {
    parent::__construct($moduleHandler);
    $this->urlGenerator = $urlGenerator;
    $this->entityTypeManager = $entityTypeManager;
    $this->resourceTypeRepository = $resourceTypeRepository;
  }

  protected function addJsonApiLink(array &$data): void {
    if ($this->hasEntityReference($data)) {
      $entityType = $this->getEntityType($data);
      $entityUuid = $this->getEntityUuid($data);
      $results = $this->entityTypeManager->getStorage($entityType)->loadByProperties(['uuid' => $entityUuid]);
      $entity = reset($results);
      if ($entity instanceof EntityInterface) {
        $resourceType = $this->resourceTypeRepository->get($entityType, $entity->bundle());
        $routeName = sprintf('jsonapi.%s.individual', $resourceType->getTypeName());
        $data['data']['value']->jsonapi_link = $this->urlGenerator->generateFromRoute(
          $routeName,
          ['entity' => $entityUuid],
          ['absolute' => TRUE]
        );
      }
    }
  }

  /**
   * Gets data from form field.
   *
   * @param \Drupal\cohesion\LayoutCanvas\Element $element
   * @param \Drupal\cohesion\LayoutCanvas\ElementModel $elementModel
   * @return array
   */
  public function getData(Element $element, ElementModel $elementModel): array {
    $data = parent::getData($element, $elementModel);
    $this->addJsonApiLink($data);

    return $data;
  }

  /**
   * @param array $data
   * @return string
   */
  abstract protected function getEntityUuid(array $data): string;

  /**
   * @param array $data
   * @return string
   */
  abstract protected function getEntityType(array $data): string;

  /**
   * @param array $data
   * @return bool
   */
  abstract protected function hasEntityReference(array $data): bool;

}
