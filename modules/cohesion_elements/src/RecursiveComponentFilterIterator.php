<?php

namespace Drupal\cohesion_elements;

/**
 * Filters a RecursiveDirectoryIterator to discover components.
 *
 * To ensure the best possible performance for component discovery, this
 * filter implementation hard-codes a range of assumptions about directories
 * in which Drupal components may not appear. Every unnecessary
 * subdirectory tree recursion is avoided.
 *
 * @todo Use RecursiveCallbackFilterIterator instead of the $acceptTests
 *   parameter forwarding once PHP 5.4 is available.
 *   https://www.drupal.org/node/2532228
 */
class RecursiveComponentFilterIterator extends \RecursiveFilterIterator {

  /**
   * List of directory names to skip when recursing.
   *
   * These directories are globally ignored in the recursive filesystem scan;
   *
   * @var array
   */
  protected $blocklist = [
    // Object-oriented code subdirectories.
    'src',
    'lib',
    'vendor',
    // Front-end.
    'assets',
    'css',
    'files',
    'images',
    'js',
    'misc',
    'templates',
    // Legacy subdirectories.
    'includes',
    // Test subdirectories.
    'fixtures',
    'Drupal',
    'node_modules',
    'bower_components',
  ];

  /**
   * Whether to include test directories when recursing.
   *
   * @var bool
   */
  protected $acceptTests = FALSE;

  /**
   * Construct a RecursiveComponentFilterIterator.
   *
   * @param \RecursiveDirectoryIterator $iterator
   *   The iterator to filter.
   * @param array $blocklist
   *   (optional) Add to the blocklist of directories that should be filtered
   *   out during the iteration.
   */
  public function __construct(\RecursiveDirectoryIterator $iterator, array $blocklist = []) {
    parent::__construct($iterator);
    $this->blocklist = array_merge($this->blocklist, $blocklist);
  }

  /**
   * Controls whether test directories will be scanned.
   *
   * @param bool $flag
   *   Pass FALSE to skip all test directories in the discovery. If TRUE,
   *   components in test directories will be discovered and only the global
   *   directory blocklist in RecursiveComponentFilterIterator::$blocklist is
   *   applied.
   */
  public function acceptTests($flag = FALSE) {
    $this->acceptTests = $flag;
    if (!$this->acceptTests) {
      $this->blocklist[] = 'tests';
    }
  }

  /**
   * {@inheritdoc}
   */
  #[\ReturnTypeWillChange]
  public function getChildren() {
    $filter = parent::getChildren();
    // Pass on the blocklist.
    $filter->blocklist = $this->blocklist;
    // Pass the $acceptTests flag forward to child iterators.
    $filter->acceptTests($this->acceptTests);
    return $filter;
  }

  /**
   * {@inheritdoc}
   */
  #[\ReturnTypeWillChange]
  public function accept() {
    $name = $this->current()->getFilename();
    // FilesystemIterator::SKIP_DOTS only skips '.' and '..', but not hidden
    // directories (like '.git').
    if ($name[0] == '.') {
      return FALSE;
    }
    if ($this->isDir()) {
      // Accept the directory unless the name is blocklisted.
      return !in_array($name, $this->blocklist, TRUE);
    }
    else {
      // Only accept component yml files.
      return str_ends_with($name, '.custom_component.yml');
    }
  }

}
