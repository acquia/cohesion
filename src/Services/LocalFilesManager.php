<?php

namespace Drupal\cohesion\Services;

use Drupal\cohesion\ExceptionLoggerTrait;
use Drupal\Component\FileSecurity\FileSecurity;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Installer\InstallerKernel;
use Drupal\Core\File\Exception\FileException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\TempStore\SharedTempStoreFactory;
use Drupal\file\FileRepositoryInterface;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class LocalFilesManager.
 *
 * Helper service used to move local files around for entity save / dx8:import.
 *
 * \Drupal::service('cohesion.local_files_manager')
 *
 * @package Drupal\cohesion\Helper
 */
class LocalFilesManager {
  use StringTranslationTrait;
  use ExceptionLoggerTrait;

  /**
   * Wether the stylesheet json is stored in the key value storage or in files.
   *
   * @var bool
   */
  private $stylesheetJsonKeyvalue;

  /**
   * @var \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   *
   * The key value store
   */
  private $keyValueStore;

  /**
   * The shared temp store service.
   *
   * @var \Drupal\Core\TempStore\SharedTempStoreFactory
   */
  protected $sharedTempStore;

  /**
   * The cohesion utils helper.
   *
   * @var \Drupal\cohesion\Services\CohesionUtils
   */
  protected $cohesionUtils;

  /**
   * Drupal File Repository service.
   *
   * @var \Drupal\file\FileRepositoryInterface
   */
  protected $fileRepository;

  /**
   * Cohesion channel logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * LocalFilesManager constructor.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   * @param \Drupal\Core\KeyValueStore\KeyValueFactoryInterface $keyValueFactory
   * @param \Drupal\Core\TempStore\SharedTempStoreFactory $shared_store_factory
   *   The tempstore service.
   * @param \Drupal\cohesion\Services\CohesionUtils $cohesion_utils
   * @param \Symfony\Component\HttpFoundation\Session\Session $session
   */
  public function __construct(
    TranslationInterface $stringTranslation,
    ConfigFactoryInterface $configFactory,
    KeyValueFactoryInterface $keyValueFactory,
    SharedTempStoreFactory $shared_store_factory,
    CohesionUtils $cohesion_utils,
    Session $session,
    FileRepositoryInterface $fileRepository,
    LoggerChannelFactoryInterface $channelFactory,
  ) {
    $this->stringTranslation = $stringTranslation;
    $this->stylesheetJsonKeyvalue = $configFactory->get('cohesion.settings')->get('stylesheet_json_storage_keyvalue');
    $this->keyValueStore = $keyValueFactory->get('sitestudio');
    // When cohesion module is installed during Site Installation, it redirects
    // user to access denied page. During Site installation, when the request is
    // made to get sharedTempStoreObject without passing userId, it creates a
    // SharedTempStore for the anonymous user
    // (i.e some random generated identifier).
    // (@see \Drupal\Core\TempStore\SharedTempStoreFactory::get)
    // But after Site Installation is
    // completed, new drupal session is created for the admin user.
    if (InstallerKernel::installationAttempted()) {
      $this->sharedTempStore = $shared_store_factory->get('sitestudio', $session->getId());
    } else {
      $this->sharedTempStore = $shared_store_factory->get('sitestudio');
    }
    $this->cohesionUtils = $cohesion_utils;
    $this->fileRepository = $fileRepository;
    $this->logger = $channelFactory->get('cohesion');
  }

  /**
   * Flush the css dummy query string parameter (forces browser reload).
   */
  public function refreshCaches() {
    \Drupal::service('asset.css.collection_optimizer')->deleteAll();
    \Drupal::service('asset.js.collection_optimizer')->deleteAll();

    // Change the js/css cache buster.
    \Drupal::service('asset.query_string')->reset();
    \Drupal::service('cache.data')->deleteAll();
  }

  /**
   * Copy the live stylesheet.json to temporary:// so styles don't get wiped
   * when re-importing.
   */
  public function liveToTemp() {

    if ($this->stylesheetJsonKeyvalue === TRUE) {
      // If the store is set to key value move the stylesheet jsons from
      // the main key value to the private storage.
      $keyvalue_store_stylesheet_jsons = $this->keyValueStore->get($this->getStylesheetJsonCollectionName(TRUE));
      if (!empty($keyvalue_store_stylesheet_jsons)) {
        $this->sharedTempStore->set($this->getStylesheetJsonCollectionName(), $keyvalue_store_stylesheet_jsons);
      }
    }
    else {
      // If the store as been set to files, loop over each enabled theme
      // and move the files to the main site studio folder.
      foreach ($this->cohesionUtils->getCohesionEnabledThemes() as $theme_info) {
        $from = $this->getStyleSheetFilename('json', $theme_info->getName(), TRUE);
        $to = $this->getStyleSheetFilename('json', $theme_info->getName());
        if (file_exists($from)) {
          try {
            \Drupal::service('file_system')->move($from, $to, FileSystemInterface::EXISTS_REPLACE);
          }
          catch (FileException $e) {
          }
        }
      }
    }
  }

  /**
   * Copy the *.tmp.* files to live.
   */
  public function tempToLive() {

    if ($this->stylesheetJsonKeyvalue === TRUE) {
      // If the store is set to key value move the stylesheet jsons from
      // the private to the main key value storage.
      $private_stylesheet_jsons = $this->sharedTempStore->get($this->getStylesheetJsonCollectionName());
      if (!empty($private_stylesheet_jsons)) {
        $this->keyValueStore->set($this->getStylesheetJsonCollectionName(TRUE), $private_stylesheet_jsons);
      }
    }
    else {
      // If the store as been set to files, loop over each enabled theme
      // and move the files to the main site studio folder.
      foreach ($this->cohesionUtils->getCohesionEnabledThemes() as $theme_info) {
        $from = $this->getStyleSheetFilename('json', $theme_info->getName());
        $to = $this->getStyleSheetFilename('json', $theme_info->getName(), TRUE);
        if (file_exists($from)) {
          try {
            \Drupal::service('file_system')->move($from, $to, FileSystemInterface::EXISTS_REPLACE);
          }
          catch (FileException $e) {
          }
        }
      }
    }

    foreach ($this->cohesionUtils->getCohesionEnabledThemes() as $theme_info) {

      $styles = ['base', 'theme', 'grid', 'icons', 'prefixed', 'preview'];

      foreach ($styles as $style) {
        $from = $this->getStyleSheetFilename($style, $theme_info->getName());
        $to = $this->getStyleSheetFilename($style, $theme_info->getName(), TRUE);
        if (file_exists($from)) {
          // Copy the file.
          try {
            \Drupal::service('file_system')->move($from, $to, FileSystemInterface::EXISTS_REPLACE);
          }
          catch (FileException $e) {
          }

        }
      }
    }

    // Clean up.
    $this->refreshCaches();
  }

  /**
   * Return different filenames depending if the user is rebuilding.
   *
   * @param string $type
   * @param string $theme_id
   * @param bool $force_clean_filename
   *
   * @return string
   */
  public function getStyleSheetFilename($type, $themeId = '', $force_clean_filename = FALSE) {

    $themeFileName = str_replace('_', '-', $themeId);
    $filename = '';

    $cohesionUris = [
      'json' => "{$themeFileName}-stylesheet.json",
      'base' => "base/{$themeFileName}-stylesheet.min.css",
      'theme' => "theme/{$themeFileName}-stylesheet.min.css",
      'grid' => "cohesion-responsive-grid-settings.css",
      'icons' => "cohesion-icon-libraries.css",
      'prefixed' => "prefixed/cohesion-prefixed-ckeditor-stylesheet.css",
      'preview' => "preview/cohesion-custom-style-stylesheet.css",
    ];

    $tmpUris = [
      'json' => "{$themeFileName}-stylesheet.json",
      'base' => "{$themeFileName}-base-stylesheet.min.css",
      'theme' => "{$themeFileName}-theme-stylesheet.min.css",
      'grid' => "cohesion-responsive-grid-settings.css",
      'icons' => "cohesion-icon-libraries.css",
      'prefixed' => "cohesion-prefixed-ckeditor-stylesheet.css",
      'preview' => "cohesion-custom-style-stylesheet.css",
    ];

    if (array_key_exists($type, $cohesionUris) && array_key_exists($type, $tmpUris)) {
      $running_dx8_batch = &drupal_static('running_dx8_batch');
      if (!$running_dx8_batch || $force_clean_filename) {
        $filename .= COHESION_CSS_PATH . '/' . $cohesionUris[$type];
      }
      else {
        $filename .= $this->scratchDirectory() . '/' . $tmpUris[$type];
      }
    }
    return $filename;
  }

  /**
   * Return a temp directory inside Site Studio directory
   * This is used because of unpredictable behavior of the /tmp diretory on
   * Pantheon and Acquia hosting.
   *
   * @return string
   */
  public function scratchDirectory() {
    $cohesion_scratch_path = COHESION_FILESYSTEM_URI . 'scratch';

    // If the scratch directory doesn't exist, create it.
    if (!file_exists($cohesion_scratch_path)) {
      // Create the directory.
      \Drupal::service('file_system')->mkdir($cohesion_scratch_path, 0777, FALSE);

      // Add a .htaccess file.
      try {
        \Drupal::service('file_system')->saveData(FileSecurity::htaccessLines(TRUE), $cohesion_scratch_path . '/.htaccess', FileSystemInterface::EXISTS_REPLACE);
      }
      catch (\Throwable $e) {
        \Drupal::service('cohesion.utils')->errorHandler('Unable to secure directory: ' . $cohesion_scratch_path);
      }
    }

    return $cohesion_scratch_path;
  }

  /**
   * Clean the scratch directory ahead of a dx8:import or dx8:rebuild.
   */
  public function resetScratchDirectory() {
    // Delete the directory.
    if (file_exists($this->scratchDirectory())) {
      \Drupal::service('file_system')->deleteRecursive($this->scratchDirectory());
    }

    // Recreate it blank.
    $this->scratchDirectory();
  }

  /**
   * Delete a file by URI checking if it's a managed file or not first.
   *
   * @param $uri
   *   - the uri of the file
   *
   * @return bool
   */
  public function deleteFileByURI($uri) {
    $files = \Drupal::entityTypeManager()->getStorage('file')->loadByProperties(['uri' => $uri]);
    if ($file = reset($files)) {
      return $file->delete();
    }
    else {
      try {
        return \Drupal::service('file_system')->delete($uri);
      }
      catch (FileException $e) {
        return FALSE;
      }
    }
  }

  /**
   * This recursively scans a decoded JSON object for temporary:// files and
   * moves them to the Site Studio directory.
   * It patches the object paths with the new URIs.
   *
   * @param $obj
   */
  public function moveTemporaryFiles(&$obj) {
    if (is_object($obj)) {
      foreach ($obj as $property => $value) {
        $file = $this->resolveTemporaryFile($value);
        if ($file) {
          $obj = $file;
        }
        else {
          $this->moveTemporaryFiles($obj->$property);
        }
      }
    }
    else {
      if (is_array($obj)) {
        foreach ($obj as $key => $value) {
          $file = $this->resolveTemporaryFile($value);
          if ($file) {
            $obj = $file;
          }
          else {
            $this->moveTemporaryFiles($obj[$key]);
          }
        }
      }
    }
  }

  /**
   * This scans a variable for a temporary file path, if found it creates a
   * permanent file in Site Studio directory
   * Note, this does NOT set the core file usage because the FileUsage plugin
   * does this on entity postSave().
   *
   * @param $tmp_file
   *
   * @return bool|object
   */
  private function resolveTemporaryFile($tmp_file) {
    $temp_folder = $this->scratchDirectory();
    $tmp_pattern = "#" . $temp_folder . "/[a-zA-Z0-9-_+]+\.[a-zA-Z0-9-_+]+#";
    if (is_string($tmp_file) && preg_match($tmp_pattern, $tmp_file)) {
      $tmp_file = str_replace('"', '', $tmp_file);
      $file_get = file_get_contents($tmp_file);
      $filename = basename($tmp_file);
      $filename = preg_replace("/[^a-zA-Z0-9-_.]/", "", basename($filename));
      try {
        $file = $this->fileRepository->writeData($file_get, 'public://cohesion/' . $filename);

        @ unlink($tmp_file);
        $return_object = new \stdClass();
        $return_object->type = 'file';
        $return_object->uri = $file->getFileUri();
        $return_object->uuid = $file->uuid();

        return $return_object;
      }
      catch (\Exception $exception) {
        $this->logException($exception);
      }
    }
    return FALSE;
  }

  /**
   * Return the store that should be used depending on the batch running.
   *
   * @return \Drupal\Core\KeyValueStore\KeyValueStoreInterface|\Drupal\Core\TempStore\PrivateTempStore|\Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  private function getKeyValueStore() {
    $running_dx8_batch = &drupal_static('running_dx8_batch');
    if ($running_dx8_batch) {
      return $this->sharedTempStore;
    }
    else {
      return $this->keyValueStore;
    }
  }

  /**
   *
   */
  private function getStylesheetJsonCollectionName($permanent = FALSE) {
    $name = 'sitestudio_stylesheet_json';
    $batch =& batch_get();
    if (isset($batch['id']) && $this->getKeyValueStore() === $this->sharedTempStore && $permanent === FALSE) {
      $name = $batch['id'] . ':' . $name;
    }

    return $name;
  }

  /**
   * Get the stylesheet.json for a specific theme from
   * the storage set in the config.
   *
   * @param $theme_name
   *
   * @return false|mixed|string
   */
  public function getStyleSheetJson($theme_name) {

    $original_css_contents = '';

    // If site studio is set to store the stysheet json to the key value store
    // only get it from it. Otherwise get it from the file system.
    if ($this->stylesheetJsonKeyvalue === TRUE) {
      $stylesheet_json_value = $this->getKeyValueStore()->get($this->getStylesheetJsonCollectionName());
      if (isset($stylesheet_json_value[$theme_name]['json'])) {
        // Return the stylesheet json for the theme.
        return $stylesheet_json_value[$theme_name]['json'];
      }
    }
    else {
      $original_css_path = $this->getStyleSheetFilename('json', $theme_name);
      if (file_exists($original_css_path)) {
        $content = file_get_contents($original_css_path);
        if (!$content) {
          $this->cohesionUtils->errorHandler('File system reported that "' . $original_css_path . '" exists but was unable to load it.');
        }
        else {
          $original_css_contents = $content;
        }

      }
    }

    return $original_css_contents;
  }

  /**
   * Set the stylesheet.json from the storage set in the config.
   *
   * @param $stylesheet_json_content
   * @param $theme_id
   */
  public function setStyleSheetJson($stylesheet_json_content, $theme_id) {
    if ($this->stylesheetJsonKeyvalue === TRUE) {
      $stylesheet_json_value = $this->getKeyValueStore()->get($this->getStylesheetJsonCollectionName());
      $stylesheet_json_value[$theme_id] = [
        'json' => $stylesheet_json_content,
        'timestamp' => \Drupal::time()->getCurrentTime(),
      ];
      $this->getKeyValueStore()->set($this->getStylesheetJsonCollectionName(), $stylesheet_json_value);
    }
    else {
      $stylesheet_json_path = $this->getStyleSheetFilename('json', $theme_id);
      \Drupal::service('file_system')->saveData($stylesheet_json_content, $stylesheet_json_path, FileSystemInterface::EXISTS_REPLACE);
    }
  }

  /**
   * Get the (sub second) timestamp of last theme stylesheet that has last been
   * update.
   *
   * @return bool|int
   */
  public function getStylesheetTimestamp() {
    $stylesheet_timestamp = 0;

    if ($this->stylesheetJsonKeyvalue === TRUE) {
      $stylesheet_json_value = $this->getKeyValueStore()->get($this->getStylesheetJsonCollectionName());
      if (!empty($stylesheet_json_value)) {
        foreach ($stylesheet_json_value as $json_values) {
          if (isset($stylesheet_json_value['timestamp']) && $stylesheet_json_value['timestamp'] > $stylesheet_timestamp) {
            $stylesheet_timestamp = $stylesheet_json_value['timestamp'];
          }
        }
      }
    }
    else {
      foreach ($this->cohesionUtils->getCohesionEnabledThemes() as $theme_info) {
        $originalCssPath = $this->getStyleSheetFilename('json', $theme_info->getName());

        clearstatcache($originalCssPath);
        if (file_exists($originalCssPath) && filemtime($originalCssPath) > $stylesheet_timestamp) {
          $stylesheet_timestamp = filemtime($originalCssPath);
        }
      }
    }

    return $stylesheet_timestamp;
  }

  /**
   * Retrieve the keyValue for sitestudio_stylesheet_json.
   *
   * @return mixed
   */
  public static function getKeyValueStylesheetJson() {
    return \Drupal::keyValue("sitestudio")->get("sitestudio_stylesheet_json");
  }

}
