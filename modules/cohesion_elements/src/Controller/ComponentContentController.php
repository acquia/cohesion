<?php

namespace Drupal\cohesion_elements\Controller;

use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\cohesion_elements\Entity\CohesionLayout;
use Drupal\cohesion_elements\Entity\ComponentContent;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\cohesion\CohesionJsonResponse;
use Drupal\cohesion_elements\Entity\Component;

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
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function getComponentContents(Request $request) {

    $storage = \Drupal::entityTypeManager()->getStorage('component_content');
    $query = $storage->getQuery()->condition('status', TRUE)->sort('title', 'asc');

    $ids = $query->execute();
    $component_contents = $storage->loadMultiple($ids);
    $data = [];

    foreach ($component_contents as $component_content) {
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
      $elements = $layout_canvas->iterateCanvas();
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

}
