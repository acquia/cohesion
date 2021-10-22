<?php

namespace Drupal\cohesion_sync\Config;

use Drupal\Component\Serialization\Exception\InvalidDataTypeException;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Serialization\Yaml;
use Drupal\file\FileInterface;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;

/**
 * Handles encoding Cohesion Config files during package exports and imports.
 */
class CohesionFileStorage extends FileStorage {

  const FILE_INDEX_FILENAME = 'sitestudio_package_files.json';

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
    if (isset($data['type']) && $data['type'] == 'cohesion_sync_package' && isset($data['settings']) && is_string($data['settings'])) {
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
   * Return a json pretty printed.
   *
   * @param string $json
   *   String containing JSON.
   *
   * @return string
   *   Pretty printed if possible or original JSON.
   */
  protected function prettyPrintJson(string $json): string {
    $decoded = json_decode($json);
    if (json_last_error() === JSON_ERROR_NONE) {
      return json_encode($decoded, JSON_PRETTY_PRINT);
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

    if ($data['type'] == 'cohesion_sync_package' && isset($data['settings']) && is_string($data['settings'])) {
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
  protected function minifyJson(string $json): string {
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
    $success = parent::deleteAll($prefix);

    $files = file_get_contents($this->directory . self::FILE_INDEX_FILENAME);
    if ($files) {
      $files = json_decode($files, TRUE);
      foreach ($files as $file) {
        $this->getFileSystem()->delete($this->directory . $file['filename']);
      }
      $this->getFileSystem()->delete($this->directory . self::FILE_INDEX_FILENAME);
    }

    return $success;
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
          $files[$file->getConfigDependencyName()] = $entry;
          $file_destination = $this->directory . $entry['filename'];

          $this->getFileSystem()->copy($file->getFileUri(), $file_destination, FileSystemInterface::EXISTS_REPLACE);
          $exported_files++;
        }
      }
    }

    if (!empty($files)) {
      // Saves files index.
      $this->getFileSystem()->saveData(json_encode($files, JSON_PRETTY_PRINT), $this->directory . self::FILE_INDEX_FILENAME, FileSystemInterface::EXISTS_REPLACE);
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
