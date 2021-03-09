<?php

namespace Drupal\cohesion;

use Drupal\cohesion\Controller\CohesionEntityController;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;
use Symfony\Component\Routing\Route;

/**
 * Provides routes for Site Studio base styles entities.
 *
 * @see Drupal\Core\Entity\Routing\AdminHtmlRouteProvider
 * @see Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider
 */
class CohesionHtmlRouteProvider extends AdminHtmlRouteProvider {

  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type) {

    $collection = parent::getRoutes($entity_type);

    $entity_type_id = $entity_type->id();

    if ($extend_form_route = $this->getExtendFormRoute($entity_type)) {
      $collection->add("entity.{$entity_type_id}.extend_form", $extend_form_route);
    }

    if ($duplicate_form_route = $this->getDuplicateFormRoute($entity_type)) {
      $collection->add("entity.{$entity_type_id}.duplicate_form", $duplicate_form_route);
    }

    if ($disable_form_route = $this->getDisableRoute($entity_type)) {
      $collection->add("entity.{$entity_type_id}.disable", $disable_form_route);
    }

    if ($enable_form_route = $this->getEnableRoute($entity_type)) {
      $collection->add("entity.{$entity_type_id}.enable", $enable_form_route);
    }

    if ($default_form_route = $this->getSetDefaultFormRoute($entity_type)) {
      $collection->add("entity.{$entity_type_id}.set_default_form", $default_form_route);
    }

    if ($in_use_route = $this->getInUseRoute($entity_type)) {
      $collection->add("entity.{$entity_type_id}.in_use", $in_use_route);
    }

    if ($enable_selection_form_route = $this->getEnableSelectionRoute($entity_type)) {
      $collection->add("entity.{$entity_type_id}.enable_selection", $enable_selection_form_route);
    }

    if ($disable_selection_form_route = $this->getDisableSelectionRoute($entity_type)) {
      $collection->add("entity.{$entity_type_id}.disable_selection", $disable_selection_form_route);
    }

    return $collection;
  }

  /**
   * Gets the extend form route.
   * Built only for entity types that have bundles.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getExtendFormRoute(EntityTypeInterface $entity_type) {
    if ($entity_type->hasLinkTemplate('extend-form')) {
      $entity_type_id = $entity_type->id();
      $route = new Route($entity_type->getLinkTemplate('extend-form'));
      $route->setDefault('_entity_form', "{$entity_type_id}.extend");
      $route->setOption('_admin_route', TRUE);
      $route->setRequirement('_entity_access', "{$entity_type_id}.extend");
      // Entity types with serial IDs can specify this in their route
      // requirements, improving the matching process.
      if ($this->getEntityTypeIdKeyType($entity_type) === 'integer') {
        $route->setRequirement($entity_type_id, '\d+');
      }

      return $route;
    }
  }

  /**
   * Gets the duplicate form route.
   * Built only for entity types that have bundles.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getDuplicateFormRoute(EntityTypeInterface $entity_type) {
    if ($entity_type->hasLinkTemplate('duplicate-form')) {
      $entity_type_id = $entity_type->id();
      $route = new Route($entity_type->getLinkTemplate('duplicate-form'));
      $route->setDefault('_entity_form', "{$entity_type_id}.duplicate");
      $route->setOption('_admin_route', TRUE);
      $route->setRequirement('_entity_access', "{$entity_type_id}.duplicate");
      // Entity types with serial IDs can specify this in their route
      // requirements, improving the matching process.
      if ($this->getEntityTypeIdKeyType($entity_type) === 'integer') {
        $route->setRequirement($entity_type_id, '\d+');
      }

      return $route;
    }
  }

  /**
   * Gets the disable route.
   * Built only for entity types that have bundles.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getDisableRoute(EntityTypeInterface $entity_type) {
    if ($entity_type->hasLinkTemplate('disable')) {
      $entity_type_id = $entity_type->id();
      $route = new Route($entity_type->getLinkTemplate('disable'));
      $route->setDefault('_entity_form', "{$entity_type_id}.disable");
      $route->setOption('_admin_route', TRUE);
      $route->setRequirement('_entity_access', "{$entity_type_id}.disable");
      // Entity types with serial IDs can specify this in their route
      // requirements, improving the matching process.
      if ($this->getEntityTypeIdKeyType($entity_type) === 'integer') {
        $route->setRequirement($entity_type_id, '\d+');
      }

      return $route;
    }
  }

  /**
   * Gets the enable route.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getEnableRoute(EntityTypeInterface $entity_type) {
    if ($entity_type->hasLinkTemplate('enable')) {
      $entity_type_id = $entity_type->id();
      $route = new Route($entity_type->getLinkTemplate('enable'));
      $route->setDefault('_entity_form', "{$entity_type_id}.enable");
      $route->setOption('_admin_route', TRUE);
      $route->setRequirement('_entity_access', "{$entity_type_id}.enable");
      // Entity types with serial IDs can specify this in their route
      // requirements, improving the matching process.
      if ($this->getEntityTypeIdKeyType($entity_type) === 'integer') {
        $route->setRequirement($entity_type_id, '\d+');
      }

      return $route;
    }
  }

  /**
   * Gets the set default form route.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getSetDefaultFormRoute(EntityTypeInterface $entity_type) {
    if ($entity_type->hasLinkTemplate('set-default-form')) {
      $entity_type_id = $entity_type->id();
      $route = new Route($entity_type->getLinkTemplate('set-default-form'));
      $route->setDefault('_entity_form', "{$entity_type_id}.set_default");
      $route->setOption('_admin_route', TRUE);
      $route->setRequirement('_entity_access', "{$entity_type_id}.set_default");
      // Entity types with serial IDs can specify this in their route
      // requirements, improving the matching process.
      if ($this->getEntityTypeIdKeyType($entity_type) === 'integer') {
        $route->setRequirement($entity_type_id, '\d+');
      }

      return $route;
    }
  }

  /**
   * Gets the in use route.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getInUseRoute(EntityTypeInterface $entity_type) {
    if ($entity_type->hasLinkTemplate('in-use')) {
      $entity_type_id = $entity_type->id();
      $route = new Route($entity_type->getLinkTemplate('in-use'));
      $route->setOption('_admin_route', TRUE);

      $route->setDefault('_controller', CohesionEntityController::class . '::inUse');
      $route->setDefault('_title_callback', CohesionEntityController::class . '::inUseTitle');
      $route->setDefault('entity_type_id', $entity_type_id);
      $route->setRequirement('_permission', $entity_type->getAdminPermission());
      $route->setOption('parameters', [
        $entity_type_id => ['type' => 'entity:' . $entity_type_id],
      ]);

      return $route;
    }
  }

  /**
   * Gets the enable selection route.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getEnableSelectionRoute(EntityTypeInterface $entity_type) {
    if ($entity_type->hasLinkTemplate('enable-selection')) {
      $entity_type_id = $entity_type->id();
      $route = new Route($entity_type->getLinkTemplate('enable-selection'));
      $route->setDefault('_entity_form', "{$entity_type_id}.enable-selection");
      $route->setOption('_admin_route', TRUE);
      $route->setRequirement('_entity_access', "{$entity_type_id}.enable-selection");
      // Entity types with serial IDs can specify this in their route
      // requirements, improving the matching process.
      if ($this->getEntityTypeIdKeyType($entity_type) === 'integer') {
        $route->setRequirement($entity_type_id, '\d+');
      }

      return $route;
    }
  }

  /**
   * Gets the disable selection route.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return \Symfony\Component\Routing\Route|null
   *   The generated route, if available.
   */
  protected function getDisableSelectionRoute(EntityTypeInterface $entity_type) {
    if ($entity_type->hasLinkTemplate('disable-selection')) {
      $entity_type_id = $entity_type->id();
      $route = new Route($entity_type->getLinkTemplate('disable-selection'));
      $route->setDefault('_entity_form', "{$entity_type_id}.disable-selection");
      $route->setOption('_admin_route', TRUE);
      $route->setRequirement('_entity_access', "{$entity_type_id}.disable-selection");
      // Entity types with serial IDs can specify this in their route
      // requirements, improving the matching process.
      if ($this->getEntityTypeIdKeyType($entity_type) === 'integer') {
        $route->setRequirement($entity_type_id, '\d+');
      }

      return $route;
    }
  }

}
