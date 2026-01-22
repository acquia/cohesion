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
    $api_key = \Drupal::config('cohesion.settings')->get('api_key');

    if (empty($api_key)) {
      $this->say($this->t('[error] Please configure your Site Studio API keys before importing assets. You can now build your website.'));
      return CommandResult::exitCode(self::EXIT_FAILURE);
    }

    $this->say($this->t('Importing assets.'));
    $errors = DX8CommandHelpers::import();

    if ($errors) {
      $this->say($this->t('[error] @error', ['@error' => $errors['error']]));
      return CommandResult::exitCode(self::EXIT_FAILURE);
    }

    $this->yell($this->t('Congratulations. Site Studio is installed and up to date.'));
    return CommandResult::exitCode(self::EXIT_SUCCESS);
  }

  /**
   * Resave all Site Studio config entities.
   *
   * @command cohesion:rebuild
   * @aliases dx8:rebuild
   * @option no-cache-clear
   * @usage drush cohesion:rebuild
   */
  public function rebuild($options = ['no-cache-clear' => FALSE]) {
    $time_start = microtime(TRUE);

    $this->say($this->t('Rebuilding all entities.'));
    $result = DX8CommandHelpers::rebuild($options);

    if ($options['verbose']) {
      $this->yell('Finished rebuilding (' . number_format((float) microtime(TRUE) - $time_start, 2, '.', '') . ' seconds).');
    }
    else {
      $this->yell('Finished rebuilding.');
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
      $this->say('Running in DRY-RUN mode - no data will be deleted.');
    }

    $orphaned_revisions = DX8CommandHelpers::findOrphanedRevisions();
    $orphan_count = count($orphaned_revisions);

    if ($orphan_count === 0) {
      $this->say('No orphaned cohesion layout revisions found.');
      return CommandResult::exitCode(self::EXIT_SUCCESS);
    }

    $this->say("Found {$orphan_count} orphaned cohesion layout revisions.");

    if ($dry_run) {
      $this->say("DRY-RUN: Would delete {$orphan_count} orphaned revisions.");
      return CommandResult::exitCode(self::EXIT_SUCCESS);
    }

    if (!$this->confirm('Are you sure you want to delete these orphaned revisions?')) {
      $this->say('Operation cancelled.');
      return CommandResult::exitCode(self::EXIT_SUCCESS);
    }

    $result = DX8CommandHelpers::cleanupOrphans(FALSE);

    $this->say("Cleanup completed: {$result['deleted']} deleted, {$result['errors']} skipped.");

    if ($result['errors'] > 0) {
      $this->say('Some revisions were skipped (default revisions, already deleted, or protected).');
      $this->say('For detailed information, run: drush watchdog:show --type=cohesion');
    }
    else {
      $this->say('All orphaned revisions cleaned successfully!');
    }

    return CommandResult::exitCode(self::EXIT_SUCCESS);
  }

}
