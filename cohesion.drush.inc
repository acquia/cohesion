<?php

use Drupal\cohesion\Drush\DX8CommandHelpers;

/**
 * Implements hook_drush_command().
 */
function cohesion_drush_command() {
  return [
    'dx8' => [
      'description' => dt('DX8 Drush commands.'),
      'arguments' => ['op' => 'Action to run. Can be any of rebuild|import'],
    ],
    'dx8-import' => [
      'description' => dt('Import assets and rebuild element styles (replacement for the CRON).'),
      'aliases' => ['dx8:import', 'cohesion:import', 'cohesion-import'],
    ],
    'dx8-rebuild' => [
      'description' => dt('Resave all Cohesion config entities.'),
      'aliases' => ['dx8:rebuild', 'cohesion:rebuild', 'cohesion-rebuild'],
    ],
  ];
}

/**
 * Router for all dx8 drush commands.
 */
function drush_cohesion_dx8($op = '', $oldNamingConvention = true) {

  $possibleActions = ['import', 'rebuild'];
  if (!in_array($op, $possibleActions)) {
    return drush_set_error('DX8', "No operation found with the name '$op' - should be of ".implode(', ', $possibleActions));
  }

  if ($oldNamingConvention) {
    drush_print('This command is getting deprecated soon!', 10);
    drush_print('Use drush dx8:'.$op.' instead.', 10);
  }

  switch ($op) {
    case 'import':
      drush_log(t('Importing DX8.'));
      $errors = DX8CommandHelpers::import();
      if($errors){
        drush_set_error('DX8', $errors['error']);
      }else{
        drush_log('Cohesion is installed and up to date.');
      }
      break;

    case 'rebuild':
      drush_log(t('Rebuilding'));
      $errors = DX8CommandHelpers::rebuild();
      if($errors){
        drush_set_error('DX8', $errors['error']);
      }else{
        drush_log(t('Cohesion entities have successfully been rebuilt. Your styles are now live.'));
      }
      break;
  }
}

function drush_cohesion_dx8_rebuild($op = '') {
  return drush_cohesion_dx8('rebuild', false);
}

function drush_cohesion_dx8_import($op = '') {
  return drush_cohesion_dx8('import', false);
}
