<?php

namespace Drupal\cohesion_sync\Hook;

use Drupal\cohesion\Entity\CohesionSettingsInterface;
use Drupal\cohesion\Services\CohesionUtils;
use Drupal\cohesion_sync\Drush\CommandHelpers;
use Drupal\cohesion_sync\Services\PackageImportHandler;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Extension\ExtensionPathResolver;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\Core\Hook\Attribute\Hook;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Yaml\Yaml;

/**
 * Hook implementations for Cohesion sync.
 */
class CohesionSyncHooks {

  public function __construct(
    protected AccountInterface $current_user,
    protected ConfigFactoryInterface $config_factory,
    protected RequestStack $request_stack,
    protected ExtensionPathResolver $extension_path_resolver,
    protected LoggerChannelFactoryInterface $logger_channel_factory,
    protected CommandHelpers $command_helpers,
    protected PackageImportHandler $package_import_handler,
    protected RouteMatchInterface $current_route_match,
    protected CohesionUtils $cohesion_utils,
  ) {}

  /**
   * Implements hook_entity_operation_alter().
   */
  #[Hook('entity_operation_alter')]
  public function entity_operation_alter(array &$operations, EntityInterface $entity): void {
    if ($entity instanceof CohesionSettingsInterface) {
      // Add "Export package to YML file." and "Lock entity" option to the
      // actions dropdown on all Site Studio entity list builders.
      if ($this->current_user->hasPermission('access cohesion sync') && $entity->get('status')) {
        // Is entity exportable & isn't something like a custom component.
        if ($entity->getEntityTypeId() && $entity->uuid()) {
          $url = Url::fromRoute('cohesion_sync.operation_export_single', [
            'entity_type' => $entity->getEntityTypeId(),
            'entity_uuid' => $entity->uuid(),
          ]);

          $url->setOption('query', [
            'destination' => $this->request_stack->getCurrentRequest()->getRequestUri(),
          ]);

          $operations['export_package_to_file'] = [
            'title' => t('Export package to .tar.gz file'),
            'url' => Url::fromRoute('cohesion_sync.export.export_single_entity_package', [
              'entity_type' => $entity->getEntityTypeId(),
              'entity_uuid' => $entity->uuid(),
            ]),
            'weight' => 49,
          ];

          if ($this->config_factory->get('cohesion.settings')->get('sync_legacy_visibility')) {
            $operations['legacy_export_package_to_file'] = [
              'title' => t('Export package to YML file'),
              'url' => $url,
              'weight' => 50,
            ];
          }

          $operations['toggle_lock_for_sync'] = [
            'title' => !$entity->isLocked() ? t('Lock entity') : t('Unlock entity'),
            'url' => Url::fromRoute('cohesion_sync.entity_lock_toggle', [
              'entity_type' => $entity->getEntityTypeId(),
              'entity_uuid' => $entity->uuid(),
            ]),
            'weight' => 60,
          ];
        }
      }
    }
  }

  /**
   * Implements hook_modules_installed().
   * @throws \Exception
   */
  #[Hook('modules_installed')]
  public function modules_installed($modules, $is_syncing): void {
    // Do not attempt importing package if Site Studio settings are not
    // initialized or config is already being synchronized.
    if ($this->cohesion_utils && $this->cohesion_utils->usedx8Status() === FALSE) {
      return;
    }

    foreach ($modules as $module) {
      $module_path = $this->extension_path_resolver->getPath('module', $module);
      // Check to see if the config/dx8/packages.yml file exists.
      $packages_yaml_file = $module_path . "/config/dx8/packages.yml";

      // Handle legacy package format.
      if (file_exists($packages_yaml_file)) {
        // Decode the file.
        $config = Yaml::parse(file_get_contents($packages_yaml_file));

        if (is_array($config)) {
          // Loop through the packages and deploy them.
          foreach ($config as $path) {
            // If it's a local path, patch in the path to the module.
            if (file_exists($module_path . '/' . $path)) {
              $path = $module_path . '/' . $path;
            }

            // Attempt to deploy the package.
            try {
              $operations = $this->command_helpers->import(TRUE, FALSE, $path);

              $batch = [
                'title' => t('Importing configuration.'),
                'operations' => $operations,
                'progressive' => FALSE,
              ];

              batch_set($batch);

            }
            catch (\Exception $e) {
              // Tell dblog what happened.
              $this->logger_channel_factory->get('cohesion')->error($e->getMessage());
            }
          }
        }
      }
      // Do not attempt new format package import if config is in sync.
      elseif ($is_syncing === TRUE) {
        return;
      }

      // Handle new package format.
      $package_list_path = $module_path . COHESION_SYNC_DEFAULT_MODULE_PACKAGES;
      if (file_exists($package_list_path)) {
        $this->package_import_handler->importPackagesFromPath($package_list_path);
      }
    }
  }

  /**
   * Implements hook_page_attachments_alter().
   */
  #[Hook('page_attachments_alter')]
  public function page_attachments_alter(array &$attachments): void {
    // Only attach for the sync import route.
    $route = $this->current_route_match->getRouteName();
    if ($route === 'cohesion_sync.import') {
      // Attach sync file chunk that can be used in module file-js.
      $attachments['#attached']['drupalSettings']['cohesion']['urls']['sync_file_chunk'] = Url::fromRoute('cohesion_sync.chunked')
        ->toString();
    }

    // Only attach for sync react app package add & edit forms.
    $sync_react_routes = [
      'entity.cohesion_sync_package.add_form',
      'entity.cohesion_sync_package.edit_form',
    ];
    if (in_array($route, $sync_react_routes)) {
      $attachments['#attached']['drupalSettings']['cohesion']['urls']['sync_refresh'] = Url::fromRoute('cohesion_sync.refresh')->toString();
    }
  }

  /**
   * @param $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param $form_id
   * @return void
   *
   * Add a checkbox to the system settings form to show/hide the legacy sync
   * options from the UI.
   */
  #[Hook('form_alter')]
  public function form_alter(&$form, FormStateInterface $form_state, $form_id): void {
    if ($form_id === 'cohesion_system_settings_form') {
      $form['sync_legacy_visibility_accordion'] = [
        '#type' => 'details',
        '#collapsible' => TRUE,
        '#collapsed' => FALSE,
        '#title' => t('Show legacy sync in UI'),
        '#weight' => 10,
        'sync_legacy_visibility' => [
          '#type' => 'checkbox',
          '#title' => t('Show legacy sync in UI'),
          '#required' => FALSE,
          '#default_value' => $this->config_factory->get('cohesion.settings')->get('sync_legacy_visibility') ?: 0,
          '#disabled' => $this->isSyncLegacyOverridden(),
          '#wrapper_attributes' => ['class' => ['clearfix']],
          '#attributes' => [
            'class' => [],
          ],
          '#description' => t('Show the legacy sync import / export in the UI.'),
        ],
        '#open' => 'panel-open',
      ];

      $form['#submit'][] = 'cohesion_sync_save_sync_legacy_visibility';
    }
  }

  /**
   *  Check if config variable is overridden by the settings.php.
   *
   * @return bool
   */
  private function isSyncLegacyOverridden(): bool {
    $original = $this->config_factory->getEditable('cohesion.settings')->get('sync_legacy_visibility');
    $current = $this->config_factory->get('cohesion.settings')->get('sync_legacy_visibility');
    return $original != $current;
  }

}
