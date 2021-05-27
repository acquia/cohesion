<?php

namespace Drupal\cohesion\TemplateStorage;

/**
 * Defines a base class for template storage services.
 */
abstract class TemplateStorageBase implements TemplateStorageInterface {

  /**
   * Implements LoaderInterface::getSource() for Twig 1.x compatibility.
   */
  public function getSource($name) {
    return $this->getSourceContext($name)->getCode();
  }

}
