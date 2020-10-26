<?php

namespace Drupal\cohesion;

use Twig\Loader\LoaderInterface;

/**
 * Defines an interface to store Cohesion templates and load them into Twig.
 */
interface TemplateStorageInterface extends LoaderInterface {

  /**
   * Saves a template.
   *
   * @param string $name
   *   The name of the template (e.g., a file name).
   * @param string $content
   *   The Twig source code of the template.
   * @param int $time
   *   (optional) The template's modification time. Defaults to the current
   *   time.
   */
  public function save(string $name, string $content, int $time = NULL) : void;

  /**
   * Lists all available templates.
   *
   * @return string[]
   *   A list of all available template names.
   */
  public function listAll() : array;

}
