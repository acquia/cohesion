<?php

namespace Drupal\cohesion\Controller;

use Drupal\cohesion\CohesionJsonResponse;
use Drupal\cohesion\Plugin\DX8JsonFormUtils;
use Drupal\cohesion\Services\CohesionEndpointHelper;
use Drupal\cohesion_elements\Controller\ElementsController;
use Drupal\cohesion_elements\CustomComponentsService;
use Drupal\cohesion_elements\Entity\CohesionElementEntityBase;
use Drupal\cohesion_elements\Entity\Component;
use Drupal\Component\Serialization\Json;
use Drupal\cohesion\Services\CohesionUtils;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Field\FieldConfigInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Utility\Error;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\file\FileRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\File\FileExists;

/**
 * Class CohesionEndpointController.
 *
 * Returns Drupal data to Angular (views, blocks, node lists, etc).
 * See function index() for the entry point.
 *
 * @package Drupal\cohesion\Controller
 */
class CohesionEndpointController extends ControllerBase {

  /**
   *
   * @var mixed
   */
  protected $helper;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The entity type bundle info.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * Look up entities by type/uuid.
   *
   * @var \Drupal\Core\Entity\EntityRepository
   */
  protected $entityRepository;

  /**
   * Cohesion utils service.
   *
   * @var \Drupal\cohesion\Services\CohesionUtils
   */
  protected $cohesionUtils;

  /**
   * Custom Components Service.
   *
   * @var \Drupal\cohesion_elements\CustomComponentsService
   */
  protected $customComponentsService;

  /**
   * Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $user;

  /**
   * File Url Generator service.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * Drupal file repository service.
   *
   * @var \Drupal\file\FileRepositoryInterface
   */
  protected $fileRepository;

  /**
   * CohesionEndpointController constructor.
   *
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   Entity Field Manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   Entity Type Bundle Info.
   * @param \Drupal\cohesion\Services\CohesionUtils $cohesion_utils
   *   Cohesion Utils service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   Request Stack.
   * @param \Drupal\cohesion_elements\CustomComponentsService
   *   Custom Components Service.
   * @param \Drupal\Core\Session\AccountInterface $user
   *   Current user.
   * @param \Drupal\file\FileRepositoryInterface $fileRepository
   *   File Repository.
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $fileUrlGenerator
   *   File Url Generator service.
   */
  public function __construct(
    EntityFieldManagerInterface $entity_field_manager,
    EntityTypeBundleInfoInterface $entity_type_bundle_info,
    EntityRepository $entity_repository,
    CohesionUtils $cohesion_utils,
    RequestStack $requestStack,
    CustomComponentsService $customComponentsService,
    AccountInterface $user,
    FileRepositoryInterface $fileRepository,
    FileUrlGeneratorInterface $fileUrlGenerator,
  ) {
    $this->entityFieldManager = $entity_field_manager;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
    $this->entityRepository = $entity_repository;
    $this->cohesionUtils = $cohesion_utils;
    $this->customComponentsService = $customComponentsService;
    $this->request = $requestStack->getCurrentRequest();
    $this->user = $user;
    $this->fileUrlGenerator = $fileUrlGenerator;
    $this->fileRepository = $fileRepository;

    $this->helper = new CohesionEndpointHelper($this->user);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_field.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('entity.repository'),
      $container->get('cohesion.utils'),
      $container->get('request_stack'),
      $container->get('custom.components'),
      $container->get('current_user'),
      $container->get('file.repository'),
      $container->get('file_url_generator')
    );
  }

  /**
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getBreakPointColumns(Request $request) {
    $results = [];
    /** @var \Drupal\cohesion_website_settings\Entity\WebsiteSettings $entity */
    $entity = $this->entityTypeManager()->getStorage('cohesion_website_settings')->load('responsive_grid_settings');

    $json_values = ($entity) ? $entity->getDecodedJsonValues() : [];

    if ($json_values && array_key_exists('columns', $json_values)) {
      $columns = (int) $json_values['columns'];
      for ($i = 0; $i < $columns + 1; $i++) {
        if ($i == 0) {
          if (\Drupal::routeMatch()->getRouteName() == 'drupal_data_endpoint.columns_push_pull_offset') {
            $results[] = [
              'label' => 'None',
            // This renders: coh-hidden-{$bp}.
              'value' => -1,
            ];
          }
          else {
            $results[] = [
              'label' => 'None (hidden)',
            // This renders: coh-hidden-{$bp}.
              'value' => -1,
            ];

            $results[] = [
              'label' => 'Undefined (expands to available width)',
            // This renders: coh-col-{$bp}.
              'value' => -2,
            ];

            $results[] = [
              'label' => 'Auto (content width)',
            // This renders: coh-col-{$bp}-auto.
              'value' => -3,
            ];
          }
        }
        else {
          $results[] = [
            'label' => $i,
            'value' => $i,
          ];
        }
      }
      $results[] = ['label' => '1/5th', 'value' => 1.5];
    }

    $error = empty($results) ? TRUE : FALSE;

    return new CohesionJsonResponse([
      'status' => !$error ? 'success' : 'error',
      'data' => $results,
    ]);
  }

  /**
   * Custom access check for the ElementGroupInfo route
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result
   */
  public function getElementGroupInfoAccess(AccountInterface $account) {

    // Map entity types to permissions.
    $map = [
      'cohesion_helper' => 'access helpers',
      'cohesion_component' => 'access components',
    ];

    $entity_type = $this->request->query->get('entity_type');
    if (array_key_exists($entity_type, $map)) {
      return AccessResult::allowedIfHasPermission($account, $map[$entity_type]);
    }

    // Fallback to original _is_logged_in check if unknown entity.
    return AccessResult::allowedIf($account->isAuthenticated());
  }

  /**
   * Get information about grouped elements, i.e. helpers & components.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getElementGroupInfo(Request $request) {

    // Get the entity type from the request.
    $entity_type = ($request->query->get('entity_type')) ?: 'cohesion_helper';
    $exclude_path = ($request->query->get('componentPath')) ?: FALSE;
    $type_access = ($request->query->get('entity_type_access')) ?: 'all';
    $bundle_access = ($request->query->get('bundle_access')) ?: 'all';
    $element_id = ($request->query->get('element_id')) ?: '';
    $access_elements = ($request->query->get('access_elements')) === 'false' ? FALSE : TRUE;
    $isCustomComponentBuilder = $request->query->get('custom_component_builder', FALSE);

    // Get list of categories relating to the element type.
    $type_map = [
      'cohesion_helper' => 'cohesion_helper_category',
      'cohesion_component' => 'cohesion_component_category',
    ];

    $categories = ElementsController::getElementCategories($type_map[$entity_type], \Drupal::currentUser()->hasPermission('administer components'));
    $element_categories = [];

    // Filter categories based on Site studio access permissions.
    foreach ($categories as $value => $category) {
      $element_categories[$value] = [
        'id' => $category['id'],
        'label' => $category['label'],
        'value' => $category['class'],
        'children' => [],
      // This is redundant.
        'dx8_access' => TRUE,
      ];
    }

    // Get list of entities matching the specified type.
    $storage = $this->entityTypeManager()->getStorage($entity_type);
    $query = $storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('status', TRUE)
      ->condition('selectable', TRUE)
      ->sort('category', 'asc')
      ->sort('label', 'asc')
      ->sort('weight', 'asc');
    $ids = $query->execute();

    // Do we need to exclude a component?
    if ($exclude_path) {
      /** @var \Drupal\Core\Url $url_object **/
      if ($url_object = \Drupal::service('path.validator')->getUrlIfValid($exclude_path)) {
        $route_parameters = $url_object->getrouteParameters();

        if (isset($route_parameters['cohesion_component']) && isset($ids[$route_parameters['cohesion_component']])) {
          unset($ids[$route_parameters['cohesion_component']]);
        }
      }
    }

    // Load the entities.
    $entities = $storage->loadMultiple($ids);
    foreach ($entities as $entity) {

      if ($this->helperAccessFilter($entity, $access_elements, $isCustomComponentBuilder)) {
        continue;
      }

      if (!$this->componentListFilter($entity, $type_access, $bundle_access)) {
        continue;
      }

      // If element_id exists then load the component entity.
      if ($element_id) {
        $entity = Component::load($element_id);

        // If no entity then it must be a custom component.
        if (!$entity) {
          // Get the custom component.
          $custom_component = $this->customComponentsService->getComponent($element_id);
          // Format as a component.
          $entity = $this->customComponentsService->formatAsComponent($custom_component);
        }
      }

      $element = $this->createElementArray($entity, $entity_type);

      // Add the preview image if defined.
      if ($preview_image = $entity->get('preview_image')) {
        $element['preview_image'] = [
          'id' => $preview_image,
        ];
      }

      // Add component ID.
      if ('cohesion_component' === $entity_type) {
        $element['componentId'] = $entity->id();
        // Remove jsons to prevent it form being display.
      }

      // Get type of top level element in components/helpers.
      if ('cohesion_component' === $entity_type || 'cohesion_helper' === $entity_type) {
        unset($element['json_values']);
        unset($element['json_mapper']);
        $top_type = $entity->getTopType();

        if ($top_type !== FALSE) {
          if ('cohesion_component' === $entity_type) {
            $element['componentType'] = $top_type;
          }
          elseif ('cohesion_helper' === $entity_type) {
            $element['helperType'] = $top_type;
          }
        }
      }

      // Get the preview_image URL.
      $element = $this->getPreviewImage($element, $entity_type, $entity);

      // Set the category as the class name.
      // Ignore if user does not have access to this category.
      if (isset($categories) && isset($categories[$element['category']])) {
        $element['category'] = $categories[$element['category']]['class'];
        // Add the element as a child of the category.
        if (isset($element_categories[$entity->get('category')]['dx8_access'])) {
          $element_categories[$entity->get('category')]['children'][] = $element;
        }
      }
    }

    if ($entity_type === 'cohesion_component') {
      try {
        $this->customComponentsService->patchComponentList($element_categories, $type_access, $bundle_access);
      }
      catch (\Exception $exception) {
        $this->getLogger('cohesion_elements.custom_components')->error(
          $exception->getMessage(),
          Error::decodeException($exception)
        );

        return new CohesionJsonResponse([
          'status' => 'error',
          'data' => ['error' => $exception->getMessage()],
        ], 500);
      }
    }

    // Clean out the categories with no children.
    $element_categories_formatted = $element_categories;

    foreach ($element_categories as $id => $category) {
      if (count($category['children'])) {
        $element_categories_formatted[$id] = $category;
      }
    }

    // If element is not set, but we have an element id it could be a
    // custom component when creating component content.
    if (!isset($element) && $element_id) {
      // Get the custom component.
      if ($custom_component = $this->customComponentsService->getComponent($element_id)) {
        // Format as a component.
        $entity = $this->customComponentsService->formatAsComponent($custom_component);
        // Create element array.
        $element = $this->createElementArray($entity, $entity_type);
        // Get the preview_image URL & set.
        $element = $this->getPreviewImage($element, $entity_type, $entity);
        // Set some other things.
        $element['componentId'] = $entity->id();
        $element['category'] = $categories[$element['category']]['class'];
        // Unset values we don't need.
        unset($element['json_values']);
        unset($element['json_mapper']);
      }
    }

    // If element_id is set we only want to return that one component.
    if ($element_id) {
      $data = $element;
    }
    else {
      // Return the data.
      $data = [
        'categories' => array_values($element_categories_formatted),
      ];
    }

    $error = !empty($data) ? FALSE : TRUE;
    return new CohesionJsonResponse([
      'status' => !$error ? 'success' : 'error',
      'data' => $data,
    ]);
  }

  /**
   * Create an array of element data for the sidebar browser / component content
   * creation form.
   *
   * @param $entity
   * @param $entity_type
   *
   * @return array
   */
  private function createElementArray($entity, $entity_type) {

    $element = [
      'uid' => $entity->id(),
      'type' => str_replace('cohesion_', '', $entity_type),
      'title' => $entity->label(),
      'enabled' => $entity->get('status'),
      'category' => $entity->get('category'),
      'json_values' => $entity->get('json_values'),
      'json_mapper' => $entity->get('json_mapper'),
    ];

    return $element;
  }

  /**
   * Get the preview image of the element.
   *
   * @param $element
   * @param $entity_type
   * @param $entity
   * @return array
   */
  private function getPreviewImage($element, $entity_type, $entity) {
    if ($entity_type && $entity->id()) {
      $element['preview_image']['url'] = ElementsController::getElementPreviewImageURL($entity_type, $entity->id());
    } else {
      $element['preview_image']['url'] = FALSE;
    }

    return $element;
  }

  /**
   * Get component JSON form values.
   *
   * @param $uid
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getComponent($uid) {
    // Get the uid of the component from the request.
    $data = [];
    // Load the component.
    if (isset($uid) && ($component_entity = $this->entityTypeManager()->getStorage('cohesion_component')->load($uid))) {
      // Return the json data.
      $data = $component_entity->getDecodedJsonValues();
    }

    try {
      $components = $this->customComponentsService->getComponents();
    }
    catch (\Exception $exception) {
      $this->getLogger('cohesion_elements.custom_components')->error(
        $exception->getMessage(),
        Error::decodeException($exception)
      );

      return new CohesionJsonResponse([
        'status' => 'error',
        'data' => ['error' => $exception->getMessage()],
      ], 500);
    }

    if (isset($components[$uid])) {
      $data = $components[$uid]['form']->getJsonValuesDecodedArray();
    }

    $error = !empty($data) ? FALSE : TRUE;
    return new CohesionJsonResponse([
      'status' => !$error ? 'success' : 'error',
      'data' => $data,
    ]);
  }

  /**
   * Get component JSON form values for specific component form field.
   *
   * @param string $uid
   *   Component UID.
   * @param string $fieldUuid
   *   Component field UUID.
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getComponentFieldFormModel(string $uid, string $fieldUuid) {
    $fieldFormModel = [];
    // Load the component.
    if (isset($uid) && ($component_entity = $this->entityTypeManager()->getStorage('cohesion_component')->load($uid))) {
      // Return the json data.
      $data = $component_entity->getDecodedJsonValues();

      // We only care about componentForm and model for specific field.
      foreach ($data['componentForm'] as $formField) {
        if ($formField['uuid'] === $fieldUuid) {
          $fieldFormModel['componentForm'] = $formField;
        }
      }
      if (isset($data['model'][$fieldUuid])) {
        $fieldFormModel['model'] = $data['model'][$fieldUuid];
      }
    }

    $error = !empty($fieldFormModel) ? FALSE : TRUE;
    return new CohesionJsonResponse([
      'status' => !$error ? 'success' : 'error',
      'data' => $fieldFormModel,
    ]);
  }

  /**
   * Get helper JSON form values.
   *
   * @param $uid
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getCanvasUpdateHelper($uid) {
    // Load the helper.
    if (isset($uid) && ($helper = $this->entityTypeManager()->getStorage('cohesion_helper')->load($uid))) {
      if($payload = $this->cohesionUtils->getPayloadForLayoutCanvasDataMerge($helper)) {
        $response = \Drupal::service('cohesion.api_client')->layoutCanvasDataMerge($payload);

        if ($response && $response['code'] == 200) {
          return new CohesionJsonResponse([
            'status' => 'success',
            'data' => $response['data'],
          ]);
        }
        else {
          return new CohesionJsonResponse([
            'status' => 'success',
            'data' => ['error' => $response['error']],
          ]);
        }
      } else {
        return new CohesionJsonResponse([
          'status' => 'success',
          'data' => [
            'layoutCanvas' => $helper->getDecodedJsonValues(TRUE),
          ],
        ]);
      }
    }

    return new CohesionJsonResponse([
      'status' => 'error',
      'data' => ['error' => t('entity not found')],
    ], 404);
  }

  /**
   * GET: /cohesionapi/cohesion-components
   * Get component JSON form values.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function getComponents(Request $request) {
    $components = [];
    $custom_components = [];
    // Get the uid of the component from the request.
    $request_uids = $request->query->get('uids');
    $uids = explode(',', $request_uids);

    if (is_array($uids)) {
      foreach ($uids as $uid) {
        if ($component_entity = $this->entityTypeManager()->getStorage('cohesion_component')->load($uid)) {
          /** @var \Drupal\cohesion_elements\Entity\Component $component_entity */
          // Return the json data.
          $components[$uid] = array_merge([
            'title' => $component_entity->get('label'),
            'category' => $component_entity->getCategoryEntity() ? $component_entity->getCategoryEntity()->getClass() : FALSE,
          ], $component_entity->getDecodedJsonValues());
        }
        else {
          if (empty($custom_components)) {
            try {
              $custom_components = $this->customComponentsService->getComponents();
            }      catch (\Exception $exception) {
              $this->getLogger('cohesion_elements.custom_components')->error(
                $exception->getMessage(),
                Error::decodeException($exception)
              );

              return new CohesionJsonResponse([
                'status' => 'error',
                'data' => ['error' => $exception->getMessage()],
              ], 500);
            }
          }

          if (array_key_exists($uid, $custom_components)) {
            $form = $custom_components[$uid]['form']->getJsonValuesDecodedArray();
            $components[$uid] = array_merge([
              'title' => $custom_components[$uid]['title'],
              'category' => $custom_components[$uid]['category'],
            ], $form);
          }
        }
      }
    }

    return new CohesionJsonResponse([
      'status' => empty($components) ? 'error' : 'success',
      'data' => $components,
    ]);
  }

  /**
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   */
  public function getEntityFields(Request $request) {
    $entity_type = $request->attributes->get('entity_type') ?: NULL;
    $entity_bundle = $request->attributes->get('entity_bundle') ?: NULL;
    $data = [];

    if ($entity_type !== '__none__') {
      $entity_types_list = [];
      // Set up the list of entities.
      if ($entity_type === '__any__') {
        // Use all entities that have bundles.
        foreach ($this->entityTypeManager()->getDefinitions() as $entity_type_key => $entity_type) {
          $bundle_entity_type = $entity_type->getBundleEntityType();
          if ($bundle_entity_type) {
            $entity_types_list[] = $entity_type_key;
          }
        }
      }
      else {
        // Use just the given entity.
        $entity_types_list[] = $entity_type;
      }

      try {
        // Loop through the list of entity types.
        foreach ($entity_types_list as $entity_type) {
          $fields = [];
          $extra_fields = [];
          if (is_null($entity_bundle) || $entity_bundle === '__any__') {
            $bundles = $this->entityTypeBundleInfo->getBundleInfo($entity_type);
            foreach ($bundles as $bundle_id => $bundle) {
              $fields = array_merge($fields, $this->entityFieldManager->getFieldDefinitions($entity_type, $bundle_id));
              $extra_fields = array_merge($extra_fields, $this->entityFieldManager->getExtraFields($entity_type, $bundle_id));
            }
          }
          else {
            $fields = array_merge($fields, $this->entityFieldManager->getFieldDefinitions($entity_type, $entity_bundle));
            $extra_fields = array_merge($extra_fields, $this->entityFieldManager->getExtraFields($entity_type, $entity_bundle));
          }

          // Loop through the configurable field entities.
          $variable = "content";
          foreach ($fields as $field) {
            if (FieldStorageConfig::loadByName($entity_type, $field->getName()) && $field instanceof FieldConfigInterface) {
              // Exclude the layout builder section config.
              if ($field->getType() !== 'layout_section') {

                $this->moduleHandler()->alter('dx8_' . $entity_type . '_drupal_field_prefix', $variable);

                $data[] = [
                  'value' => $variable . '.' . $field->getName(),
                  'name' => $field->label(),
                ];
              }
            }
          }

          if (isset($extra_fields['display']) && is_array($extra_fields['display'])) {
            foreach ($extra_fields['display'] as $name => $extra_field) {
              $data[] = [
                'value' => $variable . '.' . $name,
                'name' => $extra_field['label'],
              ];
            }
          }

          // Alter the list of variables available for this entity type.
          $this->moduleHandler()->alter('dx8_' . $entity_type . '_drupal_field_variable', $data);

          $this->moduleHandler()->alter('dx8_' . $entity_type . '_' . $entity_bundle . '_drupal_field_variable', $data);
        }
      }
      catch (\Exception $ex) {
        \Drupal::logger('cohesion')->error($ex);
        return new CohesionJsonResponse([
          'status' => 'error',
          'data' => ['error' => t('Entity :entity_type does not exist', [':entity_type' => $entity_type])],
        ], 400);
      }
    }

    // Alter the list of variables available.
    $this->moduleHandler()->alter('dx8_drupal_field_variable', $data);

    $error = !empty($data) ? FALSE : TRUE;
    return new CohesionJsonResponse([
      'status' => !$error ? 'success' : 'error',
      'data' => $data,
    ]);
  }

  /**
   * Save an element given the provided details.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function elementSave(Request $request) {
    $content_raw = $request->getContent();
    $content = Json::decode($content_raw);
    // New element data.
    $values = [
      'label' => $content['label'],
      'category' => $content['category'],
      'status' => $content['status'],
      'preview_image' => $content['preview_image'] ?? FALSE,
      'json_values' => $content['json_values'],
      'selectable' => TRUE,
      'modified' => TRUE,
    ];
    // Save the element.
    [$error, $message] = $this->helper->saveElement($values, $content);

    return new CohesionJsonResponse([
      'status' => $error ? 'error' : 'success',
      'data' => ['error' => $error ? $message : FALSE],
    ], ($error ? 400 : 200));
  }

  /**
   * This is an endpoint to retrieve all select options from JSON forms.
   * Note: Any changes made to the structure of the JSON forms must be
   * accounted for in this method.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   */
  public function getSelectOptions(Request $request) {
    $dx8_form_utils = new DX8JsonFormUtils();
    $data = $dx8_form_utils->loadDX8FormSelectItems();
    $content = Json::decode($request->getContent());

    $type = $content['type'] ?? NULL;
    $item_id = $content['itemID'] ?? NULL;
    $field_group_id = $content['fieldGroupId'] ?? NULL;
    $field_id = $content['fieldId'] ?? NULL;

    $type = $request->query->get('type') ?: $type;
    $item_id = $request->query->get('itemID') ?: $item_id;
    $field_group_id = $request->query->get('fieldGroupId') ?: $field_group_id;
    $field_id = $request->query->get('fieldId') ?: $field_id;

    if (!is_null($type) && !is_null($item_id) && !is_null($field_group_id) && !is_null($field_id)) {
      $results = $data[$type]['options'][$item_id]['options'][$field_group_id]['options'][$field_id] ?? NULL;

      if (in_array(strtolower($type), [
        'context_visibility',
        'settings',
        'styles',
      ]) && $results) {
        $data = $results;
      }
    }
    $error = empty($data) ? TRUE : FALSE;
    return new CohesionJsonResponse([
      'status' => $error ? 'error' : 'success',
      'data' => $data,
    ]);
  }

  /**
   * Return TRUE if list should filter helper if helper canvas contains any
   * elements.
   *
   * @param \Drupal\cohesion_elements\Entity\CohesionElementEntityBase $entity
   * @param int $access_elements
   * @param bool $isCustomComponentBuilder
   *
   * @return bool
   */
  private function helperAccessFilter(CohesionElementEntityBase $entity, $access_elements, $isCustomComponentBuilder) {
    // Do we need to search the helper for elements?
    if ($entity->getEntityTypeId() == 'cohesion_helper') {
      if ($isCustomComponentBuilder) {
        // If not a form helper then filter out on the custom component builder.
        return !$entity->getLayoutCanvasInstance()->isFormHelper();
      }

      if ($access_elements == FALSE) {
        // Helper contains elements, so should be filtered out.
        return $entity->getLayoutCanvasInstance()->hasElements();
      }
    }

    return FALSE;
  }

  /**
   * Filters list based on Component availability settings.
   *
   * @param \Drupal\cohesion_elements\Entity\CohesionElementEntityBase $entity
   * @param string $type_access
   * @param string $bundle_access
   *
   * @return bool
   */
  protected function componentListFilter(CohesionElementEntityBase $entity, $type_access = NULL, $bundle_access = NULL) {
    if (method_exists($entity, 'getAvailabilityData')) {
      [$types, $bundles] = $entity->getAvailabilityData();
      if (!(in_array($type_access, $types) && in_array($bundle_access, $bundles)) && !(empty($bundles) && empty($types)) && !($type_access == 'all' || $bundle_access == 'all') && !(in_array($type_access, $types) && empty($bundles))) {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Given an entity reference/field string, return the URI for the Angular
   * image preview.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   */
  public function getEntityPreviewPath(Request $request) {
    $reference = $request->attributes->get('reference') ?: NULL;

    if ($image = \Drupal::service('cohesion_image_browser.update_manager')->getPreviewThumbnail($reference)) {
      if ($image['path']) {
        $image['path'] = $this->fileUrlGenerator->generateAbsoluteString($image['path']);
      }

      // Decoded the token, found the entity and extracted the image path.
      return new CohesionJsonResponse($image);
    }
    else {
      // Something went wrong.
      return new CohesionJsonResponse([
        'data' => t('Media entity not found.'),
      ], 404);
    }
  }

  /**
   * Get entity reference string AND preview URI from URI.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function getUriToEntityReference(Request $request) {
    $file = NULL;
    $uri = $request->query->get('uri') ?: NULL;

    if ($uri) {
      // Attempt to load the file entity.
      if ($files = $this->entityTypeManager()->getStorage('file')->loadByProperties(['uri' => $uri])) {
        $file = reset($files);
      }

      // File entity doesn't exist, but file does - create it.
      if (empty($files) && file_exists($uri)) {
        $contents = file_get_contents($uri);
        /** @var \Drupal\file\Entity\File $file */
        $file = $this->fileRepository->writeData($contents, $uri, FileExists::Replace);
        $file->setPermanent();
        $file->save();
      }

      try {
        /** @var \Drupal\file\FileInterface[] $files */
        if ($file) {
          // Return the reference string and preview uri.
          return new CohesionJsonResponse([
            'data' => [
              'reference' => '[media-reference:file:' . $file->uuid() . ']',
              'preview' => $this->fileUrlGenerator->generateAbsoluteString($file->getFileUri()),
            ],
          ]);

        }
      }
      catch (\Exception $e) {
        // Catch error and return the 404 below.
      }
    }

    // Something went wrong.
    return new CohesionJsonResponse([
      'data' => t('File entity not found.'),
    ], 404);
  }

  /**
   * Get the list of categories for the save element modal.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   */
  public function getCategories(Request $request) {
    $type = $request->attributes->get('element_type') ?: NULL;

    if ($type == 'helper') {
      $type = 'cohesion_helper_category';
    }
    else {
      $type = 'cohesion_component_category';
    }

    $categories = [];
    foreach (ElementsController::getElementCategories($type) as $category) {
      // Value => label.
      $categories[] = [
        'value' => $category['id'],
        'label' => $category['label'],
      ];
    }

    return new CohesionJsonResponse([
      'data' => $categories,
    ]);
  }

  /**
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getColorTags(Request $request) {
    $storage = $this->entityTypeManager()->getStorage('cohesion_color');
    $list = [];

    if ($entities = $storage->loadMultiple()) {
      foreach ($entities as $entity) {
        $model = $entity->getDecodedJsonValues();

        if (isset($model['tags'])) {
          $list = array_merge($list, $model['tags']);
        }
      }
    }

    $list = array_values(array_unique($list, SORT_REGULAR));

    return new CohesionJsonResponse([
      'data' => $list,
    ]);
  }

}
