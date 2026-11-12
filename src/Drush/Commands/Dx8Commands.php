<?php

namespace Drupal\cohesion\Drush\Commands;

use Consolidation\AnnotatedCommand\CommandResult;
use Drupal\cohesion\Drush\DX8CommandHelpers;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drush\Commands\DrushCommands;

/**
 * Drush command for import and rebuild.
 */
class Dx8Commands extends DrushCommands {
  use StringTranslationTrait;

  /**
   * Dx8Commands constructor.
   */
  public function __construct(TranslationInterface $stringTranslation) {
    parent::__construct();
    $this->stringTranslation = $stringTranslation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('string_translation')
    );
  }

  /**
   * Import assets and rebuild element styles.
   *
   * @command cohesion:import
   * @aliases dx8:import
   * @usage drush cohesion:import
   */
  public function import() {
    if (!\Drupal::service('cohesion.utils')->apiKeysConfigured()) {
      $this->output()->writeln($this->t('[error] Please configure your Site Studio API keys before importing assets.'));
      return CommandResult::exitCode(self::EXIT_FAILURE);
    }

    $this->output()->writeln($this->t('Importing assets.'));
    $errors = DX8CommandHelpers::import();

    if ($errors) {
      $this->output()->writeln($this->t('[error] @error', ['@error' => $errors['error']]));
      return CommandResult::exitCode(self::EXIT_FAILURE);
    }

    $this->output()->writeln($this->t('Congratulations. Site Studio is installed and up to date.'));
    return CommandResult::exitCode(self::EXIT_SUCCESS);
  }

  /**
   * Resave all Site Studio config entities.
   *
   * @command cohesion:rebuild
   * @aliases dx8:rebuild
   * @option no-cache-clear
   * @usage drush cohesion:rebuild
   *   Rebuild all Site Studio entities.
   */
  public function rebuild($options = ['no-cache-clear' => FALSE]) {
    if (!\Drupal::service('cohesion.utils')->apiKeysConfigured()) {
      $this->output()->writeln(
        $this->t('[error] Please configure your Site Studio API keys before running a rebuild.')
      );
      return CommandResult::exitCode(self::EXIT_FAILURE);
    }

    $time_start = microtime(TRUE);

    $this->output()->writeln($this->t('Rebuilding all entities.'));
    $result = DX8CommandHelpers::rebuild($options);

    if ($options['verbose']) {
      $this->output()->writeln(
        'Finished rebuilding (' . number_format((float) microtime(TRUE) - $time_start, 2, '.', '') . ' seconds).'
      );
    }
    else {
      $this->output()->writeln('Finished rebuilding.');
    }

    return is_array($result) && isset(array_shift($result)['error']) ? CommandResult::exitCode(self::EXIT_FAILURE) : CommandResult::exitCode(self::EXIT_SUCCESS);
  }

  /**
   * Clean up orphaned cohesion layout revisions.
   *
   * @command sitestudio:cleanup-orphans
   * @aliases cohesion:cleanup-orphans
   * @option dry-run Run in dry-run mode without deleting any data.
   * @usage drush sitestudio:cleanup-orphans --dry-run
   * @usage drush cohesion:cleanup-orphans --dry-run
   * @usage drush sitestudio:cleanup-orphans
   */
  public function cleanupOrphans($options = ['dry-run' => FALSE]) {
    $dry_run = $options['dry-run'];

    if ($dry_run) {
      $this->output()->writeln('Running in DRY-RUN mode - no data will be deleted.');
    }

    // Use a SQL COUNT query — safe for any table size, no PHP memory impact.
    $orphan_count = \Drupal::service('cohesion.layout_revision_manager')
      ->countOrphanedRevisions();

    if ($orphan_count === 0) {
      $this->output()->writeln('No orphaned cohesion layout revisions found.');
      return CommandResult::exitCode(self::EXIT_SUCCESS);
    }

    $this->output()->writeln("Found {$orphan_count} orphaned cohesion layout revisions.");

    if ($dry_run) {
      $this->output()->writeln("DRY-RUN: Would delete {$orphan_count} orphaned revisions.");
      return CommandResult::exitCode(self::EXIT_SUCCESS);
    }

    if (!$this->io()->confirm('Are you sure you want to delete these orphaned revisions?', FALSE)) {
      $this->output()->writeln('Operation cancelled.');
      return CommandResult::exitCode(self::EXIT_SUCCESS);
    }

    $this->output()->writeln(
      "Processing in batches of " . COHESION_ORPHANS_DRUSH_BATCH_SIZE . " via Drupal batch API..."
    );
    $result = DX8CommandHelpers::cleanupOrphansBatch(FALSE, $orphan_count);

    return is_array($result) && isset(array_shift($result)['error'])
      ? CommandResult::exitCode(self::EXIT_FAILURE)
      : CommandResult::exitCode(self::EXIT_SUCCESS);
  }

}
