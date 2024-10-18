<?php

namespace Drupal\cohesion_sync\Plugin\Sync;

use Drupal\cohesion_sync\SyncPluginBase;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\file\Entity\File;
use Drupal\file\FileInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * File sync plugin.
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
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * SyncPluginBase constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\Core\Entity\EntityRepository $entity_repository
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityRepository $entity_repository, EntityTypeManagerInterface $entity_type_manager, TranslationInterface $string_translation, FileSystemInterface $file_system) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_repository, $entity_type_manager, $string_translation);

    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.repository'),
      $container->get('entity_type.manager'),
      $container->get('string_translation'),
      $container->get('file_system')
    );
  }

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
      \Drupal::service('cohesion.utils')->errorHandler(
        $this->t('File @uri does not exist on the local filesystem although the entity @label exists.', [
          '@uri' => $entity->getFileUri(),
          '@label' => $entity->label(),
        ]),
        TRUE
      );
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
      throw new \Exception('Import did not specify a file UUID.');
    }

    if (!isset($entry['uri'])) {
      throw new \Exception('Import did not specify a file URI.');
    }

    if (!isset($entry['content'])) {
      throw new \Exception('Import did not specify file contents.');
    }

    if (!base64_decode($entry['content'])) {
      // Failed to decode (the source site didn't have a file).
      // But we're going to say this is a pass and just not create the file on
      // the dest.
      $entry['content'] = TRUE;
    }

    // File already exists (by UUID) - flag it.
    if ($entity = $this->entityRepository->loadEntityByUuid('file', $entry['uuid'])) {
      if ($entity->get('uri')->getValue()[0]['value'] !== $entry['uri'] && (strpos($entry['uri'], 'cohesion://') === FALSE || $entity->get('uri')->getValue()[0]['value'] !== str_replace('cohesion://', 'public://cohesion/', $entry['uri']))) {
        throw new \Exception('An entity with this UUID ' . $entry['uuid'] . ' already exists but the URI ' . $entry['uri'] . ' does not match.');
      }

      // See if any of the entity data has changed.
      foreach ($entry as $key => $value) {
        if ($entity->hasField($key) && $key !== 'fid' && $key !== 'created' && $key !== 'changed') {
          $entity_val = $entity->get($key)->getValue()[0];

          if ($key == 'uri') {
            if (strpos($value, 'cohesion://') !== FALSE) {
              $value = str_replace('cohesion://', 'public://cohesion/', $value);
            }
          }

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
      }
      else {
        throw new \Exception("File {$entry['uri']} does not exist on the local filesystem although the entity exists.");
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

    $uri = $entry['uri'];
    // Patch old entities using the cohesion stream wrapper.
    if (strpos($uri, 'cohesion://') !== FALSE) {
      $uri = str_replace('cohesion://', 'public://cohesion/', $uri);
    }

    // Set up the directory.
    $dirname = $this->fileSystem->dirname($uri);
    $this->fileSystem->prepareDirectory($dirname, FileSystemInterface::MODIFY_PERMISSIONS | FileSystemInterface::CREATE_DIRECTORY);

    // Only attempt to save the file if it can be decoded, otherwise just create
    // the entity.
    if ($content = base64_decode($entry['content'])) {
      if (!$this->fileSystem->saveData($content, $uri, FileSystemInterface::EXISTS_REPLACE)) {
        throw new \Exception("Unable to save file {$uri}");
      }

      // Create new entity.
      if (!$entity) {
        // $user = \Drupal::currentUser();
        $entity = File::create([
          'uri' => $uri,
          'uuid' => $entry['uuid'],
          'status' => FileInterface::STATUS_PERMANENT,
          'langcode' => $entry['langcode'],
        ]);
      }

      // Apply all the $entry data to it.
      $entity->setFilename($entry['filename']);
      $entity->setFileUri($uri);
      $entity->setMimeType($entry['filemime']);
      $entity->setSize($entry['filesize']);

      $entity->save();
    }

  }

  /**
   * {@inheritdoc}
   */
  public function getActionData($entry, $action_state, $type) {
    $action_data = parent::getActionData($entry, $action_state, $type);
    $action_data['entity_type_label'] = $this->t('File')->__toString();
    $action_data['entity_label'] = $entry['uri'];

    return $action_data;
  }

}
