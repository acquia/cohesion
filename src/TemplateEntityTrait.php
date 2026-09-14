<?php

namespace Drupal\cohesion;

/**
 * Trait for template entities.
 */
trait TemplateEntityTrait {

  /**
   * Construct the global template name or the theme
   * specific template name
   *
   * @param null|string $theme_name
   *
   */
  public function getTwigFilename($theme_name = NULL) {
    if ($twig_template = $this->get('twig_template')) {
      if (!is_null($theme_name)) {
        return $twig_template . '--' . str_replace('_', '-', $theme_name);
      }
      return $twig_template;
    }
    return FALSE;
  }

  /**
   * Remove all templates (global and theme specific) for the entity
   */
  public function removeAllTemplates() {
    $this->removeGlobalTemplate();
    $this->removeThemeSpecificTemplates();
  }

  /**
   * Remove any global (non theme specific) template form template storage for
   * the entity.
   */
  public function removeGlobalTemplate() {
    /** @var \Drupal\cohesion\TemplateStorage\TemplateStorageInterface $template_storage */
    $template_storage = \Drupal::service('cohesion.template_storage');
    // Clear the global template entry if it exists
    $theme_filename = $this->getTwigFilename() . '.html.twig';
    $template_storage->delete($theme_filename);
  }

  /**
   * Remove all theme specific template form template storage for the entity
   */
  public function removeThemeSpecificTemplates() {
    /** @var \Drupal\cohesion\TemplateStorage\TemplateStorageInterface $template_storage */
    $template_storage = \Drupal::service('cohesion.template_storage');
    foreach (\Drupal::service('cohesion.utils')->getCohesionEnabledThemes() as $theme_info) {
      $theme_filename = $this->getTwigFilename($theme_info->getName()) . '.html.twig';
      $template_storage->delete($theme_filename);
    }
  }

}
