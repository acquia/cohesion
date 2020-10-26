<?php

namespace Drupal\cohesion;

use Drupal\Core\File\Exception\FileException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Twig\Loader\LoaderInterface;

/**
 * Defines a backend to store templates in the public files directory.
 */
final class PublicFileStorage implements TemplateStorageInterface {

  use LoggerChannelTrait;

  /**
   * The decorated Twig loader service.
   *
   * @var \Twig\Loader\LoaderInterface
   */
  private $twigLoader;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  private $fileSystem;

  /**
   * PublicFileStorage constructor.
   *
   * @param \Twig\Loader\LoaderInterface $twig_loader
   *   The decorated Twig loader service.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   */
  public function __construct(LoaderInterface $twig_loader, FileSystemInterface $file_system) {
    $this->twigLoader = $twig_loader;
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceContext($name) {
    return $this->twigLoader->getSourceContext($name);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheKey($name) {
    return $this->twigLoader->getCacheKey($name);
  }

  /**
   * {@inheritdoc}
   */
  public function isFresh($name, $time) {
    return $this->twigLoader->isFresh($name, $time);
  }

  /**
   * {@inheritdoc}
   */
  public function exists($name) {
    return $this->twigLoader->exists($name);
  }

  /**
   * {@inheritdoc}
   */
  public function save(string $name, string $content, int $time = NULL) : void {
    $running_dx8_batch = &drupal_static('running_dx8_batch');
    if (!$running_dx8_batch) {
      $this->saveTemplate($content, $name);
    }
    else {
      $this->saveTemporaryTemplate($content, $name);
    }
  }

  /**
   * Save a .twig template that has been compiled by the API.
   *
   * @param $content
   * @param $filename
   *
   * @throws \Exception
   */
  private function saveTemplate($content, $filename) {
    // Create the template twig directory if needed.
    if (!file_exists(COHESION_TEMPLATE_PATH)) {
      $this->fileSystem->mkdir(COHESION_TEMPLATE_PATH, 0777, FALSE);
    }

    // Save the compiled twig file.
    $template_file = COHESION_TEMPLATE_PATH . '/' . $filename;

    try {
      $this->fileSystem->saveData($content, $template_file, FileSystemInterface::EXISTS_REPLACE);
      $this->getLogger('cohesion_templates')->notice("Template created: @template_file", [
        '@template_file' => $template_file,
      ]);
    }
    catch (FileException $e) {
      \Drupal::service('cohesion.utils')->errorHandler('Unable to create template file: ' . $template_file . $e->getMessage());
    }
  }

  /**
   * When rebuilding, .twig templates are stored temporarily, so rebuilds that
   * fail do not result in a broken looking site.
   *
   * @param null $data
   * @param null $filename
   *
   * @throws \Exception
   */
  private function saveTemporaryTemplate($data = NULL, $filename = NULL) {
    if (!$filename) {
      return NULL;
    }

    // Build the path to the temporary file.
    $temporary_directory = \Drupal::service('cohesion.local_files_manager')->scratchDirectory();
    $temp_file = $temporary_directory . '/' . $filename;

    if (file_put_contents($temp_file, $data) !== FALSE) {
      // Register temporary template files.
      $templates = \Drupal::keyValue('cohesion.temporary_template')->get('temporary_templates', []);
      $templates[] = $temp_file;
      \Drupal::keyValue('cohesion.temporary_template')->set('temporary_templates', $templates);
    }
    else {
      \Drupal::service('cohesion.utils')->errorHandler('Unable to create template file: ' . $temp_file);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function listAll() : array {
    // Get real path to templates and extract relative path for theme hooks.
    // Note: The theme registry expects template paths relative to DRUPAL_ROOT.
    $template_path = static::getTemplatePath();
    if (empty($template_path)) {
      return [];
    }

    if (is_dir($template_path)) {
      $template_files = $this->fileSystem->scanDirectory($template_path, '/' . preg_quote('.html.twig') . '$/');
    }
    else {
      $template_files = [];
    }

    $map = function ($file) {
      return $file->filename;
    };
    return array_map($map, $template_files);
  }

  /**
   * Returns the file system path to the Cohesion templates, if available.
   *
   * @return string|null
   *   The file system path to the Cohesion templates, relative to the Drupal
   *   root, or NULL if the Cohesion stream wrapper is unavailable.
   */
  public static function getTemplatePath() : ?string {
    /** @var \Drupal\Core\StreamWrapper\LocalStream $wrapper */
    $wrapper = \Drupal::service('stream_wrapper_manager')
      ->getViaUri(COHESION_TEMPLATE_PATH);

    if ($wrapper) {
      return $wrapper->basePath() . '/cohesion/templates';
    }
    else {
      \Drupal::logger('cohesion')
        ->error(t('Unable to get stream wrapper for Site Studio templates path: @uri', ['@uri' => COHESION_TEMPLATE_PATH]));

      return NULL;
    }
  }

  /**
   * Implements LoaderInterface::getSource() for Twig 1.x compatibility.
   */
  public function getSource($name) {
    if (method_exists($this->twigLoader, 'getSource')) {
      return $this->twigLoader->getSource($name);
    }
    else {
      $class = get_class($this->twigLoader);
      throw new \BadMethodCallException("Decorated loader $class does not implement LoaderInterface::getSource().");
    }
  }

}
