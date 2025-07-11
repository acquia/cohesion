<?php

namespace Drupal\cohesion_sync\EventSubscriber\Import;

use Drupal\cohesion_sync\SyncConfigImporter;
use Drupal\Core\Config\ConfigImporter;
use Drupal\Core\EventSubscriber\ConfigImportSubscriber;

/**
 * Config import subscriber for config import events.
 */
class SyncConfigImportSubscriber extends ConfigImportSubscriber {

  /**
   * Validates configuration being imported does not have unmet dependencies.
   *
   * @param \Drupal\Core\Config\ConfigImporter $config_importer
   *   The configuration importer.
   */
  protected function validateDependencies(ConfigImporter $config_importer) {
    $core_extension = $config_importer->getStorageComparer()->getSourceStorage()->read('core.extension');
    $existing_dependencies = [
      'module' => array_keys($core_extension['module']),
      'theme' => array_keys($core_extension['theme']),
    ];

    if (!$config_importer instanceof SyncConfigImporter) {
      $existing_dependencies['config'] = $config_importer->getStorageComparer()->getSourceStorage()->listAll();
    }

    $theme_data = $this->getThemeData();
    $module_data = $this->moduleExtensionList->getList();

    // Validate the dependencies of all the configuration. We have to validate
    // the entire tree because existing configuration might depend on
    // configuration that is being deleted.
    foreach ($config_importer->getStorageComparer()->getSourceStorage()->listAll() as $name) {
      // Ensure that the config owner is installed. This checks all
      // configuration including configuration entities.
      [$owner] = explode('.', $name, 2);
      if ($owner !== 'core') {
        $message = FALSE;
        if (!isset($core_extension['module'][$owner]) && isset($module_data[$owner])) {
          $message = $this->t('Configuration %name depends on the %owner module that will not be installed after import.', [
            '%name' => $name,
            '%owner' => $module_data[$owner]->info['name'],
          ]);
        }
        elseif (!isset($core_extension['theme'][$owner]) && isset($theme_data[$owner])) {
          $message = $this->t('Configuration %name depends on the %owner theme that will not be installed after import.', [
            '%name' => $name,
            '%owner' => $theme_data[$owner]->info['name'],
          ]);
        }
        elseif (!isset($core_extension['module'][$owner]) && !isset($core_extension['theme'][$owner])) {
          $message = $this->t('Configuration %name depends on the %owner extension that will not be installed after import.', [
            '%name' => $name,
            '%owner' => $owner,
          ]);
        }

        if ($message) {
          $config_importer->logError($message);
          continue;
        }
      }

      $data = $config_importer->getStorageComparer()->getSourceStorage()->read($name);
      // Configuration entities have dependencies on modules, themes, and other
      // configuration entities that we can validate. Their content dependencies
      // are not validated since we assume that they are soft dependencies.
      // Configuration entities can be identified by having 'dependencies' and
      // 'uuid' keys.
      if (isset($data['dependencies']) && isset($data['uuid'])) {
        if (!$config_importer instanceof SyncConfigImporter) {
          $dependencies = ['module', 'theme', 'config'];
        }
        else {
          $dependencies = ['module', 'theme'];
        }
        $dependencies_to_check = array_intersect_key($data['dependencies'], array_flip($dependencies));

        foreach ($dependencies_to_check as $type => $dependencies) {
          $diffs = array_diff($dependencies, $existing_dependencies[$type]);
          if (!empty($diffs)) {
            $message = FALSE;
            switch ($type) {
              case 'module':
                $message = $this->formatPlural(
                  count($diffs),
                  'Configuration %name depends on the %module module that will not be installed after import.',
                  'Configuration %name depends on modules (%module) that will not be installed after import.',
                  [
                    '%name' => $name,
                    '%module' => implode(', ', $this->getNames($diffs, $module_data)),
                  ]
                );
                break;

              case 'theme':
                $message = $this->formatPlural(
                  count($diffs),
                  'Configuration %name depends on the %theme theme that will not be installed after import.',
                  'Configuration %name depends on themes (%theme) that will not be installed after import.',
                  [
                    '%name' => $name,
                    '%theme' => implode(', ', $this->getNames($diffs, $theme_data)),
                  ]
                );
                break;

              case 'config':
                $message = $this->formatPlural(
                  count($diffs),
                  'Configuration %name depends on the %config configuration that will not exist after import.',
                  'Configuration %name depends on configuration (%config) that will not exist after import.',
                  ['%name' => $name, '%config' => implode(', ', $diffs)]
                );
                break;
            }

            if ($message) {
              $config_importer->logError($message);
            }
          }
        }
      }
    }
  }

}
