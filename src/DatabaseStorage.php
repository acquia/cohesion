<?php

namespace Drupal\cohesion;

use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Twig\Error\LoaderError;
use Twig\Source;

/**
 * Defines a service to store and load templates in the database.
 */
final class DatabaseStorage implements TemplateStorageInterface {

  /**
   * The key-value storage backend.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   */
  private $storage;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  private $fileSystem;

  /**
   * DatabaseStorage constructor.
   *
   * @param \Drupal\Core\KeyValueStore\KeyValueFactoryInterface $key_value_factory
   *   The key-value storage factory service.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   */
  public function __construct(KeyValueFactoryInterface $key_value_factory, FileSystemInterface $file_system) {
    $this->storage = $key_value_factory->get('cohesion.templates');
    $this->fileSystem = $file_system;
  }

  /**
   * Transforms a template name into a storage key.
   *
   * @param string $name
   *   The template name.
   *
   * @return string
   *   A storage key that can be passed to the backend.
   */
  private function getKey(string $name) : string {
    return $this->fileSystem->basename($name);
  }

  /**
   * Loads a template or dies trying.
   *
   * @param string $name
   *   The template name.
   *
   * @return array
   *   A tuple containing the Twig source code of the template, and the time
   *   stamp when it was last modified.
   *
   * @throws \Twig\Error\LoaderError
   *   If the template does not exist, or is not a valid array.
   */
  private function loadOrDie(string $name) : array {
    $key = $this->getKey($name);
    $template = $this->storage->get($key);

    if ($template && is_array($template)) {
      return $template;
    }
    else {
      throw new LoaderError("Unknown or invalid template: $name");
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceContext($name) {
    [$template] = $this->loadOrDie($name);
    return new Source($template, $name);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheKey($name) {
    return "$name:" . $this->getSourceContext($name)->getCode();
  }

  /**
   * {@inheritdoc}
   */
  public function isFresh($name, $time) {
    [, $changed_at] = $this->loadOrDie($name);
    return $changed_at < $time;
  }

  /**
   * {@inheritdoc}
   */
  public function exists($name) {
    $key = $this->getKey($name);
    return $this->storage->has($key);
  }

  /**
   * {@inheritdoc}
   */
  public function save(string $name, string $content, int $time = NULL) : void {
    $this->storage->set($this->getKey($name), [
      $content,
      $time ?? time(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function listAll() : array {
    return array_keys($this->storage->getAll());
  }

  /**
   * Implements LoaderInterface::getSource() for Twig 1.x compatibility.
   */
  public function getSource($name) {
    return $this->getSourceContext($name)->getCode();
  }

}
