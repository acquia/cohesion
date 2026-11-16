<?php

namespace Drupal\Tests\cohesion_sync\Unit\Config;

use Drupal\cohesion_sync\Config\CohesionFileStorage;
use Drupal\Component\Serialization\Yaml;
use Drupal\Tests\UnitTestCase;

/**
 * Class CohesionFileStorageTest
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\cohesion_sync\Unit\Config
 */
class CohesionFileStorageTest extends UnitTestCase {

  const FIXTURE_PATH = __DIR__ . '/../../../fixtures';

  /**
   * @dataProvider dataGetFileFixtures
   *
   * @param string $directory
   * @param array $expectedList
   *
   * @return void
   */
  public function testGetFiles(string $directory, array $expectedList) {
    $fileStorage = new CohesionFileStorage($directory);
    $this->assertEquals($expectedList, $fileStorage->getFiles());
  }

  /**
   * Data provider for ::testGetFiles.
   *
   * @return array
   */
  public function dataGetFileFixtures() {

    // Package with file index.
    $directory = self::FIXTURE_PATH . '/file_test_1';
    $index_file_path = $directory . '/' . CohesionFileStorage::FILE_INDEX_FILENAME;
    $expectedFiles = json_decode(file_get_contents($index_file_path), TRUE);

    $data[] = [
      $directory,
      $expectedFiles,
    ];

    // Package with individual file metadata.
    $directory = self::FIXTURE_PATH . '/file_test_2';
    $expectedFiles = $this->readMetadata($directory);

    $data[] = [
      $directory,
      $expectedFiles,
    ];

    // Package with both the index file and individual metadata.
    $directory = self::FIXTURE_PATH . '/file_test_3';
    $expectedFiles = $this->readMetadata($directory);

    $data[] = [
      $directory,
      $expectedFiles,
    ];

    // Package with files, but no metadata.
    $directory = self::FIXTURE_PATH . '/file_test_4';
    $expectedFiles = [];

    $data[] = [
      $directory,
      $expectedFiles,
    ];

    // Package with individual file metadata, URI mismatch the filenames.
    $directory = self::FIXTURE_PATH . '/file_test_2';
    $expectedFiles = $this->readMetadata($directory);

    $data[] = [
      $directory,
      $expectedFiles,
    ];

    return $data;
  }

  /**
   * Reads and returns all metadata files from specified directory.
   *
   * @param string $directory
   *
   * @return array
   */
  protected function readMetadata(string $directory) {
    $metadata = [];
    $pattern = '/^' . preg_quote(CohesionFileStorage::FILE_METADATA_PREFIX, '/') . '.*' . preg_quote('.yml', '/') . '$/';
    $files = scandir($directory);
    foreach ($files as $file) {
      if ($file[0] !== '.' && preg_match($pattern, $file)) {
        $metadata[] = Yaml::decode(file_get_contents($directory . '/' . $file));
      }
    }

    return $metadata;
  }

}
