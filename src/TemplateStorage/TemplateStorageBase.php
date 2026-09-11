<?php

namespace Drupal\cohesion\TemplateStorage;

/**
 * Defines a base class for template storage services.
 *
 * @phpstan-ignore-next-line
 */
abstract class TemplateStorageBase implements TemplateStorageInterface {

  const TEMPLATE_PREFIX = '--cohesion-';

  /**
   * Implements LoaderInterface::getSource() for Twig 1.x compatibility.
   */
  public function getSource($name) {
    return $this->getSourceContext($name)->getCode();
  }

}
