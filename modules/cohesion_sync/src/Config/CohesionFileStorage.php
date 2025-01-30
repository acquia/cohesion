<?php

namespace Drupal\cohesion_sync\Config;

use Drupal\Component\Serialization\Exception\InvalidDataTypeException;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Component\Serialization\Yaml;
use Drupal\file\FileInterface;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;

/**
 * Handles encoding Cohesion Config files during package exports and imports.
 */
class CohesionFileStorage extends FileStorage {

  const FILE_INDEX_FILENAME = 'sitestudio_package_files.json';
  const FILE_METADATA_PREFIX = 'sitestudio_file_metadata';

  /**
   * {@inheritDoc}
   */
  public function encode($data): string {
    if (!is_array($data)) {
      return FALSE;
    }

    // Json values multilne.
    if (isset($data['json_values']) && is_string($data['json_values'])) {
      $data['json_values'] = $this->prettyPrintJson($data['json_values']);
    }

    if (isset($data['json_mapper']) && is_string($data['json_mapper'])) {
      $data['json_mapper'] = $this->prettyPrintJson($data['json_mapper']);
    }

    // Package settings multiline.
    if (isset($data['settings']) && is_string($data['settings'])) {
      $data['settings'] = $this->prettyPrintJson($data['settings']);
    }

    try {
      return SymfonyYaml::dump($data, PHP_INT_MAX, 2, SymfonyYaml::DUMP_EXCEPTION_ON_INVALID_TYPE + SymfonyYaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
    }
    catch (\Exception $e) {
      throw new InvalidDataTypeException($e->getMessage(), $e->getCode(), $e);
    }
  }

  /**
   * Return a json pretty printed if it's not empty string.
   *
   * @param string $json
   *   Unprocessed JSON string.
   *
   * @return string
   *   Processed JSON string.
   */
  public static function prettyPrintJson(string $json) {
    $decoded = json_decode($json);
    if (!empty((array) $decoded) && json_last_error() === JSON_ERROR_NONE) {
      $encoded_json = json_encode($decoded, JSON_PRETTY_PRINT);
      if ($encoded_json[-1] !== '\n') {
        $encoded_json .= "\n";
      }
      return $encoded_json;
    }

    return $json;
  }

  /**
   * {@inheritdoc}
   */
  public function decode($raw) {
    $data = Yaml::decode($raw);
    // A simple string is valid YAML for any reason.
    if (!is_array($data)) {
      return FALSE;
    }

    // Undo JSON pretty-print in Cohesion YAML files.
    $this->minifyJsonValues($data);

    return $data;
  }

  /**
   * Minifies JSON in array.
   *
   * @param array $data
   *   Array with data to modify.
   */
  protected function minifyJsonValues(array &$data) {
    if (isset($data['json_values']) && is_string($data['json_values'])) {
      $data['json_values'] = $this->minifyJson($data['json_values']);
    }

    if (isset($data['json_mapper']) && is_string($data['json_mapper'])) {
      $data['json_mapper'] = $this->minifyJson($data['json_mapper']);
    }

    if (isset($data['settings']) && is_string($data['settings'])) {
      $data['settings'] = $this->minifyJson($data['settings']);
    }
  }

  /**
   * Minifies pretty-printed JSON.
   *
   * @param string $json
   *   String containing JSON.
   *
   * @return string
   *   Minified if possible or original JSON.
   */
  public static function minifyJson(string $json): string {
    $decoded = json_decode($json);
    if (json_last_error() === JSON_ERROR_NONE) {
      return json_encode($decoded);
    }

    return $json;
  }

  /**
   * Deletes config and non-config files in destination directory.
   *
   * @param string $prefix
   *   Defaults to cohesion, as that's the default Site Studio config prefix.
   *
   * @return bool
   *   False if config deletion fails.
   */
  public function deleteAll($prefix = 'cohesion') {
    $success = parent::deleteAll();

    if (is_file($this->directory . '/' . self::FILE_INDEX_FILENAME)) {
      $files = file_get_contents($this->directory . '/' . self::FILE_INDEX_FILENAME);
      if ($files) {
        $files = json_decode($files, TRUE);
        foreach ($files as $file) {
          $this->getFileSystem()->delete($this->directory . '/' . $file['filename']);
        }
        $this->getFileSystem()->delete($this->directory . '/' . self::FILE_INDEX_FILENAME);
      }
    }

    return $success;
  }

  /**
   * Return the files info from the the file index file.
   *
   * @return array
   *   The files info.
   */
  public function getFiles() {
    $files = [];

    // Attempt to load individual metadata files.
    $extension = parent::getFileExtension();
    $metadata_files = parent::listAll(self::FILE_METADATA_PREFIX);
    foreach ($metadata_files as $metadata_file) {
      $metadata_file_content = file_get_contents($this->directory . '/' . $metadata_file . '.' . $extension);
      $files[] = Yaml::decode($metadata_file_content);
    }

    // If no individual files found and index file exists - use index file.
    if (empty($files) && file_exists($this->directory . '/' . self::FILE_INDEX_FILENAME)) {
      $files_content = file_get_contents($this->directory . '/' . self::FILE_INDEX_FILENAME);
      if ($files_content) {
        $files = json_decode($files_content, TRUE);
      }
    }

    return $files;
  }

  /**
   * {@inheritdoc}
   */
  public function listAll($prefix = '') {
    return array_diff(parent::listAll(), parent::listAll(self::FILE_METADATA_PREFIX));
  }

  /**
   * Exports non-config files based on provided file list.
   *
   * @param array $file_list
   *   File list used for export.
   *
   * @return int
   *   Count of files exported.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function exportFiles(array $file_list) {
    $files = [];
    $exported_files = 0;
    foreach ($file_list as $uuid => $type) {
      if ($type == 'file') {
        $file = $this->getEntityTypeManager()->getStorage('file')->loadByProperties(['uuid' => $uuid]);
        $file = reset($file);
        if ($file instanceof FileInterface) {
          // Builds file entry structure for index.
          $entry = $this->buildFileExportEntry($file);
          $data = SymfonyYaml::dump($entry, PHP_INT_MAX, 2, SymfonyYaml::DUMP_EXCEPTION_ON_INVALID_TYPE + SymfonyYaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
          try {
            $this->getFileSystem()->saveData($data, $this->directory . self::FILE_METADATA_PREFIX . '.' . $file->uuid() . '.' . parent::getFileExtension(), FileSystemInterface::EXISTS_REPLACE);
          }
          catch (\Exception $e) {
            throw new InvalidDataTypeException($e->getMessage(), $e->getCode(), $e);
          }
          $files[$file->getConfigDependencyName()] = $entry;
          $file_destination = $this->directory . $entry['filename'];

          $this->getFileSystem()->copy($file->getFileUri(), $file_destination, FileSystemInterface::EXISTS_REPLACE);
          $exported_files++;
        }
      }
    }

    return $exported_files;
  }

  /**
   * Returns file system service.
   *
   * @return \Drupal\Core\File\FileSystemInterface
   *   The file system service.
   */
  private function getFileSystem() {
    return \Drupal::service('file_system');
  }

  /**
   * Returns the entity manager service.
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   *   The entity manager service.
   */
  private function getEntityTypeManager() {
    return \Drupal::entityTypeManager();
  }

  /**
   * {@inheritdoc}
   *
   * @testme
   */
  protected function buildFileExportEntry($entity) {
    /** @var \Drupal\file\Entity\File $entity */
    $struct = [];

    // Get all the field values into the struct.
    foreach ($entity->getFields() as $field_key => $value) {
      if ($field_key === 'fid') {
        continue;
      }
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

    return $struct;
  }

}
