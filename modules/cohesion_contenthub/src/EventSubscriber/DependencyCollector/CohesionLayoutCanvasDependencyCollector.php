<?php

namespace Drupal\cohesion_contenthub\EventSubscriber\DependencyCollector;

use Drupal\cohesion_elements\Entity\CohesionLayout;
use Drupal\Component\Uuid\Uuid;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\depcalc\DependencyCalculatorEvents;
use Drupal\depcalc\DependentEntityWrapper;
use Drupal\depcalc\Event\CalculateEntityDependenciesEvent;
use Drupal\depcalc\EventSubscriber\DependencyCollector\BaseDependencyCollector;

/**
 * Subscribes to dependency collection to extract referenced entities.
 */
class CohesionLayoutCanvasDependencyCollector extends BaseDependencyCollector {

  /**
   * Array of entities referenced in Layout Canvas, keyed by type:uuid.
   *
   * @var array
   */
  protected $entityReferences;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * CohesionJsonValueFieldDependencyCollector constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type Manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[DependencyCalculatorEvents::CALCULATE_DEPENDENCIES][] = ['onCalculateDependencies'];

    return $events;
  }

  /**
   * Calculates the referenced entities.
   *
   * @param \Drupal\depcalc\Event\CalculateEntityDependenciesEvent $event
   *   The dependency calculation event.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function onCalculateDependencies(CalculateEntityDependenciesEvent $event) {
    $entity = $event->getEntity();

    if (!($entity instanceof CohesionLayout)) {
      return;
    }

    $layoutCanvas = $entity->getLayoutCanvasInstance();
    $entityReferences = $layoutCanvas->getEntityReferences(TRUE, TRUE);
    if (!empty($entityReferences)) {
      foreach ($entityReferences as $entityReference) {
        if (isset($this->entityReferences[implode(":", $entityReference)])) {
          continue;
        }
        if ($loadedEntity = $this->loadEntity($entityReference)) {
          $this->entityReferences[implode(":", $entityReference)] = $loadedEntity;
        }
      }
    }
    $this->addEntityDependencies($event);
  }

  /**
   * Adds entity references used in Layout Canvas to the dependency list.
   *
   * @param \Drupal\depcalc\Event\CalculateEntityDependenciesEvent $event
   *   The dependency calculation event.
   *
   * @throws \Exception
   */
  protected function addEntityDependencies(CalculateEntityDependenciesEvent $event) {
    if (!empty($this->entityReferences)) {
      foreach ($this->entityReferences as $entity) {
        $item_entity_wrapper = new DependentEntityWrapper($entity);
        $local_dependencies = [];
        $this->mergeDependencies($item_entity_wrapper, $event->getStack(), $this->getCalculator()->calculateDependencies($item_entity_wrapper, $event->getStack(), $local_dependencies));
        $event->addDependency($item_entity_wrapper);
      }
    }
  }

  /**
   * Loads entity by type and uuid.
   *
   * @param array $entityReference
   *   Entity type and uuid.
   *
   * @return \Drupal\Core\Entity\EntityInterface|false
   *   Loaded entity or FALSE.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function loadEntity(array $entityReference) {
    if (Uuid::isValid($entityReference['entity_id'])) {
      $results = $this->entityTypeManager->getStorage($entityReference['entity_type'])->loadByProperties(['uuid' => $entityReference['entity_id']]);
      $entity = reset($results);
    }
    else {
      $entity = $this->entityTypeManager->getStorage($entityReference['entity_type'])->load($entityReference['entity_id']);
    }

    return $entity;
  }

}
