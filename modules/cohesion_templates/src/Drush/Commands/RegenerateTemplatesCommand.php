<?php

namespace Drupal\cohesion_templates\Drush\Commands;

use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Drush command to regenerate Site Studio templates.
 */
final class RegenerateTemplatesCommand extends DrushCommands {

  const NAME = 'sitestudio:templates:regenerate';

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self();
  }

  /**
   * Regenerate Site Studio templates.
   *
   * @command sitestudio:templates:regenerate
   * @aliases test:templates:regenerate
   * @validate-module-enabled cohesion_templates
   * @usage sitestudio:templates:regenerate Regenerate Site Studio templates.
   */
  #[CLI\Command(name: self::NAME, aliases: ['test:templates:regenerate'])]
  #[CLI\ValidateModulesEnabled(modules: ['cohesion_templates'])]
  #[CLI\Usage(name: self::NAME, description: 'Regenerate Site Studio templates.')]
  public function regenerate(): int {
    $message = _cohesion_templates_generate_content_template_entities();

    if ($message) {
      $this->io()->success((string) $message);
    }

    return self::EXIT_SUCCESS;
  }

}
