<?php

namespace Drupal\cohesion;

use Composer\Semver\Semver;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;

/**
 *
 */
class SettingsEndpointUtils {

  /**
   * @var \Drupal\cohesion\CohesionApiElementStorage
   */
  protected $apiElementStorage;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\cohesion\EntityGroupsPluginManager
   */
  protected $entityGroupsManager;

  /**
   * SettingsEndpointUtils constructor.
   *
   * @param \Drupal\cohesion\CohesionApiElementStorage $apiElementStorage
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\cohesion\EntityGroupsPluginManager $entity_groups_manager
   */
  public function __construct(CohesionApiElementStorage $apiElementStorage, EntityTypeManagerInterface $entity_type_manager, EntityGroupsPluginManager $entity_groups_manager) {
    $this->apiElementStorage = $apiElementStorage;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityGroupsManager = $entity_groups_manager;
  }

  /**
   * @param bool $isComponentBuilder
   * @param array $categories
   * @param array $asset_data
   *
   * @return array
   */
  public function elementCategoriesData($isComponentBuilder, $categories = [], $asset_data = []) {
    $results = [];

    // Filter categories based on dx8 access permissions.
    foreach ($categories as $key => $category) {
      // Component builder group should only be available within the component
      // builder.
      if ($key == 'component-builder-elements' && $isComponentBuilder !== TRUE) {
        continue;
      }
      $access_key = isset($category['label']) ? 'access ' . strtolower($category['label']) . ' group' : NULL;

      if (isset($category['elements']) && is_array($category['elements']) && \Drupal::currentUser()->hasPermission($access_key)) {

        $results[$key] = $category;
        $elements = [];

        foreach ($category['elements'] as $element_id) {
          $elements[$element_id] = $asset_data[$element_id] ?? [];
        }
        // Assign new elements.
        $results[$key]['children'] = array_values($elements);
        unset($results[$key]['elements']);
      }
    }
    return array_values($results);
  }

  /**
   * Return one or all elements from asset storage group.
   *
   * @param bool $isComponentBuilder
   * @param $assetLibrary
   * @param $type
   * @param $group
   * @param bool $with_categories
   *
   * @return array
   */
  public function getAssets($isComponentBuilder, $assetLibrary, $type, $group, $with_categories = FALSE) {
    if ($assetLibrary) {
      if ($group) {
        if ($type == '__ALL__') {
          // Get all assets.
          if ($group == 'elements' && $with_categories) {

            $asset_data = $assetLibrary->getAll();

            // Get a weighted list of category items.
            $element_keys = [];
            foreach (\Drupal::service('cohesion.element.storage')->getByGroup('element_categories') as $element) {
              $element_keys[] = $element['element_id'];
            }

            // And query the key/value storage using this sorted list of element
            // ids.
            $assetCategoryLibrary = \Drupal::keyValue('cohesion.assets.element_categories');
            $categories = $assetCategoryLibrary->getMultiple($element_keys);

            $asset = [
              'categories' => $this->elementCategoriesData($isComponentBuilder, $categories, $asset_data),
            ];

            // Patch in the custom elements.
            if (\Drupal::currentUser()->hasPermission('access custom elements group')) {
              $asset['categories'] = \Drupal::service('custom.elements')->patchElementCategoryList($asset['categories']);
            }

          }
          else {
            $asset = [];

            foreach (\Drupal::service('cohesion.element.storage')->getByGroup($group) as $element) {
              $asset[$element['element_id']] = $assetLibrary->get($element['element_id']);
            }
          }
        }
        else {
          $asset = $assetLibrary->get($type);
        }

        $data = $asset ?: [];
        $error = $data ? FALSE : TRUE;
        $message = $data ? t('Site Studio asset library loaded.') : t('Empty Site Studio asset library.');
      }
      else {
        $error = TRUE;
        $data = [];
        $message = t('Asset :group :type not found.', [
          ':group' => $group,
          ':type' => $type,
        ]);
      }
    }
    else {
      // Could not load asset library.
      $error = TRUE;
      $data = [];
      $message = t('Could not load Asset library.');
    }

    return [$error, $data, $message];
  }

  /**
   * Get the initil form JSON.
   *
   * @param $group
   * @param $type
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   */
  public function getCohFormOnInit($group, $type) {
    $assetLibrary = \Drupal::keyValue('cohesion.assets.' . $group);

    [$error, $data, $message] = $this->getAssets(FALSE, $assetLibrary, $type, $group, FALSE);

    // Return the (optionally) patched results.
    return $data;
  }

  /**
   * Function siteLibraries.
   *
   * @param $bundle
   *
   * @return array|null
   */
  public function siteLibraries($bundle) {
    $urls = &drupal_static(__FUNCTION__ . $bundle);

    if (!isset($urls)) {
      $urls = [];

      switch ($bundle) {

        case 'font_libraries':
          $data = $this->getLibraries($bundle);
          if ($data) {
            array_walk_recursive($data, function ($value, $key) use (&$urls) {
              $vals = Json::decode($value);
              if (isset($vals['fonts'])) {
                foreach ($vals['fonts'] as $icon_library) {
                  if (isset($icon_library['library']['type']) && $icon_library['library']['type'] == 'import') {
                    $urls[] = $icon_library['library']['url'] ?? NULL;
                  }
                }
              }
            });
          }
          break;

        case 'icon_libraries':
          $data = $this->getLibraries($bundle);
          if ($data) {
            array_walk_recursive($data, function ($value, $key) use (&$urls) {
              $vals = Json::decode($value);
              if (isset($vals['iconLibraries'])) {
                foreach ($vals['iconLibraries'] as $icon_library) {
                  if (isset($icon_library['library']['url']) && isset($icon_library['library']['type']) && $icon_library['library']['type'] == 'import') {
                    $urls[] = $icon_library['library']['url'];
                  }
                }
              }
            });
          }
          break;
      }
    }
    return !empty($urls) ? $urls : NULL;
  }

  /**
   * Function getLibraries.
   *
   * @param $bundle
   * @param bool $all_values
   *
   * @return array|mixed|null
   */
  public function getLibraries($bundle, $all_values = FALSE) {
    $results = &drupal_static(__FUNCTION__);
    $tag = [$bundle];
    if ($bundle) {
      $cid = $bundle . '_libraries:' . \Drupal::languageManager()->getCurrentLanguage()->getId();

      $cache = (\Drupal::cache()->get($cid)) ?: FALSE;

      if ($cache) {
        $results = $cache->data;
      }
      else {
        $results = $this->getWebsiteSettingsValuesById($bundle);
        \Drupal::cache()->set($cid, $results, CacheBackendInterface::CACHE_PERMANENT, $tag);
      }
    }
    return $results;
  }

  /**
   * Function getEndpointLibraries.
   *
   * @param $bundle
   *   * @return array|mixed|null
   */
  public function getEndpointLibraries($bundle) {
    $results = &drupal_static(__FUNCTION__);
    $tag = [$bundle];
    if ($bundle) {
      $cid = $bundle . '_endpoint_libraries:' . \Drupal::languageManager()->getCurrentLanguage()->getId();

      $cache = (\Drupal::cache()->get($cid)) ?: FALSE;

      if ($cache && FALSE) {
        $results = $cache->data;
      }
      else {
        $results = $this->getWebsiteSettingsValues($bundle);
        \Drupal::cache()->set($cid, $results, CacheBackendInterface::CACHE_PERMANENT, $tag);
      }
    }
    return $results;
  }

  /**
   * Get all the colors combined in a single array (for Angular color picker).
   *
   * @param null $item
   *
   * @return array
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getColorsList($item = NULL) {
    $color_values = [];

    /** @var \Drupal\cohesion_website_settings\Entity\Color $color_entity */
    foreach ($this->entityTypeManager->getStorage('cohesion_color')->loadMultiple() as $color_entity) {
      if ($json_values = $color_entity->getDecodedJsonValues()) {
        // Reorder the colors by weight.
        $weight = $color_entity->getWeight();

        if (isset($color_values[$weight])) {
          while (isset($color_values[$weight])) {
            $weight++;
          }
        }

        if ($item == NULL || $item == $json_values['variable']) {
          $color_values[$weight] = $json_values;
        }

      }
    }

    ksort($color_values);

    return $color_values;
  }

  /**
   * Retrieve a website settings value by its id.
   *
   * @param null $id
   *
   * @return array|null
   *
   * @todo the hardcoded/switch logic should be moved into the corresponding entity class file.
   */
  private function getWebsiteSettingsValuesById($id = NULL) {
    $result = [];

    switch ($id) {
      // Combine font library and font stack entities into a single JSON object.
      case 'font_libraries':
        try {
          $plugin = $this->entityGroupsManager->createInstance('font_libraries_entity_groups');
          $result[$id] = $plugin->getGroupJsonValues();
        }
        catch (\Exception $e) {
        }
        break;

      // Combine icon library entities into a single JSON object.
      case 'icon_libraries':
        try {
          $plugin = $this->entityGroupsManager->createInstance('icon_libraries_entity_groups');
          $result[$id] = $plugin->getGroupJsonValues();
        }
        catch (\Exception $e) {
        }
        break;

      // Website settings that exist as a single WebsiteSettings entity. Just
      // get their JSON object from the entity.
      default:
        $website_settings = [];

        if ($id) {
          try {
            if ($storage = $this->entityTypeManager->getStorage('cohesion_website_settings')) {
              $entityId = $storage->getQuery()
                ->accessCheck(TRUE)
                ->condition("status", 1)
                ->condition("id", $id, '=')
                ->execute();
              if ($entityId) {
                /** @var \Drupal\cohesion_website_settings\Entity\WebsiteSettings $website_settings */
                $website_settings = $storage->load(reset($entityId));
              }
            }
          }
          catch (\Exception $ex) {
            return [];
          }
        }

        if (!empty($website_settings)) {
          if ($website_settings->getJsonValues()) {
            $result[$id] = $website_settings->getJsonValues();
          }
        }

        break;
    }

    return $result;
  }

  /**
   * Retrieve website settings values.
   *
   * @param null $bundle_type
   *
   * @return array|null
   */
  private function getWebsiteSettingsValues($bundle_type = NULL) {
    $result = NULL;
    $website_settings_ids = [];
    $icon_content = [];
    $font_content = [];
    $system_font = [];

    if ($bundle_type) {
      $result = $this->getWebsiteSettingsValuesById($bundle_type);
    }
    else {
      try {
        if ($storage = $this->entityTypeManager->getStorage('cohesion_website_settings')) {
          $ids = $storage->getQuery()
            ->accessCheck(TRUE)
            ->condition('status', 1)
            ->execute();
          if ($ids) {
            $website_settings_ids = $storage->loadMultiple($ids);
          }
        }
      }
      catch (\Exception $ex) {
        return [];
      }
    }

    if (!empty($website_settings_ids)) {
      foreach ($website_settings_ids as $key => $website_setting) {
        if ($website_setting->getJsonValues()) {
          $result[$key] = $website_setting->getJsonValues();
        }
      }
    }

    if (isset($result[$bundle_type])) {
      switch ($bundle_type) {
        case 'icon_libraries':
          // Callback for processing a single icon font library.
          $icon_font_library_callback = function ($library, $key) use (&$icon_content) {
            if (isset($library['library']['iconJSON']['json'])) {

              // Get the basename of the saved JSON file.
              $basename = basename($library['library']['iconJSON']['json']);

              // Load and decode the JSON.
              $file_path = COHESION_FILESYSTEM_URI . $basename;
              $file_content = file_get_contents($file_path);
              $file_content = json_decode($file_content);
              // Set key to prevent storing duplicate content.
              $content_key = $library['library']['name'] ?? $basename;
              $library_type = $library['library']['type'] ?? 'custom';
              $font_family = $library['library']['fontFamilyName'] ?? NULL;

              // Add it to a keyed array (by icon label).
              if (!$font_family) {
                $icon_content[$content_key] = [
                  'type' => $library_type,
                  'icons' => $file_content,
                ];
              }
              // Has a font family.
              else {
                $icon_content[$content_key] = [
                  'type' => $library_type,
                  'font-family' => $font_family,
                  'icons' => $file_content,
                ];
              }
            }
          };

          if (($icon_fonts = Json::decode($result[$bundle_type])) && isset($icon_fonts['iconLibraries'])) {
            array_walk($icon_fonts['iconLibraries'], $icon_font_library_callback);
          }

          break;

        case 'font_libraries':
          if (($font_stack = json_decode($result[$bundle_type]))) {
            $font_content = property_exists($font_stack, 'fontStacks') ? $font_stack->fontStacks : [];
          }
          break;

      }
    }

    // Get Site Studio static values.
    switch ($bundle_type) {
      case 'system_font':
        $static_asset_library = \Drupal::keyValue('cohesion.assets.static_assets');
        $system_font = $static_asset_library->get($bundle_type) ?: [];
        break;
    }

    if ($bundle_type == 'font_libraries') {
      $result = $font_content;
    }
    elseif ($bundle_type == 'icon_libraries') {
      $result = $icon_content;
    }
    elseif ($bundle_type == 'system_font') {
      $result = $system_font;
    }

    return $result;
  }

  /**
   * Import a form asset and store in the asset library.
   *
   * @param array $data
   * @param $context
   *
   * @throws \Exception
   */
  public function importAsset(array $data, &$context) {
    $content = NULL;
    // Ensure the actual asset data it at the root.
    if ($data && array_key_exists('data', $data)) {
      $data = array_shift($data);
    }

    $context['message'] = t('Importing @group', [
      '@group' => ucfirst(str_replace('_', ' ', $data['element_group'])),
    ]);

    $element_group = $data['element_group'] ?? NULL;

    $exclude_groups = ['global_libraries', 'element_templates'];
    $uri = '/group/' . $element_group;

    if (($response = \Drupal::service('cohesion.api_client')->getAssetJson($uri)) && $response->getStatusCode() === 200) {
      $content = $response->getBody()->getContents();
    }
    else {
      $context['results']['error'] = t('Failed to connect to @group. Please try again', ['@group' => ucfirst(str_replace('_', ' ', $data['element_group']))]);
      return;
    }

    // Process the asset.
    if (($results = Json::decode($content))) {

      foreach ($results as $result) {
        $id = $result['id'] ?? NULL;
        $label = $result['label'] ?? NULL;

        // Make sure all code hitting the database is JSON decoded and can be
        // serialized.
        if (isset($result['content'])) {
          if (!is_array($result['content'])) {
            try {
              // This is probably a JSON object, so try and decode it.
              $content = Json::decode($result['content']);
            }
            catch (\Exception $e) {
              // Failed to decode, so just store the content as its raw value.
              $content = $result['content'];
            }
          }
          else {
            // Content is already an array, so just store as is.
            $content = $result['content'];
          }
        }

        if ($id && !in_array($element_group, $exclude_groups)) {
          // Store asset in keyValue storage.
          $asset_library = \Drupal::keyValue('cohesion.assets' . '.' . $element_group);
          $asset_library->set($id, $content);

          // Update element schema table.
          $update_data = [
            'element_id' => $id,
            'element_label' => $label,
            'element_group' => $element_group,
            'feature_id' => $result['feature_id'] ?? '1.0',
            'element_weight' => $result['weight'] ?? 0,
            'element_element' => $result['element'] ?? NULL,
          ];
          $this->apiElementStorage->cohUpsert($update_data);
        }

        // Import asset dependencies(js, css)
        if (is_array($result) && isset($result['assets'])) {
          $this->importLibraries($result['assets'], $element_group);
        }
      }
    }
  }

  /**
   * Import js and css libraries associated with an element template.
   *
   * @param $assets
   * @param $element_group
   */
  private function importLibraries($assets, $element_group) {
    $elements_asset_libraries = \Drupal::keyValue('cohesion.elements.asset.libraries');
    foreach ($assets as $k_asset => $asset) {
      if(!isset($asset['core_compatibility']) || Semver::satisfies(\Drupal::VERSION, $asset['core_compatibility'])){
        // Import any available js assets.
        if (isset($asset['js'])) {
          $this->_saveAssets('js', $asset);
        }

        // Import any available css assets.
        if (isset($asset['css'])) {
          $this->_saveAssets('css', $asset);
        }

        // Import any available generic assets.
        if (isset($asset['assets'])) {
          $this->_saveAssets('assets', $asset);
        }

        // Import available default json assets.
        if (isset($asset['json'])) {
          $this->_saveAssets('json', $asset);
        }
        $elements_asset_libraries->set($element_group . '.' . $asset['id'], $asset);
      }
    }
  }

  /**
   * Helper function for importLibraries().
   *
   * @param $type
   * @param array $asset
   */
  private function _saveAssets($type, &$asset = []) {
    if (!is_array($asset)) {
      return;
    }

    // Get the correct path depending on the type of asset.
    $paths = [
      'css' => COHESION_CSS_PATH,
      'js' => COHESION_JS_PATH,
      'assets' => COHESION_ASSETS_PATH,
      'json' => COHESION_DEFAULT_PATH,
    ];
    $cohesion_asset_path = $paths[$type] ?? NULL;
    // Loop through the assets within the js or css section.
    foreach ($asset[$type] as $key => $library) {
      if ($library['external'] === FALSE && $cohesion_asset_path) {
        $asset_dir_path = sprintf("%s/%s", $cohesion_asset_path, $asset['id']);
        $filename = basename($library['asset_url']);
        if (isset($library['type'])) {
          $asset_dir_path .= '/' . $library['type'];
        }
        $asset_file_path = sprintf("%s/%s", $asset_dir_path, $filename);
        $asset_content = NULL;
        if (!is_dir($asset_dir_path) && !file_exists($asset_dir_path)) {
          if (\Drupal::service('file_system')->prepareDirectory($asset_dir_path, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS)) {
            \Drupal::messenger()->addMessage(t('Element @asset_id asset directory created', ['@asset_id' => $asset['id']]));
          }
        }

        $asset_response = \Drupal::service('cohesion.api_client')->getAssetJson($library['asset_url']);
        if ($asset_response && $asset_response->getStatusCode() == 200) {
          $asset_content = $asset_response->getBody()->getContents();
        }

        try {
          \Drupal::service('file_system')->saveData($asset_content, $asset_file_path, FileSystemInterface::EXISTS_REPLACE);
          $asset[$type][$key]['asset_url'] = $asset_file_path;
        }
        catch (\Throwable $e) {
          $asset[$type][$key]['asset_url'] = '';
        }
      }
      preg_match_all('#[.\[\]]*\[([a-zA-Z_]+)\][.\[\]]*#', $library['asset_url'], $matches);
      if (isset($matches[1])) {
        foreach ($matches[1] as $keyi => $config_key) {
          if ($config_value = \Drupal::config('cohesion.settings')->get($config_key)) {
            $asset[$type][$keyi]['asset_url'] = str_replace($matches[0][$keyi], $config_value, $library['asset_url']);
          }
        }
      }
    }
  }

  /**
   * Log js error message.
   *
   * @param string $error_data
   */
  public function logError($error_data = NULL) {
    if ($error_data) {
      \Drupal::logger('cohesion_js_error')->error($error_data);
    }
  }

  /**
   * Return a list of DX8 permission for the current user (based on roles).
   *
   * @return array
   */
  public function dx8PermissionsList() {
    $permissions = [];
    foreach (\Drupal::currentUser()->getRoles(FALSE) as $role) {
      $permissions = array_merge($permissions, $this->dx8PermissionsByRole($role));
    }

    return array_values(array_unique($permissions));
  }

  /**
   * Get list of DX8 permission by a particular role.
   *
   * @param $role
   *
   * @return array
   */
  private function dx8PermissionsByRole($role) {
    // User 1 is always administrator.
    if (\Drupal::currentUser()->id() == 1) {
      $role = 'administrator';
    }

    $cache_key = __FUNCTION__ . $role;

    // Attempt to use an existing cache for this list.
    $dx8_permissions = &drupal_static($cache_key);
    if (!isset($dx8_permissions)) {
      if ($cache = \Drupal::cache()->get($cache_key)) {
        $dx8_permissions = $cache->data;
      }
      else {
        // No cache found, so need to re-build it.
        $results = [];
        $cohesion_modules = $this->dx8SubModules();
        $dx8_permissions = [];
        $role_entity = $this->entityTypeManager->getStorage('user_role')->load($role);
        if ($role_entity) {
          $user_permissions = $role_entity->getPermissions();

          // Filter permissions base on logged in user role.
          foreach ($user_permissions as $permissions) {
            $results = array_merge($results, (array) $permissions);
          }

          // Filter cohesion_permissions from site permissions.
          foreach (\Drupal::service('user.permissions')->getPermissions() as $permission_id => $permission) {
            if (in_array($permission['provider'], $cohesion_modules)) {
              $dx8_permissions[] = $permission_id;
            }
          }
        }

        $dx8_permissions = $role == 'administrator' ? $dx8_permissions : array_values(array_intersect($results, $dx8_permissions));

        // Store these results in the cache. Invalidate this cache when the role
        // changes permissions.
        \Drupal::cache()->set($cache_key, $dx8_permissions, CacheBackendInterface::CACHE_PERMANENT, ['config:user.role.' . $role]);
      }
    }
    return $dx8_permissions;
  }

  /**
   * Helper. See above.
   *
   * @return array of cohesion submodules
   */
  private function dx8SubModules() {
    $system_modules = \Drupal::service('extension.list.module')->reset()->getList();
    if (\Drupal::service('module_handler')
      ->moduleExists('cohesion') && (in_array('cohesion', array_keys($system_modules)) || in_array('dx8', array_keys($system_modules))) && ($required_by = $system_modules['cohesion']->required_by)) {

      $dx8_submodule_callback = function ($module) {
        return (\Drupal::service('module_handler')->moduleExists($module) && \Drupal::service('user.permissions')->moduleProvidesPermissions($module));
      };
      $modules = array_filter(array_keys($required_by), $dx8_submodule_callback);
      $modules[] = 'cohesion';
      $results = array_values($modules);
    }
    else {
      $results = [];
    }

    return $results;
  }

}
