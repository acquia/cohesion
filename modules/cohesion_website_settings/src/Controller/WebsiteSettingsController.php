<?php

namespace Drupal\cohesion_website_settings\Controller;

use Drupal\cohesion\CohesionJsonResponse;
use Drupal\cohesion\Event\SiteStudioEvents;
use Drupal\cohesion\Event\PreRebuildEvent;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Config\Entity\ConfigEntityTypeInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Utility\Error;

/**
 * Class WebsiteSettingsController.
 *
 * Returns responses for WebsiteSettings routes.
 *
 * @package Drupal\cohesion_website_settings\Controller
 */
class WebsiteSettingsController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * WebsiteSettingsController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack
   */
  public function __construct(RequestStack $requestStack) {
    $this->request = $requestStack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('request_stack')
    );
  }

  /**
   * POST: /admin/cohesion/upload/font_libraries.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Symfony\Component\HttpFoundation\Response json response data
   *   Callback from angular form that responds what fonts are included in the
   *   zip, and unzip it to a temporary directory, and return a response to
   *   angular to highlight which fonts are in there. Sets an "updated" flag in
   *   the json if the font has been updated so on entity save it knows to
   *   handle a change in the font files
   */
  public function fontLibrariesPostCallback(Request $request) {
    $response = new Response();
    $response->headers->set('Content-Type', 'application/json');

    $accepted_types = [
      'application/zip',
      'application/x-zip-compressed',
      'multipart/x-zip',
      'application/x-compressed',
      'application/octet-stream',
    ];
    $accepted_extensions = ['eot', 'ttf', 'woff', 'woff2'];
    $temp_folder = \Drupal::service('cohesion.local_files_manager')->scratchDirectory();
    $file = $request->files->get("file");
    // Move uploaded ZIP file to temp directory if valid.
    if ($file && !$file->getError() && in_array(\Drupal::service('file.mime_type.guesser')->guessMimeType($file), $accepted_types) && pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION) == "zip") {
      $filename = $file->getClientOriginalName();
      $file->move($temp_folder, $filename);

      $zip = new \ZipArchive();
      $return = [];
      foreach ($accepted_extensions as $extension) {
        $return[$extension] = NULL;
      }

      $real_path = \Drupal::service('file_system')->realpath($temp_folder . "/" . $filename);
      if ($zip->open($real_path) === TRUE) {
        for ($i = 0; $i < $zip->numFiles; $i++) {
          $fontname = $zip->getNameIndex($i);
          if (strpos($fontname, "_MAC") > 0) {
            continue;
          }
          $fontname = preg_replace("/[^a-zA-Z0-9-_.]/", "+", basename($fontname));
          $ext = pathinfo($fontname, PATHINFO_EXTENSION);
          // Save each font file in the temp directory.
          if (in_array($ext, $accepted_extensions)) {
            $font = $zip->getFromIndex($i);

            $file_destination = $temp_folder . "/" . $fontname;

            try {
              \Drupal::service('file_system')->saveData($font, $file_destination);
              $return[$ext]['uri'] = '"' . $file_destination . '"';
              unset($accepted_extensions[$ext]);
            }
            catch (\Throwable $e) {
            }
          }
        }
        $zip->close();
        unlink($temp_folder . "/" . $filename);
      }
      else {

        \Drupal::logger('api-call-error')->error(t("Error occurred while uploading the file."));
        $response->setStatusCode(400);
        $return = (object) [
          "message" => "Site Studio API",
          "error" => $this->t("Error occurred while uploading the file."),
        ];
      }
    }
    else {

      if ($file && $file->getError()) {
        $message = $file->getErrorMessage();
      }
      else {
        $message = $this->t("Error occurred while uploading the file.");
      }

      \Drupal::logger('api-call-error')->error($message);
      $response->setStatusCode(400);
      $return = (object) [
        "message" => "Site Studio API",
        "error" => $message,
      ];
    }
    $response->setContent(Json::encode($return));
    return $response;
  }

  /**
   * User uploaded an icon JSON file via the Angular form. Returns the path to
   * the converted JSON file.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function iconLibrariesPostCallback(Request $request) {
    $response = new Response();
    $response->headers->set('Content-Type', 'application/json');

    $accepted_types = ['application/octet-stream'];
    $file = $request->files->get("file");

    // Check a file was uploaded.
    if ($file && in_array(\Drupal::service('file.mime_type.guesser')->guessMimeType($file), $accepted_types)) {

      $icons = \Drupal::service('cohesion.icon_interpreter')->sendToApi(file_get_contents($file->getPathname()));

      if ($icons['code'] == 200) {
        $contents = isset($icons['data']) ? json_encode($icons['data'], JSON_PRETTY_PRINT) : '';
      }
      else {
        $return = [
          "message" => "Site Studio API",
          "error" => $this->t("Invalid icon library loaded"),
        ];
        \Drupal::logger('api-call-error')->error(t("Error: Invalid icon library loaded"));
        $response->setContent(Json::encode($return));
        return $response;
      }

      if (is_array(Json::decode($contents))) {
        $filename = $file->getClientOriginalName();
        try {
          $file_unmanaged = FALSE;
          try {
            $file_unmanaged = \Drupal::service('file_system')->saveData($contents, COHESION_FILESYSTEM_URI . $filename);
          }
          catch (\Throwable $e) {
          }

          if (strpos($file_unmanaged, DRUPAL_ROOT) === 0) {
            $file_path = substr($file_unmanaged, strlen(DRUPAL_ROOT));
          }
          else {
            $file_path = $file_unmanaged;
          }

          $return = (object) [
            "json" => $file_path,
          ];
        }
        catch (FileException $e) {
          $return = (object) [
            "message" => "Site Studio API",
            "error" => $this->t("Error occured while uploading the file."),
          ];
          \Drupal::logger('api-call-error')->error(t("Error occurred while uploading the file."));
        }
      }
      else {
        \Drupal::logger('api-call-error')->error(t("Error occurred while uploading the file."));
        $return = (object) [
          "message" => "Site Studio API",
          "error" => $this->t("Error occured while uploading the file."),
        ];
      }
    }
    else {
      \Drupal::logger('api-call-error')->error(t("Error occurred while uploading the file."));
      $return = (object) [
        "message" => "Site Studio API",
        "error" => $this->t("Error occurred while uploading the file."),
      ];
    }

    $response->setContent(Json::encode($return));
    return $response;
  }

  /**
   * Endpoint to return one of the website settings library, color - font - icon
   *
   * GET: /cohesionapi/main/{type}.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Symfony\Component\HttpFoundation\Response json response data
   *   Endpoint to return one of the website settings library (color,font,icon)
   */
  public function libraryAction(Request $request) {
    // Get the type of website setting from the request.
    $type = $request->get('type');

    $error = FALSE;
    $status = 200;

    switch ($type) {
      case 'icon_libraries':
      case 'font_libraries':
      case 'responsive_grid_settings':
      case 'system_font':
        $content = \Drupal::service('settings.endpoint.utils')->getEndpointLibraries($type);
        break;

      case 'color_palette':
        // A second parameter can be passed for color palette to retrieve a
        // specific color.
        $item = $request->get('item');
        $colors = \Drupal::service('settings.endpoint.utils')->getColorsList($item);
        if ($item) {
          $content = array_pop($colors);
          if(empty($content)) {
            $status = 400;
            $error = TRUE;
            $content = ['error' => $this->t('Color %color cannot be found. Please make sure this color exists in your color palette before continuing', ['%color' => $item])];
          }
        } else {
          $content = array_values($colors);
        }

        break;

      default:
        $status = 400;
        $error = TRUE;
        $content = ['error' => $this->t('Unknown asset type: %type', ["%type" => $type])];
        break;
    }

    // Send response.
    return new CohesionJsonResponse([
      'status' => !$error ? 'success' : 'error',
      'data' => $content,
    ], $status);
  }

  /**
   * Filter the sidebar elements list by drupalSettings.cohesion.entityTypeId.
   *
   * @param $data
   * @param $entity_type_id
   *
   * @return mixed
   */
  private function filterByEntityTypeId($data, $entity_type_id) {
    // Get the exclusion list.
    $excludes = \Drupal::keyValue('cohesion.assets.static_assets')->get('sidebar-elements-exclude');

    // Strip elements.
    if ($excludes == NULL || !isset($excludes[$entity_type_id])) {
      $entity_type_id = 'default';
    }

    foreach ($data as $category_index => $category) {
      foreach ($category['children'] as $child_index => $child) {
        if (in_array($child['uid'], $excludes[$entity_type_id])) {
          unset($data[$category_index]['children'][$child_index]);
        }
      }
      // Rebase keys.
      $data[$category_index]['children'] = array_values($data[$category_index]['children']);
    }

    // Remove empty categories.
    foreach ($data as $category_index => $category) {
      if (empty($category['children'])) {
        unset($data[$category_index]);
      }
    }

    // Return the patched list with rebased keys.
    return array_values($data);
  }

  /**
   * Filter elements by permissions.
   *
   * @param $data
   * @param $entity_type_id
   *
   * @return mixed
   */
  private function filterByElementsPermissions($data) {

    $config = $this->config('cohesion.settings');
    $perms = ($config && $config->get('elements_permissions')) ? $config->get('elements_permissions') : "{}";
    $perms = Json::decode($perms);

    foreach ($data as $key => $element) {
      $perm = FALSE;
      foreach ($this->currentUser()->getRoles() as $role) {
        if (!isset($perms[$role][$element['uid']]) && $role != AccountInterface::ANONYMOUS_ROLE || isset($perms[$role][$element['uid']]) && $perms[$role][$element['uid']] == 1) {
          $perm = TRUE;
          break;
        }
      }
      if (!$perm) {
        unset($data[$key]);
      }
    }

    $data = array_values($data);

    return $data;
  }

  /**
   * Filter out disabled elements.
   *
   * @param $data
   * @return array
   */
  private function filterDisabledElements($data) {
    $config = $this->config('cohesion.settings');
    $elementsToggle = ($config && $config->get('element_toggle')) ? $config->get('element_toggle') : "{}";
    $elementsToggle = Json::decode($elementsToggle);

    foreach ($data as $key => $element) {
      $enabled = FALSE;
      if (!isset($elementsToggle[$element['uid']]) || isset($elementsToggle[$element['uid']]) && $elementsToggle[$element['uid']] == 1) {
        $enabled = TRUE;
      }

      if (!$enabled) {
        unset($data[$key]);
      }
    }

    $data = array_values($data);

    return $data;
  }

  /**
   * Access check for elementAction method.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   * @return \Drupal\Core\Access\AccessResultInterface
   */
  public function elementActionAccess(AccountInterface $account) {
    $group = $this->request->get('group');

    // Map groups to permissions.
    $map = [
      'elements' => 'access elements',
      'form_elements' => 'access fields',
    ];

    if (array_key_exists($group, $map)) {
      return AccessResult::allowedIfHasPermission($account, $map[$group]);
    }

    // Fallback to original _is_logged_in check if unknown group.
    return AccessResult::allowedIf($account->isAuthenticated());
  }

  /**
   * GET: /cohesionapi/element/{group}/{type}
   * Retrieve one or all elements from asset storage group. Endpoint.
   *
   * Request should include {group} and {type} parameters.
   * If {type} is omitted, it is assumed all assets of that group are desired.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   */
  public function elementAction(Request $request) {
    $group = ($request->get('group')) ? $request->get('group') : NULL;
    $assetLibrary = $this->keyValue('cohesion.assets.' . $group);
    $type = $request->get('type');
    $with_categories = $request->query->get('withcategories');
    $entity_type_id = $request->query->get('entityTypeId');
    $isCustom = $request->query->get('isCustom');

    // Check if it's custom component builder.
    $isComponentBuilder = FALSE;
    if ($entity_type_id === 'cohesion_component') {
      $isComponentBuilder = TRUE;
    }

    [$error, $data, $message] = \Drupal::service('settings.endpoint.utils')->getAssets($isComponentBuilder, $assetLibrary, $type, $group, $with_categories);

    if ($group == 'elements') {
      if ($with_categories) {
        foreach ($data['categories'] as $key => $category) {
          $children = $this->filterByElementsPermissions($category['children']);
          $children = $this->filterDisabledElements($children);
          if (empty($children)) {
            unset($data['categories'][$key]);
          }
          else {
            $filtered = $this->filterByElementsPermissions($category['children']);
            $data['categories'][$key]['children'] = $this->filterDisabledElements($filtered);
          }
        }
      }
      else {
        $filtered = $this->filterByElementsPermissions($data);
        $data = $this->filterDisabledElements($filtered);
      }

      // Filter the elements list by drupalSettings.cohesion.entityTypeId.
      if ($entity_type_id) {
        $data['categories'] = $this->filterByEntityTypeId($data['categories'], $entity_type_id);
      }

      // Filter the list of elements for the custom component form builder page.
      if ($entity_type_id == 'cohesion_component' && isset($isCustom) && $isCustom == TRUE) {
        $allowed_elements = \Drupal::keyValue('cohesion.assets.static_assets')
          ->get('custom-component-element-whitelist');

        foreach ($data['categories'] as $key => $category) {
          foreach ($category['children'] as $k => $child) {
            if (!in_array($child['uid'], $allowed_elements)) {
              unset($data['categories'][$key]['children'][$k]);
            }
          }
        }
      }
    }

    if ($group == 'form_elements') {
      // Filter the list of form elements (whitelist for style guide sidebar).
      if ($entity_type_id == 'cohesion_style_guide') {
        $form_elements = \Drupal::keyValue('cohesion.assets.static_assets')
          ->get('style-guide-form-element-whitelist');
        foreach ($data as $form_element_id => $form_element) {
          if (!in_array($form_element_id, $form_elements)) {
            unset($data[$form_element_id]);
          }
        }
      }

      // Filter the list of form elements (allowed for component sidebar).
      $allowed_list = ['cohesion_component', 'cohesion_helper'];
      if (in_array($entity_type_id, $allowed_list)) {
        $form_elements = \Drupal::keyValue('cohesion.assets.static_assets')
          ->get('component-form-element-blacklist');
        foreach ($data as $form_element_id => $form_element) {
          if (in_array($form_element_id, $form_elements)) {
            unset($data[$form_element_id]);
          }
        }
      }
    }

    // Show available categories.
    if (isset($data['categories']) && is_array($data['categories'])) {
      $data['categories'] = array_values($data['categories']);
    }

    // Return the (optionally) patched results.
    return new CohesionJsonResponse([
      'status' => !$error ? 'success' : 'error',
      'data' => $data,
    ]);
  }

  /**
   * GET: /cohesionapi/element
   * Return all key_value collections matching cohesion
   * (SELECT DISTINCT collection FROM key_value WHERE collection LIKE
   * "cohesion.assets.%";)
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   */
  public function elementActionAll(Request $request) {
    $data = [];

    foreach ($this->elementCollection() as $collection) {
      $base_name = str_replace('cohesion.assets.', '', $collection->collection);

      // Using the status /Drupal:keyValue here instead of the one from the
      // collection so the base collection can be modified in the loop.
      $assetLibrary = \Drupal::keyValue($collection->collection);
      [$error, $group_data, $message] = \Drupal::service('settings.endpoint.utils')->getAssets(FALSE, $assetLibrary, '__ALL__', $base_name, FALSE);

      // Patch in any custom element data.
      switch ($base_name) {
        case 'elements':
          $group_data = \Drupal::service('custom.elements')->patchElementList($group_data);
          break;

        case 'element_forms':
          $group_data = \Drupal::service('custom.elements')->patchElementBuilderForms($group_data);
          break;

        case 'form_defaults':
          $group_data = \Drupal::service('custom.elements')->patchFormDefaults($group_data);
          break;

        case 'element_properties':
          $group_data = \Drupal::service('custom.elements')->patchElementProperties($group_data);
          break;

        case 'property_group_options':
          $group_data = \Drupal::service('custom.elements')->patchProperyGroupOptions($group_data);
          break;

        case 'static_assets':
          $group_data['api-urls'] = \Drupal::service('custom.elements')->patchApiUrls($group_data['api-urls']);
          break;
      }

      // And finalize.
      if (!$error) {
        $data[$base_name] = $group_data;
      }
      else {
        // Reset data if error found.
        $data = [];
        break;
      }
    }
    // Send response.
    return new CohesionJsonResponse([
      'status' => !$error ? 'success' : 'error',
      'data' => $data,
    ]);
  }

  /**
   * @param bool $cron
   * @param bool $verbose
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|null
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public static function batch($cron = FALSE, $verbose = FALSE, $no_cache_clear = FALSE) {

    // Setup an event to be dispatched pre rebuild.
    $pre_event = new PreRebuildEvent();
    \Drupal::service('event_dispatcher')->dispatch($pre_event, SiteStudioEvents::PRE_REBUILD);
    \Drupal::logger('cohesion')->info('Rebuild operation started.');

    // Reset temporary template list.
    \Drupal::keyValue('cohesion.temporary_template')->set('temporary_templates', []);

    // Clean the scratch directory.
    // @fix - no error checking here?
    \Drupal::service('cohesion.local_files_manager')->resetScratchDirectory();

    // Set up the batch process array framework.
    $batch = [
      'title' => t('Rebuilding'),
      'operations' => [],
      'finished' => 'entity_rebuild_finished_callback',
      'error_message' => t('Site Studio rebuild has encountered an error.'),
      'file' => \Drupal::service('extension.path.resolver')->getPath('module', 'cohesion_website_settings') . '/cohesion_website_settings.batch.inc',
    ];

    // Process default element styles.
    $batch['operations'][] = [
      'cohesion_elements_get_elements_style_process_batch',
      [$verbose],
    ];

    $configs = \Drupal::entityTypeManager()->getDefinitions();

    // Make sure website settings are processed first.
    $style_configs = [
      'cohesion_scss_variable',
      'cohesion_color',
      'cohesion_icon_library',
      'cohesion_font_library',
      'cohesion_font_stack',
      'cohesion_website_settings',
      'cohesion_base_styles',
      'cohesion_custom_style',
      'cohesion_component_category',
      'cohesion_helper_category',
    ];

    $entity_update_manager = \Drupal::service('cohesion.entity_update_manager');
    // The number of entities processed by batch operation.
    $entity_to_process = Settings::get('rebuild_max_entity', 10);

    // A list of entity ids that can be processed at once.
    foreach ($style_configs as $style_config_type) {
      if (isset($configs[$style_config_type])) {
        // Get entity ids needing an Site Studio update.
        $entity_ids_needs_udpdate = \Drupal::entityTypeManager()
          ->getStorage($style_config_type)->getQuery()
          ->accessCheck(FALSE)
          ->condition('status', TRUE)
          ->condition('last_entity_update', $entity_update_manager->getLastPluginId(), '<>')
          ->execute();

        for ($i = 0; $i < count($entity_ids_needs_udpdate); $i += $entity_to_process) {
          $ids = array_slice($entity_ids_needs_udpdate, $i, $entity_to_process);
          $batch['operations'][] = [
            '_resave_config_entity',
            [$ids, $style_config_type, $verbose],
          ];
        }

        // Remove processed category config types from all configs.
        if (in_array($style_config_type, ['cohesion_component_category', 'cohesion_helper_category'])) {
          unset($configs[$style_config_type]);
          continue;
        }

        $entity_ids_no_udpdate = \Drupal::entityTypeManager()
          ->getStorage($style_config_type)->getQuery()
          ->accessCheck(FALSE)
          ->condition('status', TRUE)
          ->condition('id', $entity_ids_needs_udpdate, 'NOT IN')
          ->execute();

        $batch['operations'][] = [
          '_cohesion_styles_bulk_save',
          [$entity_ids_no_udpdate, $style_config_type, $verbose],
        ];

        // Remove processed config type from all configs.
        unset($configs[$style_config_type]);
      }
    }

    // Process all remaining Site Studio configuration entities. (components,
    // templates etc...)
    $search = 'cohesion_';
    $entity_list = '';
    foreach ($configs as $entity_type_name => $entity_type) {
      if ($entity_type instanceof ConfigEntityTypeInterface && substr($entity_type_name, 0, strlen($search)) === $search) {
        try {
          $entity_ids = \Drupal::entityTypeManager()
            ->getStorage($entity_type_name)
            ->getQuery()
            ->accessCheck(FALSE)
            ->condition('modified', TRUE)
            ->execute();

          for ($i = 0; $i < count($entity_ids); $i += $entity_to_process) {
            $ids = array_slice($entity_ids, $i, $entity_to_process);
            $batch['operations'][] = [
              '_resave_config_entity',
              [$ids, $entity_type_name, $verbose],
            ];
          }

          unset($entity_list);

        }
        catch (\Exception $e) {

        }
      }
    }

    // Save all "cohesion_layout" content entities.
    $query = \Drupal::entityQuery('cohesion_layout')->accessCheck(FALSE);
    $entity_ids = $query->execute();
    for ($i = 0; $i < count($entity_ids); $i += $entity_to_process) {
      $ids = array_slice($entity_ids, $i, $entity_to_process);
      $batch['operations'][] = [
        '_resave_cohesion_layout_entity',
        [$ids, $verbose],
      ];
    }

    // Rebuild the views usage.
    $batch['operations'][] = [
      '_rebuild_views_usage', [$verbose],
    ];

    // Add .htaccess to twig template directory.
    $batch['operations'][] = [
      'cohesion_templates_secure_directory', [$verbose],
    ];

    // Move temp to live.
    $batch['operations'][] = [
      'entity_rebuild_temp_to_live', [$verbose],
    ];

    if(!$no_cache_clear) {
      $batch['operations'][] = [
        'batch_drupal_flush_all_caches', [$verbose],
      ];
    }

    // Carry on!
    if ($cron) {
      return $batch;
    }
    else {
      // Run the batch process.
      batch_set($batch);
      return batch_process(Url::fromRoute('cohesion.configuration.account_settings')->toString());
    }
  }

  /**
   * @return array collection of Site Studio elements
   */
  private function elementCollection() {
    try {
      return \Drupal::database()->select('key_value', 'chc')->fields('chc', ['collection'])->condition('chc.collection', 'cohesion.assets.%', 'LIKE')->groupBy('chc.collection')->execute()->fetchAll();
    }
    catch (\Exception $ex) {
      Error::logException('cohesion', $ex);
    }

    return [];
  }

}
