<?php

namespace Drupal\cohesion_elements\Controller;

use Drupal\cohesion\CohesionJsonResponse;
use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\cohesion_elements\Entity\CohesionLayout;
use Drupal\cohesion_elements\Entity\Component;
use Drupal\cohesion_elements\Entity\ComponentContent;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CohesionEndpointController.
 *
 * Returns Drupal data to Angular (views, blocks, node lists, etc).
 * See function index() for the entry point.
 *
 * @package Drupal\cohesion\Controller
 */
class ComponentContentController extends ControllerBase {

  /**
   * This is an endpoint to retrieve all instances of a global component.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function getComponentContents(Request $request) {

    $storage = $this->entityTypeManager()->getStorage('component_content');
    $query = $storage->getQuery()->condition('status', TRUE)->sort('title', 'asc');
    $exclude_path = ($request->query->get('componentPath')) ?: FALSE;
    $exclude_component_content = FALSE;

    if ($exclude_path) {
      /** @var \Drupal\Core\Url $url_object */
      if ($url_object = \Drupal::service('path.validator')->getUrlIfValid($exclude_path)) {
        $route_parameters = $url_object->getrouteParameters();

        if (isset($route_parameters['component_content'])) {
          $exclude_component_content = $route_parameters['component_content'];
        }
      }
    }

    $ids = $query->execute();
    $component_contents = $storage->loadMultiple($ids);
    $data = [];

    foreach ($component_contents as $component_content) {
      if ($component_content->id() === $exclude_component_content) {
        continue;
      }
      /** @var \Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem $component_field */
      $component_field = $component_content->get('component')->first();
      if ($component_field) {
        /** @var \Drupal\Core\Entity\Plugin\DataType\EntityReference $entityReference */
        $entityReference = $component_field->get('entity');
        /** @var \Drupal\Core\Entity\Plugin\DataType\EntityAdapter $entityAdapter */
        $entityAdapter = $entityReference->getTarget();

        // Component content entity exists, but no component config.
        if (!$entityAdapter) {
          continue;
        }

        /** @var \Drupal\cohesion_elements\Entity\Component $component */
        $component = $entityAdapter->getValue();

        if (!isset($data[$component->id()])) {
          $data[$component->id()] = [
            'id' => $component->id(),
            'label' => $component->label(),
            'children' => [],
          ];
        }

        // Calculate the preview image.
        if (!is_array($component->get('preview_image')) && !empty($component->get('preview_image'))) {
          try {
            $preview_image = [
              'id' => $component->get('preview_image'),
              'url' => ElementsController::getElementPreviewImageURL('cohesion_component', $component->id()),
            ];
          }
          catch (\Exception $e) {
            $preview_image = ['url' => FALSE];
          }
        }
        else {
          $preview_image = ['url' => FALSE];
        }

        $json_values = $component->getDecodedJsonValues();
        $top_type = FALSE;

        if (isset($json_values['canvas'])) {
          foreach ($json_values['canvas'] as $top_level_element) {
            if (isset($top_level_element['uid'])) {
              if ($top_type === FALSE) {
                $top_type = $top_level_element['uid'];
              }
              elseif ($top_type !== $top_level_element['uid']) {
                $top_type = 'misc';
              }
            }
          }
        }

        // Populate component content details.
        /** @var \Drupal\cohesion_elements\Entity\ElementCategoryInterface $category_entity */
        $category_entity = $component->getCategoryEntity();

        $data[$component->id()]['children'][] = [
          'title' => $component_content->label(),
          'type' => 'component-content',
          'componentContentId' => 'cc_' . $component_content->id(),
          'uid' => 'cc_' . $component_content->id(),
          'componentId' => $component->id(),
          'category' => $category_entity ? $category_entity->getClass() : FALSE,
          'componentType' => $top_type,
          'preview_image' => $preview_image,
          'url' => $component_content->toUrl('edit-form')->toString(),
        ];
      }
    }

    $data = array_values($data);

    return new CohesionJsonResponse([
      'status' => 'success',
      'data' => $data,
    ]);

  }

  /**
   * GET: /cohesionapi/component-contents
   * Get component JSON form values.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   */
  public function getComponentContentsByIds(Request $request) {
    $components = [];
    // Get the uid of the component from the request.
    $uids = $request->query->get('ids');
    if (is_array($uids)) {
      foreach ($uids as $uid) {
        $id = str_replace('cc_', '', $uid);
        if ($component_content = ComponentContent::load($id)) {
          $category_entity = $component_content->getComponent()->getCategoryEntity();

          $components[$uid] = array_merge([
            'title' => $component_content->label(),
            'url' => $component_content->toUrl('edit-form')->toString(),
            'category' => $category_entity ? $category_entity->getClass() : FALSE,
          ]);
        }
      }
    }

    $error = !empty($components) ? FALSE : TRUE;
    return new CohesionJsonResponse([
      'status' => !$error ? 'success' : 'error',
      'data' => $components,
    ]);
  }

  /**
   * Save a component as component content.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   */
  public function save(Request $request) {

    $content_raw = $request->getContent();
    $content = json_decode($content_raw);

    if (property_exists($content, 'canvas') && property_exists($content, 'model')) {
      $layout_canvas = new LayoutCanvas($content_raw);
      $elements = $layout_canvas->getCanvasElements();
      // Make sure the canvas contains only one top level element and this element is a component.
      if (count($elements) == 1 && $elements[0]->isComponent() && $elements[0]->getModel()) {
        $element = $elements[0];
        if ($componentEntity = Component::load($element->getComponentID())) {
          // Load the component used for this component content
          // Get component content from model if it has been changed, from the element otherwise.
          $component_name = $element->getModel()->getProperty(['settings', 'title']) ? $element->getModel()->getProperty(['settings', 'title']) : $element->getProperty('title');

          // Create a new component content.
          $component_content = ComponentContent::create([
            'title' => $component_name,
            'component' => $componentEntity,
          ]);

          $layout = CohesionLayout::create([
            'json_values' => $content_raw,
            'parent_type' => $component_content->getEntityTypeId(),
            'parent_field_name' => 'field_dx8_component',
          ]);

          $component_content->set('layout_canvas', $layout);
          $component_content->setPublished();
          $component_content->save();

          return new CohesionJsonResponse([
            'status' => 'success',
            'data' => [
              'componentId' => 'cc_' . $component_content->id(),
              'title' => $component_content->label(),
              'url' => $component_content->toUrl('edit-form')->toString(),
            ],
          ]);
        }
      }
    }

    return new CohesionJsonResponse([
      'status' => 'error',
      'data' => ['error' => t('Bad request')],
    ], 400);
  }

  /**
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function addPage() {
    $build['#attached']['library'][] = 'cohesion/cohesion-list-builder-sort';

    // Get complete list of component content.
    $entityType = $this->entityTypeManager()->getStorage('cohesion_component')->getEntityType();

    $reflector = new \ReflectionClass($entityType->getClass());
    $category_type_id = $reflector->getConstant('CATEGORY_ENTITY_TYPE_ID');

    $categories_query = $this->entityTypeManager->getStorage($category_type_id)->getQuery()->sort('weight', 'asc');

    if ($categories = $this->entityTypeManager->getStorage($category_type_id)->loadMultiple($categories_query->execute())) {
      foreach ($categories as $category) {

        $query = $this->entityTypeManager->getStorage($entityType->id())->getQuery()->condition('category', $category->id())->sort('weight', 'asc');

        $entities = $this->entityTypeManager->getStorage($entityType->id())->loadMultiple($query->execute());

        // Build the accordions.
        $build[$entityType->id()][$category->id()]['accordion'] = [
          '#type' => 'details',
          '#open' => FALSE,
          '#title' => $category->label() . ' (' . $query->count()->execute() . ')',
        ];

        // Build the accordion group tables.
        $this->buildTable($build[$entityType->id()][$category->id()]['accordion'], $entityType, $category, $entities);
      }
    }

    $build['#attached']['library'][] = 'cohesion/cohesion-admin-styles';

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildTable(&$build_data, $entityType, $category, $entities = []) {
    $build_data['table'] = [
      '#type' => 'table',
      '#header' => ($entities) ? $this->buildHeader() : [],
      '#title' => $category->label(),
      '#rows' => [],
      '#empty' => $this->t('There are no available @label that component content can be created from.', ['@label' => mb_strtolower($entityType->getLabel())]),
      '#cache' => [
        'contexts' => $entityType->getListCacheContexts(),
        'tags' => $entityType->getListCacheTags(),
      ],
    ];

    // Build rows.
    foreach ($entities as $entity) {
      $common_row = $this->buildRow($entity);

      $id = $entity->id();

      $build_data['table'][$id]['label'] = [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#value' => $common_row['label'],
      ];

      $build_data['table'][$id]['type'] = [
        '#type' => 'markup',
        '#markup' => $common_row['type'],
      ];

      $build_data['table'][$id]['create'] = [
        '#type' => 'operations',
        '#links' => [
          [
            'title' => $this->t('Create Component Content'),
            'url' => $common_row['operations'],
          ],
        ],
      ];

    }
  }

  /**
   * {@inheritdoc}
   **/
  public function buildHeader() {
    $header = [];
    $header['label'] = [
      'data' => $this->t('Title'),
      'width' => '25%',
    ];

    $header['type'] = [
      'data' => $this->t('Category'),
      'width' => '50%',
    ];

    $header['operations']['data'] = $this->t('Operations');

    return $header;
  }

  /**
   * {@inheritdoc}
   **/
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();

    if ($category_entity = $entity->getCategoryEntity()) {
      $row['type'] = $category_entity->label();
    }

    $row['operations'] = Url::fromRoute('entity.component_content.add_form', ['cohesion_component' => $entity->id()]);

    return $row;
  }

}
