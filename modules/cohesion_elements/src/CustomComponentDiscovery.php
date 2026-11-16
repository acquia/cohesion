<?php

namespace Drupal\cohesion_elements;

use Drupal\cohesion_elements\Exception\CustomComponentMissingPropertiesException;
use Drupal\cohesion_elements\Exception\FileUnreadableException;
use Drupal\cohesion_elements\Exception\FileNotFoundException;
use Drupal\Core\Site\Settings;
use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Psr\Log\LoggerInterface;

/**
 * Discovery service for custom components provided by modules and themes.
 *
 * This is heavily influenced by 'Components' module, since we also need to
 * scan subdirectories to look for yml files.
 *
 * Modules can define custom components in a MACHINE_NAME.custom_component.yml
 * file contained in the 'custom_component' subfolder in the extension's base
 * directory.
 *
 * See the example_custom_component module for detailed examples.
 */
class CustomComponentDiscovery implements CustomComponentDiscoveryInterface {

  const CUSTOM_COMPONENT_POSTFIX = '.custom_component.yml';
  const DEFAULT_CUSTOM_COMPONENT_DIR = '/custom_components';
  const REQUIRED_PROPERTIES = ['name', 'category'];

  /**
   * The app root for the current operation.
   *
   * @var string
   */
  protected $root;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;


  /**
   * Regular expression to match PHP function names.
   *
   * @see http://php.net/manual/functions.user-defined.php
   */
  const PHP_FUNCT_PATTERN = '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\\x7f-\\xff]*$/';

  /**
   * Defines the default component configuration object.
   *
   * @var array
   */
  protected $defaults = [
    // Human readable label for custom component.
    'name' => '',
    // Description of the custom component for the admin UI.
    'description' => '',
    // JS & CSS files for inclusion as a library for this custom component.
    'js' => [],
    'css' => [],
    // The TWIG template to use to render the custom component.
    'template' => NULL,
    // The HTML template to use to render the custom component.
    'html' => NULL,
    // Set the cache timeout for this custom component if needed.
    'cache' => ['max-age' => 0],
    // Define the library dependencies that this custom component needs.
    'dependencies' => [],
  ];

  /**
   * CustomComponentDiscovery constructor.
   *
   * @param string $root
   *   The root web directory of the Drupal installation.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   */
  public function __construct($root, ModuleHandlerInterface $module_handler, ThemeHandlerInterface $theme_handler, LoggerInterface $logger) {
    $this->root = $root;
    $this->moduleHandler = $module_handler;
    $this->themeHandler = $theme_handler;
    $this->logger = $logger;
  }

  /**
   * Generates an array of custom component data.
   *
   * This is the ONLY public method available via this service.
   *
   * @return array
   *   A multidimensional array keyed by the custom component name.
   */
  public function getComponents() {
    // Set up the default array of custom components.
    $components = [];
    // Find custom component yml files. This provides an array of file paths
    // keyed by the machine_name (filename) of the custom component.
    $component_files = $this->scan();

    // Process each custom component file and create the list of custom
    // components.
    foreach ($component_files as $name => $filepath) {
      // If the yml is valid and has data, then process it.
      if ($file_data = $this->parse($filepath)) {
        // Set the defaults and add the custom component data from the file.
        $component_data = array_merge($this->defaults, $file_data);
        // Create the custom component path and subpath (relative to the root).
        $path = str_replace($name . self::CUSTOM_COMPONENT_POSTFIX, '', $filepath);
        $subpath = str_replace($this->root, '', $path);
        // Build the custom components return array.
        $components[$name] = $component_data;
        $components[$name]['machine_name'] = $name;
        $components[$name]['path'] = $path;
        $components[$name]['subpath'] = $subpath;
      }
    }
    // Allow the custom component info to be altered.
    $this->moduleHandler->alter('custom_component_info', $components);

    return $components;
  }

  /**
   * Discovers all the custom component Yaml files in the system.
   *
   * This will look in all enabled modules and themes for a "custom_components"
   *  subdirectory. Then it will check that directory and all subdirectories
   *  for custom component Yaml files.
   *
   * The information is returned in an associative array, keyed by the custom
   * component name (without .component.yml extension).
   *
   * @return array
   *   Array of file paths for *.custom_component.yml files.
   */
  protected function scan() {
    $filepaths = [];
    // Scan in the proper directories for custom components.
    foreach ($this->getSearchDirs() as $dir) {
      // Check for any custom component yml files, and store their filepath.
      if ($component_files = $this->scanDirectory($dir)) {
        $filepaths += $component_files;
      }
    }

    // Process and return the list of custom components keyed by machine name.
    return $filepaths;
  }

  /**
   * Generate an array of directories to search in for custom components.
   *
   * We want to only look in the "custom_components" folder of any activated
   * modules or themes.
   *
   * @return array
   *   Array of paths with a "custom_components" directory in the root.
   */
  protected function getSearchDirs() {
    $dirs = [];
    // Create a list of parent directories to search. Look in all active
    // modules and themes. Look in the root as well.
    $parents = array_merge($this->moduleHandler->getModuleDirectories(), $this->themeHandler->getThemeDirectories());
    $parents['root'] = $this->root;
    // Loop through the possible parent directories.
    foreach ($parents as $path) {
      // Only return paths that exist.
      if (($component_dir = $path . self::DEFAULT_CUSTOM_COMPONENT_DIR) && is_dir($component_dir)) {
        $dirs[] = $component_dir;
      }
    }
    return $dirs;
  }

  /**
   * Parse a custom component Yaml file.
   *
   * @param string $filepath
   *   The path to the file that needs to be parsed.
   *
   * @return array
   *   An array of custom component data read from the file.
   */
  protected function parse($filepath) {
    // Make sure the file exists.
    if (file_exists($filepath) === FALSE) {
      throw new FileNotFoundException($filepath);
    }

    $file_content = file_get_contents($filepath);

    if ($file_content === FALSE) {
      throw new FileUnreadableException($filepath);
    }

    // Straight Yaml decode.
    $parsed_data = Yaml::decode($file_content);

    // Validate that the custom component has the required keys.
    $missing_keys = array_diff(self::REQUIRED_PROPERTIES, array_keys($parsed_data));
    if (!empty($missing_keys)) {
      throw new CustomComponentMissingPropertiesException($filepath, self::REQUIRED_PROPERTIES, $missing_keys);
    }
    return $parsed_data;
  }

  /**
   * Recursively scans a base directory for the custom components it contains.
   *
   * @param string $dir
   *   A relative base directory path to scan, without trailing slash.
   *
   * @return array
   *   An array of paths with filenames for each custom component yml.
   *
   * @see \Drupal\Core\Extension\Discovery\RecursiveExtensionFilterIterator
   * @see \Drupal\Core\Extension\ExtensionDiscovery
   */
  protected function scanDirectory($dir) {
    // Validate that this is a directory worth investigating.
    if (!is_dir($dir)) {
      return NULL;
    }
    $files = [];
    // ******************** UNLEASH THE KRAKEN ********************.
    // We need to create a recursive iterator to crawl the directory and look
    // for custom component yml files. This type of action can be resource
    // intensive. For performance reasons, we want to limit the number of
    // subdirectories that we will search inside. First we create a filter to
    // do so. Use Unix paths regardless of platform, skip dot directories,
    // follow symlinks (to allow extensions to be linked from elsewhere), and
    // return the RecursiveDirectoryIterator instance to have access to
    // getSubPath(), since SplFileInfo does not support relative paths.
    $flags = \FilesystemIterator::UNIX_PATHS;
    $flags |= \FilesystemIterator::SKIP_DOTS;
    $flags |= \FilesystemIterator::FOLLOW_SYMLINKS;
    $flags |= \FilesystemIterator::CURRENT_AS_SELF;
    $dir_iterator = new \RecursiveDirectoryIterator($dir, $flags);

    // Allow directories specified in settings.php to be ignored. You can use
    // this to not check for files in common special-purpose directories.
    $ignore_dir = Settings::get('sitestudio_file_scan_ignore_directories', []);

    // Create the filter to use. Note that this is based on ExtensionDiscovery
    // We can safely use the same filters for finding info.yml files here.
    $filter = new RecursiveComponentFilterIterator($dir_iterator, $ignore_dir);

    // Grab the list of files that have been discovered using the filter above
    // to avoid scanning all subdirectories.
    $iterator = new \RecursiveIteratorIterator(
      $filter,
      \RecursiveIteratorIterator::LEAVES_ONLY,
      \RecursiveIteratorIterator::CATCH_GET_CHILD
    );

    // Loop through the files found in directory and all valid subdirectories.
    foreach ($iterator as $fileinfo) {
      // If this isn't a valid custom component file, then go to the next one.
      if (!preg_match(static::PHP_FUNCT_PATTERN, $fileinfo
        ->getBasename(self::CUSTOM_COMPONENT_POSTFIX))) {
        continue;
      }
      // Set the name of the custom component and the pathname of where to
      // find it.
      $name = $fileinfo->getBasename(self::CUSTOM_COMPONENT_POSTFIX);
      // Set the full path including the file itself.
      $pathfilename = $fileinfo->getPathName();
      // Add the filepath keyed by custom component machine name.
      $files[$name] = $pathfilename;
    }
    return $files;
  }

  /**
   * Clears cached definitions.
   */
  public function clearCachedDefinitions() {}

}
