<?php

namespace Drupal\sitestudio_page_builder\Services;

use Drupal\Core\Entity\ContentEntityInterface;

interface SitestudioPageBuilderManagerInterface {

  /**
   * Whether the current page should have the page builder
   *
   * return the current route entity if TRUE
   *
   * @return bool|ContentEntityInterface
   */
  public function shouldEnablePageBuilder();

}