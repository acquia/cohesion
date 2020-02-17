<?php

namespace Drupal\cohesion_sync\Plugin\Sync;

use Drupal\cohesion_sync\SyncPluginBase;
use Drupal\file\Entity\File;

/**
 * Class FileSync.
 *
 * @package Drupal\cohesion_sync
 *
 * @Sync(
 *   id = "file_sync",
 *   name = @Translation("File entity and content sync"),
 *   interface = "Drupal\file\FileInterface"
 * )
 */
class FileSync extends SyncPluginBase {

  /**
   * {@inheritdoc}
   *
   * @testme
   */
  public function buildExport($entity) {
    /** @var \Drupal\file\Entity\File $entity */
    $struct = [];

    // Get all the field values into the struct.
    foreach ($entity->getFields() as $field_key => $value) {
      // Get the value of the field.
      if ($value = $entity->get($field_key)->getValue()) {
        $value = reset($value);
      }
      else {
        continue;
      }

      // Add it to the export.
      if (isset($value['value'])) {
        $struct[$field_key] = $value['value'];
      }
    }

    // Load and base64 the file contents.
    if (file_exists($entity->getFileUri())) {
      $struct['content'] = base64_encode(file_get_contents($entity->getFileUri()));
    }
    else {
      throw new \Exception($this->t('File @uri does not exist on the local filesystem although the entity @label exists.', ['@uri' => $entity->getFileUri(), '@label' => $entity->label()]));
    }

    return $struct;
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies($entity) {
    /** @var \Drupal\file\Entity\File $entity */
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function validatePackageEntryShouldApply($entry) {
    parent::validatePackageEntryShouldApply($entry);

    if (!isset($entry['uuid'])) {
      throw new \Exception($this->t('Import did not specify a file UUID.'));
    }

    if (!isset($entry['uri'])) {
      throw new \Exception($this->t('Import did not specify a file URI.'));
    }

    if (!isset($entry['content'])) {
      throw new \Exception($this->t('Import did not specify file contents.'));
    }

    if (!base64_decode($entry['content'])) {
      // Failed to decode (the source site didn't have a file).
      // But we're going to say this is a pass and just not create the file on the dest.
      $entry['content'] = TRUE;
    }

    // File already exists (by UUID) - flag it.
    if ($entity = $this->entityRepository->loadEntityByUuid('file', $entry['uuid'])) {
      if ($entity->get('uri')->getValue()[0]['value'] !== $entry['uri']) {
        throw new \Exception($this->t('An entity with this UUID already exists but the URI does not match.'));
      }

      // See if any of the entity data has changed.
      foreach ($entry as $key => $value) {
        if ($entity->hasField($key) && $key !== 'fid' && $key !== 'created' && $key !== 'changed') {
          $entity_val = $entity->get($key)->getValue()[0];

          if (isset($entity_val['value']) && $entity_val['value'] !== $value) {
            // Entity has changes.
            // Ask the user what to do.
            return ENTRY_EXISTING_ASK;
          }
        }
      }

      // See if the file contents have changed.
      @ $local_file_contents = file_get_contents($entry['uri']);
      if ($local_file_contents) {
        if (base64_encode($local_file_contents) !== $entry['content']) {
          // Ask the user what to do.
          return ENTRY_EXISTING_ASK;
        }
      } else {
        throw new \Exception($this->t('File @uri does not exist on the local filesystem although the entity exists.', ['@uri' => $entry['uri']]));
      }

      // Nothing changed, so ignore it.
      return ENTRY_EXISTING_NO_CHANGES;
    }
    // New entity so, just apply it.
    else {
      return ENTRY_NEW_IMPORTED;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function applyPackageEntry($entry) {
    parent::applyPackageEntry($entry);

    // Load existing entity.
    try {
      $entity = $this->entityRepository->loadEntityByUuid('file', $entry['uuid']);
    }
    catch (\Throwable $e) {
      $entity = NULL;
    }

    // Set up the directory.
    $dirname = dirname($entry['uri']);
    file_prepare_directory($dirname, FILE_MODIFY_PERMISSIONS | FILE_CREATE_DIRECTORY);

    // Only attempt to save the file if it can be decoded, otherwise just create the entity.
    if ($content = base64_decode($entry['content'])) {
      if (!file_unmanaged_save_data($content, $entry['uri'], FILE_EXISTS_REPLACE)) {
        throw new \Exception($this->t('Unable to save file %fileuri', ['%fileuri' => $entry['uri']]));
      }

      // Create new entity.
      if (!$entity) {
        // $user = \Drupal::currentUser();
        $entity = File::create([
          'uri' => $entry['uri'],
          'uuid' => $entry['uuid'],
          'status' => FILE_STATUS_PERMANENT,
          'langcode' => $entry['langcode'],
        ]);
      }

      // Apply all the $entry data to it.
      $entity->setFilename($entry['filename']);
      $entity->setFileUri($entry['uri']);
      $entity->setMimeType($entry['filemime']);
      $entity->setSize($entry['filesize']);

      $entity->save();
    }

  }

  /**
   * {@inheritdoc}
   */
  public function getActionData($entry, $action_state) {
    return [
      'entity_type_label' => $this->t('File')->__toString(),
      'entity_label' => $entry['uri'],
      'entry_uuid' => $entry['uuid'],
      'entry_action_state' => $action_state,
    ];
  }

}
