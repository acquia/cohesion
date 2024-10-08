<?php

namespace Drupal\cohesion\TemplateStorage;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\UseCacheBackendTrait;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Twig\Error\LoaderError;
use Twig\Source;

/**
 * Defines a template storage service that stores templates in the database.
 */
final class KeyValueStorage extends TemplateStorageBase {

  use UseCacheBackendTrait;

  /**
   * The key-value storage backend.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueStoreInterface
   */
  private $keyValue;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  private $fileSystem;

  /**
   * KeyValueStorage constructor.
   *
   * @param \Drupal\Core\KeyValueStore\KeyValueFactoryInterface $key_value_factory
   *   The key-value factory service.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   (optional) The cache backend.
   */
  public function __construct(
    KeyValueFactoryInterface $key_value_factory,
    FileSystemInterface $file_system,
    ?CacheBackendInterface $cache_backend = NULL,
  ) {
    $this->keyValue = $key_value_factory->get('cohesion.templates');
    $this->fileSystem = $file_system;
    $this->cacheBackend = $cache_backend;
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceContext($name): Source {
    [$content] = $this->load($name);
    return new Source($content, $name);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheKey($name): string {
    [$content] = $this->load($name);
    return "$name:$content";
  }

  /**
   * {@inheritdoc}
   */
  public function isFresh($name, $time): bool {
    [, $changed_at] = $this->load($name);
    return $changed_at < $time;
  }

  /**
   * {@inheritdoc}
   */
  public function exists($name) {
    // To avoid needlessly querying the database, we only do the check if this
    // is a Cohesion template.
    if (strpos($name, self::TEMPLATE_PREFIX)) {
      $key = $this->getKey($name);
      return $this->keyValue->has($key);
    }
    else {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function delete(string $name) {
    $this->keyValue->delete($this->getKey($name));
  }

  /**
   * {@inheritdoc}
   */
  public function listAll() : array {
    $templates = [];

    foreach ($this->keyValue->getAll() as $key => $template) {
      if (strpos($key, 'temporary::') === FALSE) {
        $templates[] = reset($template);
      }
    }
    return $templates;
  }

  /**
   * {@inheritdoc}
   */
  public function save(string $name, string $content, ?int $time = NULL) {
    $key = $this->getKey($name);

    $this->keyValue->set("temporary::$key", [
      $name,
      $content,
      $time ?: time(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function commit() {
    $templates = array_keys($this->keyValue->getAll());

    foreach ($templates as $key) {
      if (strpos($key, 'temporary::') === 0) {
        $this->keyValue->set(substr($key, 11), $this->keyValue->get($key));
        $this->keyValue->delete($key);
      }
    }
  }

  /**
   * Loads a template.
   *
   * @param string $name
   *   The name of the template to load (normally a real or imaginary file path,
   *   relative to the Drupal root).
   *
   * @return array
   *   A tuple containing, in order:
   *   - The Twig source code of the template.
   *   - The time stamp at which the template was last modified.
   *
   * @throws \Twig\Error\LoaderError
   *   If the template does not exist, is not an array, or doesn't have the
   *   expected number of items.
   */
  private function load(string $name) : array {
    $key = $this->getKey($name);

    $cached = $this->cacheGet($key);
    if ($cached) {
      $template = $cached->data;
    }
    else {
      $template = $this->keyValue->get($key);

      if ($template && is_array($template) && count($template) === 3) {
        $template = array_slice($template, 1);
        $this->cacheSet($key, $template);
      }
      else {
        throw new LoaderError("Invalid or unknown template: $name");
      }
    }
    return $template;
  }

  /**
   * Converts a template name to a storage key.
   *
   * If the template name is very long, it might cause an exception when we try
   * to store it in the key-value store (i.e., it's too long for its database
   * column). This method converts the template name to a key which can be
   * stored safely.
   *
   * @param string $name
   *   The name of the template.
   *
   * @return string
   *   A storage-level identifier that can be passed to the key-value store.
   */
  private function getKey(string $name) : string {
    return hash('sha256', $this->fileSystem->basename($name));
  }

}
