<?php

namespace Drupal\cohesion\Drush\Commands;

use Drupal\cohesion\Services\CohesionUtils;
use Drupal\cohesion_website_settings\Controller\WebsiteSettingsController;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Drush command to resave all Site Studio config entities.
 */
final class RebuildCommand extends DrushCommands {

  use StringTranslationTrait;

  const NAME = 'cohesion:rebuild';

  /**
   * RebuildCommand constructor.
   */
  public function __construct(
    protected CohesionUtils $cohesionUtils,
    TranslationInterface $stringTranslation,
  ) {
    parent::__construct();
    $this->stringTranslation = $stringTranslation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('cohesion.utils'),
      $container->get('string_translation'),
    );
  }

  /**
   * Resave all Site Studio config entities.
   *
   * @command cohesion:rebuild
   * @aliases dx8:rebuild
   * @option no-cache-clear Do not clear caches after the rebuild.
   * @usage cohesion:rebuild Resave all Site Studio config entities.
   */
  #[CLI\Command(name: self::NAME, aliases: ['dx8:rebuild'])]
  #[CLI\Option(name: 'no-cache-clear', description: 'Do not clear caches after the rebuild.')]
  #[CLI\Usage(name: self::NAME, description: 'Resave all Site Studio config entities.')]
  public function rebuild(array $options = ['no-cache-clear' => FALSE]): int {
    $io = $this->io();

    if (!$this->cohesionUtils->apiKeysConfigured()) {
      $io->error($this->t('Please configure your Site Studio API keys before running a rebuild.'));
      return self::EXIT_FAILURE;
    }

    $time_start = microtime(TRUE);

    $io->writeln($this->t('Rebuilding all entities.'));
    $verbose = $this->output()->isVerbose();
    $batch = WebsiteSettingsController::batch(TRUE, $verbose, (bool) $options['no-cache-clear']);
    batch_set($batch);
    $batch['progressive'] = FALSE;
    $result = drush_backend_batch_process();

    if ($verbose) {
      $io->success('Finished rebuilding (' . number_format(microtime(TRUE) - $time_start, 2, '.', '') . ' seconds).');
    }
    else {
      $io->success('Finished rebuilding.');
    }

    return is_array($result) && isset(array_shift($result)['error']) ? self::EXIT_FAILURE : self::EXIT_SUCCESS;
  }

}
