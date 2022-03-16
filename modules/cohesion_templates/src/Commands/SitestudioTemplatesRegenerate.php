<?php

namespace Drupal\cohesion_templates\Commands;

use Drush\Commands\DrushCommands;

/**
 * Provides templates regeneration handling command.
 */
class SitestudioTemplatesRegenerate extends DrushCommands {

  /**
   * Regenerate Site studio templates.
   *
   * @command sitestudio:templates:regenerate
   */
  public function regenerateTemplates() {
    $this->yell(_cohesion_templates_generate_content_template_entities());
  }

}
