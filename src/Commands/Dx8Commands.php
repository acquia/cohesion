<?php

namespace Drupal\cohesion\Commands;

use Drupal\cohesion\Drush\DX8CommandHelpers;
use Drush\Commands\DrushCommands;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class Dx8Commands.
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
    }
    else {
      $this->yell($this->t('Congratulations. Cohesion is installed and up to date. You can now build your website.'));
    }
  }

  /**
   * Resave all Cohesion config entities.
   *
   * @command cohesion:rebuild
   * @aliases dx8:rebuild
   * @usage drush cohesion:rebuild
   */
  public function rebuild() {
    $this->say($this->t('Rebuilding all entities.'));
    DX8CommandHelpers::rebuild();
    $this->yell(t('Finished rebuilding.'));
  }

}
