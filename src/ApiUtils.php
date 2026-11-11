<?php

namespace Drupal\cohesion;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Site\Settings;

/**
 * Helper functions for Site studio API client.
 *
 * @package Drupal\cohesion\Plugin
 */
class ApiUtils {

  /**
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuid;

  /**
   * ApiUtils constructor.
   *
   * @param \Drupal\Component\Uuid\UuidInterface $uuid
   */
  public function __construct(UuidInterface $uuid) {
    $this->uuid = $uuid;
  }

  /**
   * If cohesion_devel is installed, use config, otherwise build the URL
   * from the module version number.
   */
  public function getAPIServerURL() {
    if (Settings::get('dx8_editable_api_url', FALSE)) {
      $config = \Drupal::config('cohesion.settings');
      return $config ? $config->get('api_url') : '';
    }
    else {
      return 'https://eu-api.sitestudio.acquia.com';
    }
  }

  /**
   * Return the version of the API to send in the headers to the API.
   */
  public function getApiVersionNumber() {
    if (Settings::get('dx8_editable_version_number', FALSE)) {
      // Get the version number from settings config.
      $config = \Drupal::config('cohesion.settings');
      $version = $config->get('override_version_number');
      return $version ? $version : 'master';
    }
    else {
      // Get the version number from the module info.yml file.
      $module_info = [];
      try {
        $module_info = \Drupal::service('extension.list.module')->getExtensionInfo('cohesion');
      }
      catch (\Throwable $e) {
      }

      if (strstr($module_info['version'], '-master')) {
        return 'master';
      }
      else {
        $version = $module_info['version'];
        $version = str_replace('8.x-', '', $version);
        $versions = explode('.', $version);
        return $versions[0] . '-' . $versions[1];
      }
    }
  }

  /**
   * @param $value
   * @param array $map
   * @param array $content_paths
   *
   * @return mixed|null
   */
  public function mapContentField($value, $map = [], $content_paths = []) {
    if (in_array($map, $content_paths) && (strpos($value, 'field.') !== FALSE)) {
      $replace_str = ['[' => '', ']' => '', 'field.' => ''];
      return str_replace(array_keys($replace_str), array_values($replace_str), $value);
    }

    return NULL;
  }

  /**
   * Recurse through a JSON object and extract all UUIDs that are keys.
   *
   * @param $object
   * @param $uuids
   */
  private function extractUUIDKeys($object, &$uuids) {
    $pattern = "#[0-9a-f]{7,8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}#i";

    foreach ($object as $key => $value) {
      // Search key for UUIDS.
      if (preg_match_all($pattern, $key, $this_uuids)) {
        $uuids[] = $key;
        // Recurse unless the parent is a uuid which means this is component
        // in a component.
      }
      elseif (is_array($value)) {
        $this->extractUUIDKeys($value, $uuids);
      }
    }
  }

  /**
   * Replace UUIDs that are keys in the JSON with new UUIDs.
   *
   * @param string $json_values
   *
   * @return string
   */
  public function uniqueJsonKeyUuids($json_values = '') {
    // Find keys that are uuids.
    $uuids = [];
    $this->extractUUIDKeys(Json::decode($json_values), $uuids);

    // Nothing found.
    if (!$uuids) {
      return $json_values;
    }
    $uuids = array_unique($uuids);

    // Generate new uuids.
    $updated_uuids = array_map(function ($value) {
      $value = $this->uuid->generate();
      return $value;
    }, $uuids);

    // Replace UUIDs with newly generated ones.
    return str_replace($uuids, $updated_uuids, $json_values);
  }

}
