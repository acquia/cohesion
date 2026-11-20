<?php

namespace Drupal\cohesion_elements\Drush\Commands;

use Drupal\cohesion_elements\Controller\ElementsController;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Drush command to generate element usage.
 */
final class CohesionElementUsageCommand extends DrushCommands {

  const NAME = 'sitestudio:element-usage';

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self();
  }

  /**
   * Generate element usage.
   *
   * @command sitestudio:element-usage
   * @validate-module-enabled cohesion_elements
   * @usage sitestudio:element-usage Generate element usage.
   */
  #[CLI\Command(name: self::NAME)]
  #[CLI\ValidateModulesEnabled(modules: ['cohesion_elements'])]
  #[CLI\Usage(name: self::NAME, description: 'Generate element usage.')]
  public function elementUsage(): int {
    $result = $this->runBatch();

    return is_array($result) && isset(array_shift($result)['error']) ? self::EXIT_FAILURE : self::EXIT_SUCCESS;
  }

  /**
   * Runs the element usage batch.
   *
   * @return mixed
   *   Return value of drush_backend_batch_process().
   */
  protected function runBatch(): mixed {
    $batch = ElementsController::setElementUsageBatch();

    batch_set($batch);
    $batch['progressive'] = FALSE;
    return drush_backend_batch_process();
  }

}
