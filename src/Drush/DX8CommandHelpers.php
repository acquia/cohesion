<?php

namespace Drupal\cohesion\Drush;

use Drupal\cohesion_website_settings\Controller\WebsiteSettingsController;
use Drupal\cohesion\Controller\AdministrationController;

/**
 * Class DX8CommandHelpers.
 *
 * @package Drupal\cohesion\Drush
 */
final class DX8CommandHelpers {

  /**
   * Import s3forms and rebuild element styles.
   */
  public static function import() {
    $config = \Drupal::config('cohesion.settings');

    if ($config->get('api_key') !== '') {
      // Get a list of the batch items.
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

      // Give access to all routes.
      // Enable the routes.
      cohesion_website_settings_batch_import_finished(TRUE, $context['results'], '');

      if (isset($context['results']['error'])) {
        return ['error' => $context['results']['error']];
      }
    }
    else {
      return ['error' => t('Your Cohesion API KEY has not been set.') . $config->get('site_id')];
    }

    return FALSE;

  }

  /**
   * Resave all Cohesion config entities.
   *
   * @return FALSE | array
   */
  public static function rebuild() {
    // Reset temporary template list.
    $batch = WebsiteSettingsController::batch(TRUE);
    batch_set($batch);
    drush_backend_batch_process();
    return FALSE;
  }

}
