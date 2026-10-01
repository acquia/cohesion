<?php

namespace Drupal\cohesion\TemplateStorage;

use Twig\Loader\LoaderInterface;

/**
 * Defines an interface for services that store Cohesion templates.
 */
interface TemplateStorageInterface extends LoaderInterface {

  /**
   * Returns a list of all templates stored by this backend.
   *
   * Note that this method should only return "permanent" templates -- that is,
   * templates which should be used to actually render content. Templates in
   * volatile or temporary storage should NOT be included.
   *
   * @return string[]
   *   An array of template names.
   */
  public function listAll() : array;

  /**
   * Saves a template.
   *
   * Implementing classes may choose to save the template into a temporary or
   * volatile storage, because this method may be called during batch imports,
   * rebuilds, or other long-running jobs that may fail midway through. External
   * code should call the commit() method to actually move all saved templates
   * into permanent storage so they can be used to render content.
   *
   * @param string $name
   *   The name of the template to save.
   * @param string $content
   *   The Twig source code of the template.
   * @param int $time
   *   (optional) The time stamp at which the template was modified. Defaults to
   *   the current time.
   */
  public function save(string $name, string $content, ?int $time = NULL);

  /**
   * Makes all saved templates permanent.
   *
   * "Permanent" templates are exposed to Drupal's rendering system and used to
   * actually render content, so they need to be available consistently. Calling
   * code should NOT call this method during a batch import, rebuild, or other
   * long-running job that can fail midway through.
   */
  public function commit();

  /**
   * Delete a "permanent" template if is exists
   *
   * @param string $name
   */
  public function delete(string $name);

}
