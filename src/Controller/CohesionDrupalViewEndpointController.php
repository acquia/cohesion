<?php

namespace Drupal\cohesion\Controller;

use Drupal\cohesion\CohesionJsonResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\views\Views;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CohesionDrupalViewEndpointController
 * Returns Drupal data to Angular (views, blocks, node lists, etc).
 * See function index() for the entry point.
 *
 * @package Drupal\cohesion\Controller
 */
class CohesionDrupalViewEndpointController extends ControllerBase {

  /**
   * Return a list of view modes for the given entity type.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function getEntityViewModes(Request $request) {

    // Get the view from the request.
    $entity_type = $request->attributes->get('entity_type');
    $view_modes_data = [
      [
        'value' => 'default',
        'name' => t('Drupal view default'),
      ],
    ];
    // And use that to get the list of view modes for the entity type.
    $view_modes = $this->entityTypeManager()
      ->getStorage('entity_view_mode')
      ->getQuery()
      ->accessCheck(TRUE)
      ->condition('targetEntityType', $entity_type)
      ->execute();

    // Get a list of view modes for the desired entity.
    foreach ($view_modes as $view_mode) {

      // Load the view mode config entity to get the label.
      $view_mode_entity = $this->entityTypeManager()->getStorage('entity_view_mode')->load($view_mode);

      // Save the data.
      try {
        $view_modes_data[] = [
          'value' => explode('.', $view_mode)[1],
          'name' => $view_mode_entity->label(),
        ];
      }
      catch (\Exception $e) {

      }
    }

    $error = !empty($view_modes_data) ? FALSE : TRUE;

    return new CohesionJsonResponse([
      'status' => !$error ? 'success' : 'error',
      'data' => $view_modes_data,
    ]);
  }

  /**
   * Return a list of view modes for the given entity type.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function getViewModes(Request $request) {

    $view_modes_data = [];
    $restrict = [$request->query->get('restrict')] ?: [];

    $storage = $this->entityTypeManager()->getStorage('entity_view_mode');
    $entity_ids = $storage->getQuery()->accessCheck(TRUE)->execute();
    $entities = $storage->loadMultiple($entity_ids);
    $entity_types_definition = $this->entityTypeManager->getDefinitions();

    $default_view_modes = [];
    foreach ($entities as $view_mode => $entity) {
      $entity_type = $entity_types_definition[$entity->getTargetType()];

      if (empty(array_filter($restrict)) || in_array($entity->getTargetType(), $restrict)) {
        $view_builder = $entity_type->hasHandlerClass('view_builder');
        if ($entity_type instanceof ContentEntityTypeInterface && $view_builder) {
          $view_modes_data[] = [
            'value' => $view_mode,
            'label' => $entity->label(),
            'group' => $entity_type->getLabel(),
          ];

          if (!isset($default_view_modes[$entity_type->id()])) {
            $default_view_modes[$entity_type->id()] = [
              'value' => $entity_type->id() . '.default',
              'label' => t('Drupal view default'),
              'group' => $entity_type->getLabel(),
            ];
          }
        }
      }

    }

    $view_modes_data = array_merge($view_modes_data, array_values($default_view_modes));

    $error = !empty($view_modes_data) ? FALSE : TRUE;

    return new CohesionJsonResponse([
      'status' => !$error ? 'success' : 'error',
      'data' => $view_modes_data,
    ]);
  }

  /**
   * Return a list of bundles for the given entity type.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   */
  public function getEntityViewBundles(Request $request) {

    // Get the view from the request.
    $entity_type = $request->attributes->get('entity_type');
    $bundles_data = [
      [
        'value' => 'all',
        'name' => t('All'),
      ],
    ];
    // And use that to get the list of view modes for the entity type.
    $bundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo($entity_type);

    // Get a list of view modes for the desired entity.
    foreach ($bundles as $bundle_id => $bundle) {

      $bundles_data[] = [
        'value' => $bundle_id,
        'name' => t("@label", ['@label' => $bundle['label']]),
      ];
    }

    return new CohesionJsonResponse([
      'status' => 'success',
      'data' => $bundles_data,
    ]);
  }

  /**
   * Return a list of views.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   */
  public function getViews(Request $request) {

    $views_data = [];
    // Get a list of views.
    if (($views = $this->entityTypeManager()->getStorage('view')->loadMultiple())) {
      foreach ($views as $view) {
        $views_data[] = [
          'value' => $view->id(),
          'name' => $view->label(),
        ];
      }
    }

    $error = !empty($views_data) ? FALSE : TRUE;

    return new CohesionJsonResponse([
      'status' => !$error ? 'success' : 'error',
      'data' => $views_data,
    ]);
  }

  /**
   * Return a list of exposed filters for a particular view/display.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   */
  public function getViewsData(Request $request) {
    // Get the view name / display name from the Request.
    $view_id = $request->attributes->get('view');
    $display_id = $request->attributes->get('display');
    $filter_id = $request->attributes->get('filter');

    // Views data.
    $views_data = $this->viewsData($view_id, $display_id, $filter_id);
    $error = !empty($views_data) ? FALSE : TRUE;

    $views_data = reset($views_data);

    return new CohesionJsonResponse([
      'status' => !$error ? 'success' : 'error',
      'data' => $views_data,
    ]);
  }

  /**
   * Get a list of pagers from the view display.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   */
  public function getViewPager(Request $request) {

    $pager_data = [];
    // Get the view name / display name from the Request.
    $view_name = $request->attributes->get('view');
    $view_display_name = $request->attributes->get('display');

    if ($view = $this->entityTypeManager()->getStorage('view')->load($view_name)) {

      // Get the defined pager for this view display.
      $this_pager_type = $view->get('display')[$view_display_name]['display_options']['pager']['type'] ?? $view->get('display')['default']['display_options']['pager']['type'];

      // Get all the possible pagers.
      if ($options = Views::fetchPluginNames('pager', !TRUE ? 'basic' : NULL, [])) {
        foreach ($options as $key => $pager) {

          // Only return the pager that is set in this view.
          if ($key == $this_pager_type) {
            $pager_data[] = [
              'value' => $key,
              'name' => $pager->render(),
            ];
            break;
          }
        }
      }
    }

    $error = !empty($pager_data) ? FALSE : TRUE;

    return new CohesionJsonResponse([
      'status' => !$error ? 'success' : 'error',
      'data' => $pager_data,
    ]);
  }

  /**
   *
   * @param string $view_id
   * @param string $display_id
   * @param string $filter_id
   *
   * @return array
   */
  protected function viewsData($view_id = NULL, $display_id = NULL, $filter_id = NULL) {

    $results = [];

    if ($filter_id) {
      $results['view_filter_display'] = [
        'default' => [
          'value' => '',
          'name' => t('Drupal default'),
        ],
      ];
    }

    if (($view = $this->entityTypeManager()->getStorage('view')->load($view_id))) {
      $displays = $view->get('display');

      // Views data.
      foreach ($displays as $display_name => $display) {

        // Return the filters for a display.
        if ($display_id) {

          // If either the default or the specific view display itself.
          if ($display_name == 'default' || $display_name == $display_id) {

            // Filters.
            if (isset($display['display_options']['filters'])) {
              foreach ($display['display_options']['filters'] as $filter) {

                // Only include if it's an exposed filter/sort.
                if (isset($filter['exposed'])) {

                  if (!$filter_id) {

                    $results['view_filter'][$filter['id']] = [
                      'value' => $filter['id'],
                      'name' => (isset($filter['entity_type']) && isset($filter['entity_field'])) ? ucwords($filter['entity_type']) . ' ' . ucwords($filter['entity_field']) . ' (' . $filter['entity_field'] . ')' : $filter['expose']['label'],
                    ];
                  }
                  // Determine if this will be a list of data.
                  else {
                    if ($filter['id'] == $filter_id && $filter['plugin_id'] != 'string') {

                      $results['view_filter_display']['list'] = [
                        'value' => 'list',
                        'name' => t('List of links'),
                      ];
                    }
                  }
                }
              }
            }

            // Sorts.
            if (isset($display['display_options']['sorts'])) {
              foreach ($display['display_options']['sorts'] as $filter) {

                // Only include if it's an exposed filter/sort.
                if ($filter['exposed']) {

                  // Return the filter.
                  if (!$filter_id) {

                    $filter_name = (isset($filter['entity_type']) && isset($filter['entity_field'])) ? ucwords($filter['entity_type']) . ' ' . ucwords($filter['entity_field']) . ' (' . $filter['entity_field'] . ')' : $filter['expose']['label'];
                    $results['view_filter']['coh_sort_' . $filter['id']] = [
                      'value' => 'coh_sort_' . $filter['id'],
                      'name' => '[sort] ' . $filter_name,
                    ];
                  }
                  // Sorts are always available as lists
                  // (they're always <select>'s)
                  else {
                    if ($filter['id'] == $filter_id) {
                      $results['view_filter_display']['list'] = [
                        'value' => 'list',
                        'name' => t('List style'),
                      ];
                    }
                  }
                }
              }
            }
          }
        }
        // Only load the displays of this view.
        else {
          $results['view_display'][] = [
            'value' => $display['id'],
            'name' => $display['display_title'],
          ];
        }
      }
    }

    if (isset($results['view_filter'])) {
      $results['view_filter'] = array_values($results['view_filter']);
    }

    if (isset($results['view_filter_display'])) {
      $results['view_filter_display'] = array_values($results['view_filter_display']);
    }

    return $results;
  }

}
