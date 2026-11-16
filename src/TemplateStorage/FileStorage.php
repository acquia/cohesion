<?php

namespace Drupal\cohesion\TemplateStorage;

use Drupal\cohesion\Services\CohesionUtils;
use Drupal\cohesion\Services\LocalFilesManager;
use Drupal\Core\File\Exception\FileException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Template\Loader\FilesystemLoader;
use Twig\Source;

/**
 * Defines a template storage service that stores templates in the file system.
 */
final class FileStorage extends TemplateStorageBase {

  use MessengerTrait;
  use StringTranslationTrait;

  /**
   * The decorated file system Twig loader.
   *
   * @var \Drupal\Core\Template\Loader\FilesystemLoader
   */
  private $fileSystemLoader;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  private $fileSystem;

  /**
   * The key-value store for temporary templates.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   */
  private $temporaryStorage;

  /**
   * The local file manager service.
   *
   * @var \Drupal\cohesion\Services\LocalFilesManager
   */
  private $localFileManager;

  /**
   * The utilities service. Water, electricity, etc.
   *
   * @var \Drupal\cohesion\Services\CohesionUtils
   */
  private $utils;

  /**
   * FileStorage constructor.
   *
   * @param \Drupal\Core\Template\Loader\FilesystemLoader $file_system_loader
   *   The decorated Twig file system loader.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   * @param \Drupal\Core\KeyValueStore\KeyValueFactoryInterface $key_value_factory
   *   The key-value store factory service.
   * @param \Drupal\cohesion\Services\LocalFilesManager $local_file_manager
   *   The local file manager service.
   * @param \Drupal\cohesion\Services\CohesionUtils $utils
   *   The utilities service.
   */
  public function __construct(FilesystemLoader $file_system_loader, FileSystemInterface $file_system, KeyValueFactoryInterface $key_value_factory, LocalFilesManager $local_file_manager, CohesionUtils $utils) {
    $this->fileSystemLoader = $file_system_loader;
    $this->fileSystem = $file_system;
    $this->temporaryStorage = $key_value_factory->get('cohesion.temporary_template');
    $this->localFileManager = $local_file_manager;
    $this->utils = $utils;
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceContext($name): Source {
    return $this->fileSystemLoader->getSourceContext($name);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheKey($name): string {
    return $this->fileSystemLoader->getCacheKey($name);
  }

  /**
   * {@inheritdoc}
   */
  public function isFresh($name, $time): bool {
    return $this->fileSystemLoader->isFresh($name, $time);
  }

  /**
   * {@inheritdoc}
   */
  public function exists($name) {
    return $this->fileSystemLoader->exists($name);
  }

  public function delete(string $name) {
    $theme_filename = COHESION_TEMPLATE_PATH . '/' . $name;
    if (file_exists($theme_filename)) {
      $this->fileSystem->delete($theme_filename);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function listAll() : array {
    $dir = $this->ensureTemplatesDirectoryExists();
    $ext = '.html.twig';
    $templates = [];

    $files = $this->fileSystem->scanDirectory($dir, '/' . preg_quote($ext) . '$/');
    foreach ($files as $file) {
      $templates[] = $this->fileSystem->basename($file->filename, $ext);
    }
    return $templates;
  }

  /**
   * {@inheritdoc}
   */
  public function save(string $name, string $content, ?int $time = NULL) {
    // Build the path to the temporary file.
    $temporary_directory = $this->localFileManager->scratchDirectory();
    $temp_file = $temporary_directory . '/' . $name;

    if (file_put_contents($temp_file, $content) !== FALSE) {
      // Register temporary template files.
      $templates = $this->getTemporaryTemplates();
      $templates[] = $temp_file;
      $this->setTemporaryTemplates($templates);
    }
    else {
      $this->utils->errorHandler("Unable to create template file: $temp_file");
    }
  }

  /**
   * {@inheritdoc}
   */
  public function commit() {
    $template_path = $this->ensureTemplatesDirectoryExists();

    foreach ($this->getTemporaryTemplates() as $temp_template) {
      $template_file = $template_path . '/' . basename($temp_template);
      // Skip this file if it doesn't, y'know, exist.
      if (!file_exists($temp_template)) {
        continue;
      }

      // Copy the file and add to the list to be saved for later.
      try {
        $this->fileSystem->move($temp_template, $template_file, FileSystemInterface::EXISTS_REPLACE);
      }
      catch (FileException $e) {
        $message = $this->t('Error moving @file: @error', [
          '@file' => $temp_template,
          '@error' => $e->getMessage(),
        ]);
        $this->messenger()->addError($message);
      }
    }
    // Reset temporary template list.
    $this->setTemporaryTemplates([]);
  }

  /**
   * Returns all temporary template URIs.
   *
   * @return string[]
   *   An array of temporary template URIs.
   */
  private function getTemporaryTemplates() : array {
    return $this->temporaryStorage->get('temporary_templates', []);
  }

  /**
   * Updates the list of temporary template URIs.
   *
   * @param array $templates
   *   An array of temporary template URIs.
   */
  private function setTemporaryTemplates(array $templates) {
    $this->temporaryStorage->set('temporary_templates', $templates);
  }

  /**
   * Ensures that the Site Studio templates directory exists.
   *
   * @return string
   *   The URI of the templates directory.
   */
  private function ensureTemplatesDirectoryExists() : string {
    // @todo Eventually, make this a constant of this class, or a parameter
    // of the constructor, so that templates can theoretically be stored in
    // other places, like private files.
    $dir = COHESION_TEMPLATE_PATH;

    if (!file_exists($dir)) {
      $this->fileSystem->mkdir($dir, 0777, TRUE);
    }

    return $dir;
  }

}
