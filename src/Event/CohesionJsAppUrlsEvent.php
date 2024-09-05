<?php

namespace Drupal\cohesion\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event that collects all urls to endpoints needed for the Site Studio app
 */
class CohesionJsAppUrlsEvent extends Event {

  const FRONTEND_URL = 'cohesion_frontend_urls';
  const ADMIN_URL = 'cohesion_admin_urls';

  /**
   * The registered frontend urls
   *
   * @var array
   *
   */
  protected $url_collection = [];

  /**
   * @var \Drupal\Core\Form\FormStateInterface|null
   */
  protected $form_state = NULL;

  public function __construct($form_state = NULL) {
    $this->form_state = $form_state;
  }

  /**
   * Adds urls to the frontend collection
   *
   * @param string $name the name of the url
   * @param array $url
   *
   */
  public function addUrl(string $name, array $url) {
    $this->url_collection[$name] = $url;
  }

  /**
   * Get the registered urls
   *
   * @return array
   */
  public function getUrls() {
    return $this->url_collection;
  }

  /**
   * Return the form state if any
   *
   * @return \Drupal\Core\Form\FormStateInterface|NULL
   */
  public function getFormState() {
    return $this->form_state;
  }

}
