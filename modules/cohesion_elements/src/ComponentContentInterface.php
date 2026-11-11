<?php

namespace Drupal\cohesion_elements;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Interface ComponentContentInterface.
 *
 * Provides an interface defining a component content entity.
 *
 * @package Drupal\cohesion_elements
 */
interface ComponentContentInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface, RevisionLogInterface, EntityPublishedInterface {

  /**
   * Denotes that the component content is not published.
   */
  const NOT_PUBLISHED = 0;

  /**
   * Denotes that the component content is published.
   */
  const PUBLISHED = 1;

  /**
   * Gets the component content title.
   *
   * @return string
   *   Title of the component content.
   */
  public function getTitle();

  /**
   * Sets the component content title.
   *
   * @param string $title
   *   The component content title.
   *
   * @return \Drupal\cohesion_elements\ComponentContentInterface
   *   The called component content entity.
   */
  public function setTitle($title);

  /**
   * Gets the component content creation timestamp.
   *
   * @return int
   *   Creation timestamp of the component content.
   */
  public function getCreatedTime();

  /**
   * Sets the component content creation timestamp.
   *
   * @param int $timestamp
   *   The nocomponent contentde creation timestamp.
   *
   * @return \Drupal\cohesion_elements\ComponentContentInterface
   *   The called component content entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the component content revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the component content revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\cohesion_elements\ComponentContentInterface
   *   The called component content entity.
   */
  public function setRevisionCreationTime($timestamp);

}
