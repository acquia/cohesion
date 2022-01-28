<?php

namespace Drupal\cohesion\Commands;

use Consolidation\AnnotatedCommand\CommandResult;
use Drupal\cohesion\Drush\DX8CommandHelpers;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drush\Commands\DrushCommands;

/**
 * Drush command for import and rebuild.
 *
 * @package Drupal\cohesion\Commands
 */
class Dx8Commands extends DrushCommands {
  use StringTranslationTrait;

  /**
   * Dx8Commands constructor.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   */
  public function __construct(TranslationInterface $stringTranslation) {
    parent::__construct();
    $this->stringTranslation = $stringTranslation;
  }

  /**
   * Import assets and rebuild element styles (replacement for the CRON).
   *
   * @command cohesion:import
   * @aliases dx8:import
   * @usage drush cohesion:import
   */
  public function import() {
    $this->say($this->t('Importing assets.'));
    $errors = DX8CommandHelpers::import();
    if ($errors) {
      $this->say('[error] ' . $errors['error']);
      return CommandResult::exitCode(self::EXIT_FAILURE);
    }
    else {
      $this->yell($this->t('Congratulations. Site Studio is installed and up to date. You can now build your website.'));
      return CommandResult::exitCode(self::EXIT_SUCCESS);
    }
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

    // Output results.
    // Output results.
    if ($options['verbose']) {
      $this->yell('Finished rebuilding (' . number_format((float) microtime(TRUE) - $time_start, 2, '.', '') . ' seconds).');
    }
    else {
      $this->yell('Finished rebuilding.');
    }

    // Status code.
    return is_array($result) && isset(array_shift($result)['error']) ? CommandResult::exitCode(self::EXIT_FAILURE) : CommandResult::exitCode(self::EXIT_SUCCESS);
  }

}
