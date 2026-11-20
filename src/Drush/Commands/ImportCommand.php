<?php

namespace Drupal\cohesion\Drush\Commands;

use Drupal\cohesion\Controller\AdministrationController;
use Drupal\cohesion\Services\CohesionUtils;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Drush command to import Site Studio assets and rebuild element styles.
 */
final class ImportCommand extends DrushCommands {

  use StringTranslationTrait;

  const NAME = 'cohesion:import';

  /**
   * ImportCommand constructor.
   */
  public function __construct(
    protected CohesionUtils $cohesionUtils,
    protected ConfigFactoryInterface $configFactory,
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
      $container->get('config.factory'),
      $container->get('string_translation'),
    );
  }

  /**
   * Import assets and rebuild element styles.
   *
   * @command cohesion:import
   * @aliases dx8:import
   * @usage cohesion:import Import assets and rebuild element styles.
   */
  #[CLI\Command(name: self::NAME, aliases: ['dx8:import'])]
  #[CLI\Usage(name: self::NAME, description: 'Import assets and rebuild element styles.')]
  public function import(): int {
    $io = $this->io();

    if (!$this->cohesionUtils->apiKeysConfigured()) {
      $io->error($this->t('Please configure your Site Studio API keys before importing assets.'));
      return self::EXIT_FAILURE;
    }

    $io->writeln($this->t('Importing assets.'));
    $errors = $this->runImport();

    if ($errors) {
      $io->error($this->t('@error', ['@error' => $errors['error']]));
      return self::EXIT_FAILURE;
    }

    $io->success($this->t('Congratulations. Site Studio is installed and up to date.'));
    return self::EXIT_SUCCESS;
  }

  /**
   * Imports s3forms and rebuilds element styles.
   *
   * @return array|false
   *   An array containing an 'error' key on failure, FALSE on success.
   */
  protected function runImport(): array|false {
    $config = $this->configFactory->get('cohesion.settings');

    if ($config->get('api_key') === '') {
      return ['error' => $this->t('Your Site Studio API KEY has not been set.') . $config->get('site_id')];
    }

    $batch = AdministrationController::batchAction(TRUE);
    if (isset($batch['error'])) {
      return $batch;
    }

    foreach ($batch['operations'] as $operation) {
      $context = ['results' => []];
      $function = $operation[0];
      $args = $operation[1];

      if (function_exists($function)) {
        call_user_func_array($function, array_merge($args, [&$context]));
      }
    }

    cohesion_website_settings_batch_import_finished(TRUE, $context['results'], '');

    if (isset($context['results']['error'])) {
      return ['error' => $context['results']['error']];
    }

    return FALSE;
  }

}
