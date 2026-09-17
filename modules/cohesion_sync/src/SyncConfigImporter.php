<?php

namespace Drupal\cohesion_sync;

use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Config\ConfigImporter;
use Drupal\Core\Config\ConfigImporterEvent;
use Drupal\Core\Config\ConfigImporterException;

/**
 * Sync config importer.
 *
 * @package Drupal\cohesion_sync
 */
class SyncConfigImporter extends ConfigImporter {

  /**
   * Dispatches validate event for a ConfigImporter object.
   * This is almost the same as \Drupal\Core\Config\ConfigImporter expect it
   * doesn't check modules, theme and other config entity dependencies. This
   * is because the entire tree is not supplied when performing a cohesion_sync
   * import.
   *
   * @throws \Drupal\Core\Config\ConfigImporterException
   *   Exception thrown if the validate event logged any errors.
   */
  public function validate() {
    if (!$this->validated) {
      // Create the list of installs and uninstalls.
      $this->createExtensionChangelist();
      // Validate renames.
      foreach ($this->getUnprocessedConfiguration('rename') as $name) {
        $names = $this->storageComparer->extractRenameNames($name);
        $old_entity_type_id = $this->configManager->getEntityTypeIdByName($names['old_name']);
        $new_entity_type_id = $this->configManager->getEntityTypeIdByName($names['new_name']);
        if ($old_entity_type_id != $new_entity_type_id) {
          $this->logError($this->t('Entity type mismatch on rename. @old_type not equal to @new_type for existing configuration @old_name and staged configuration @new_name.',
            [
              '@old_type' => $old_entity_type_id,
              '@new_type' => $new_entity_type_id,
              '@old_name' => $names['old_name'],
              '@new_name' => $names['new_name'],
            ]
          ));
        }
        // Has to be a configuration entity.
        if (!$old_entity_type_id) {
          $this->logError($this->t('Rename operation for simple configuration. Existing configuration @old_name and staged configuration @new_name.',
            [
              '@old_name' => $names['old_name'],
              '@new_name' => $names['new_name'],
            ]
          ));
        }
      }
      $this->eventDispatcher->dispatch(new ConfigImporterEvent($this), ConfigEvents::IMPORT_VALIDATE);
      if (count($this->getErrors())) {
        $errors = array_merge(['There were errors validating the config synchronization.'], $this->getErrors());
        throw new ConfigImporterException(implode(PHP_EOL, $errors));
      }
      else {
        $this->validated = TRUE;
      }
    }
    return $this;
  }

}
