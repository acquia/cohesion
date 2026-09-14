<?php

namespace Drupal\cohesion\Hook;

use Drupal\cohesion\CohesionEntityViewBuilder;
use Drupal\cohesion\ImageBrowserUpdateManager;
use Drupal\cohesion\UsageUpdateManager;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Entity Hook implementations for Cohesion.
 */
class CohesionEntityHooks {

  public function __construct(
    protected ImageBrowserUpdateManager $image_browser_update_manager,
    protected UsageUpdateManager $usage_update_manager,
  ) {}

  /**
   * Implements hook_entity_insert().
   * @throws \Exception
   */
  #[Hook('entity_insert')]
  public function entity_insert(EntityInterface $entity): void {
    // Run the active image browser plugin function for config and content.
    $this->image_browser_update_manager->onEntityInsertUpdate($entity);

    // Set dependencies for this content entity.
    if (method_exists($entity, 'getHost') && $entity->getHost()) {
      $entity = $entity->getHost();
    }

    if ($entity->id()) {
      $this->usage_update_manager->buildRequires($entity);
    }
  }

  /**
   * Implements hook_entity_update().
   */
  #[Hook('entity_update')]
  public function entity_update(EntityInterface $entity): void {
    // Run the active image browser plugin function for config and content.
    $this->image_browser_update_manager->onEntityInsertUpdate($entity);

    // Update dependencies for this content entity.
    if (method_exists($entity, 'getHost') && $entity->getHost()) {
      $entity = $entity->getHost();
    }

    $this->usage_update_manager->buildRequires($entity);
  }

  /**
   * Implements hook_entity_delete().
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  #[Hook('entity_delete')]
  public function entity_delete(EntityInterface $entity): void {
    if (method_exists($entity, 'getHost') && $entity->getHost()) {
      $entity = $entity->getHost();
    }

    $this->usage_update_manager->removeUsage($entity);
  }

  /**
   * Implements hook_entity_operation_alter().
   *
   * Remove the "clone" option from Site Studio entity lists.
   *
   */
  #[Hook('entity_operation_alter')]
  public function entity_operation_alter(array &$operations, EntityInterface $entity): void {
    if (isset($operations['clone']) && str_contains($entity->getEntityTypeId(), 'cohesion_')) {
      unset($operations['clone']);
    }
  }

  /**
   * Implements hook_entity_view_alter().
   */
  #[Hook('entity_view_alter')]
  public function entity_view_alter(array &$build): void {
    if (isset($build['#view_mode']) && $build['#view_mode'] === 'search_result') {
      $build['#post_render'][] = [CohesionEntityViewBuilder::class, 'postRender'];
    }
  }

}
