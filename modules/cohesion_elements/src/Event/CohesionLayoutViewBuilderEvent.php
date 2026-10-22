<?php

namespace Drupal\cohesion_elements\Event;

use Drupal\cohesion_elements\Entity\CohesionLayout;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event to alter the view build array of
 * \Drupal\cohesion_elements\CohesionLayoutViewBuilder
 */
class CohesionLayoutViewBuilderEvent extends Event {

  const ALTER = 'sitestudio_cohesion_canvas_view_builder_alter';

  /**
   * The build array
   *
   * @var array
   */
  protected $build;

  /**
   * The entity to render.
   *
   * @var Drupal\cohesion_elements\Entity\CohesionLayout
   */

  protected $entity;

  /**
   * Constructs the object.
   *
   * @param $build
   *  The build array
   * @param \Drupal\cohesion_elements\Entity\CohesionLayout $entity
   *  The CohesionLayout entity
   *
   */
  public function __construct($build, CohesionLayout $entity) {
    $this->build = $build;
    $this->entity = $entity;
  }

  /**
   * Get the build array
   *
   * @return array
   */
  public function getBuild() {
    return $this->build;
  }

  /**
   * Get the entity to render
   *
   * @return \Drupal\cohesion_elements\Entity\CohesionLayout
   */
  public function getEntity() {
    return $this->entity;
  }

  /**
   * Set the build array
   *
   * @param array $build
   */
  public function setBuild(array $build) {
    $this->build = $build;
  }

}
