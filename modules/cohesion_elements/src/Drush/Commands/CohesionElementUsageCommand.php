<?php

namespace Drupal\cohesion_elements\Drush\Commands;

use Drupal\cohesion_elements\Controller\ElementsController;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drush\Commands\DrushCommands;
use Consolidation\AnnotatedCommand\CommandResult;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides drush commands for element usage.
 *
 * @package Drupal\cohesion_elements\Drush\Commands
 */
class CohesionElementUsageCommand extends DrushCommands {
  use StringTranslationTrait;

  /**
   * ElementUsageCommand constructor.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   */
  public function __construct(TranslationInterface $stringTranslation) {
    parent::__construct();
    $this->stringTranslation = $stringTranslation;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return static
   */
  public static function create(ContainerInterface $container): self {
    $commandHandler = new self(
      $container->get('string_translation')
    );

    return $commandHandler;
  }

  /**
   * Generate element usage.
   *
   * @validate-module-enabled cohesion_elements
   *
   * @command sitestudio:element-usage
   */
  public function runElementUsage() {
    $result = $this->elementUsage();

    return is_array($result) && isset(array_shift($result)['error']) ? CommandResult::exitCode(self::EXIT_FAILURE) : CommandResult::exitCode(self::EXIT_SUCCESS);
  }

  /**
   * @return mixed
   */
  public function elementUsage() {
    $batch = ElementsController::setElementUsageBatch();

    batch_set($batch);
    $batch['progressive'] = FALSE;
    return drush_backend_batch_process();
  }

}
