<?php

namespace Drupal\cohesion_templates\Drush\Commands;

use Consolidation\AnnotatedCommand\CommandResult;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides templates regeneration handling command.
 *
 * @package Drupal\cohesion_templates\Drush\Commands
 */
class CohesionTemplatesCommands extends DrushCommands {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self();
  }

  /**
   * Regenerate Site studio templates.
   *
   * @validate-module-enabled cohesion_templates
   *
   * @command sitestudio:templates:regenerate
   * @alias test:templates:regenerate
   */
  public function regenerateTemplates() {
    $this->yell(_cohesion_templates_generate_content_template_entities());

    return CommandResult::exitCode(self::EXIT_SUCCESS);
  }

}
