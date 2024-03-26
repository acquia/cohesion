<?php

namespace Drupal\cohesion\Event;

/**
 * Defines events for the Cohesion (Site Studio) module.
 */
final class SiteStudioEvents {

  /**
   * The name of the event fired before Site Studio rebuild.
   *
   * @Event
   *
   * @see \Drupal\cohesion\Event\PreRebuildEvent
   */
  const PRE_REBUILD = 'sitestudio.pre_rebuild';

  /**
   * The name of the event fired after Site Studio rebuild.
   *
   * @Event
   *
   * @see \Drupal\cohesion\Event\PostRebuildEvent
   */
  const POST_REBUILD = 'sitestudio.post_rebuild';

}
