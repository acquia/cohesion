<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ElementLevel;

use Drupal\cohesion\Services\CohesionUtils;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\jsonapi\ResourceType\ResourceTypeRepositoryInterface;

/**
 * Handles Site Studio element of "link" type.
 */
class LinkHandler extends EntityReferenceHandlerBase implements ElementLevelHandlerInterface {
  const ID = 'link';
  const MAP = '/maps/element/link.map.yml';
  const SCHEMA = '/maps/element/link.schema.json';

  /**
   * @var \Drupal\cohesion\Services\CohesionUtils
   */
  protected $cohesionUtils;

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
   * @param \Drupal\cohesion\Services\CohesionUtils $cohesionUtils
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $urlGenerator
   * @param \Drupal\jsonapi\ResourceType\ResourceTypeRepositoryInterface $entityTypeManager
   * @param \Drupal\jsonapi\ResourceType\ResourceTypeRepositoryInterface
   */
  public function __construct(
    ModuleHandlerInterface $moduleHandler,
    CohesionUtils $cohesionUtils,
    UrlGeneratorInterface $urlGenerator,
    EntityTypeManagerInterface $entityTypeManager,
    ResourceTypeRepositoryInterface $resourceTypeRepository,
  ) {
    parent::__construct($moduleHandler, $urlGenerator, $entityTypeManager, $resourceTypeRepository);
    $this->cohesionUtils = $cohesionUtils;
    $this->urlGenerator = $urlGenerator;
  }

  /**
   * {@inheritdoc}
   */
  protected function processProperty(array $item): mixed {
    $property = parent::processProperty($item);
    $link = [];

    if (isset($item['_process_token'])) {
      foreach (CohesionUtils::SCHEMES as $scheme) {
        if (str_starts_with($property, $scheme)) {
          return (object) ['url' => $property];
        }
      }
      $link['url'] = is_string($property) ? $this->cohesionUtils->urlProcessor($property, TRUE) : "";

      if (!empty($link['url'])) {
        $entityData = explode('::', (string) $property);
        if (is_string($entityData[0]) && is_numeric($entityData[1])) {
          $entity = $this->entityTypeManager->getStorage($entityData[0])->load($entityData[1]);

          if ($entity instanceof EntityInterface) {
            $reference['uuid'] = $entity->uuid();
            $reference['type'] = $entityData[0];
            $link['reference'] = (object) $reference;
          }
        }
      }
      return (object) $link;
    }

    return $property;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityUuid(array $data): string {
    return $data['data']['value']->reference->uuid;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityType(array $data): string {
    return $data['data']['value']->reference->type;
  }

  /**
   * {@inheritdoc}
   */
  protected function hasEntityReference(array $data): bool {
    if (isset($data['data']['value']->reference)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  protected function addJsonApiLink(array &$data): void {
    parent::addJsonApiLink($data);
    if (is_object($data['data']['value']) && isset($data['data']['value']->reference)) {
      unset ($data['data']['value']->reference);
    }
  }

}
