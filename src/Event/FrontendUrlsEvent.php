<?php

namespace Drupal\cohesion\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event that collects all urls to endpoints needed for the Site Studio app
 */
class FrontendUrlsEvent extends Event {

  const EVENT_NAME = 'sitestudio_frontend_urls';

  const ALL_TYPE = 0;
  const ADMIN_TYPE = 1;
  const FRONTEND_TYPE = 2;

  /**
   * The registered frontend urls
   *
   * @var array
   *
   */
  protected $frontend_collection = [];

  /**
   * The registered admin urls
   *
   * @var array
   */
  protected $admin_collection = [];

  /**
   * Adds urls to the frontend collection
   *
   * @param string $name the name of the url
   * @param array $url
   *
   */
  public function addFrontEndUrl(string $name, array $url) {
    $this->frontend_collection[$name] = $url;
  }

  /**
   * Adds urls to the admin collection
   *
   * @param string $name the name of the url
   * @param array $url
   *
   */
  public function addAdminUrls(string $name, array $url) {
    $this->admin_collection[$name] = $url;
  }

  /**
   * Get the registered urls by type (admin,frontend,all)
   *
   * @param int $type the type of urls needed, default ALL_TYPE
   * @return array
   */
  public function getUrls(int $type = self::ALL_TYPE) {
    switch($type) {
      case self::ALL_TYPE:
        return array_merge($this->admin_collection, $this->frontend_collection);

      case self::FRONTEND_TYPE:
        return $this->frontend_collection;

      case self::ADMIN_TYPE:
        return $this->admin_collection;
    }
  }

}
