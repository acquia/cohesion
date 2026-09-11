<?php

namespace Drupal\cohesion_templates\Controller;

use Drupal\cohesion\CohesionJsonResponse;
use Drupal\cohesion_templates\Entity\MasterTemplates;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\system\SystemManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Utility\Error;

/**
 * Class CohesionTemplateSettingsController.
 *
 * Controller routines for Site Studio admin index page.
 *
 * @package Drupal\cohesion_templates\Controller
 */
class CohesionTemplateSettingsController extends ControllerBase {

  /**
   * System Manager Service.
   *
   * @var \Drupal\system\SystemManager
   */
  protected $systemManager;

  /**
   * Constructs a new SystemController.
   *
   * @param \Drupal\system\SystemManager $systemManager
   *   System manager service.
   */
  public function __construct(SystemManager $systemManager) {
    $this->systemManager = $systemManager;
  }

  /**
   *
   */
  public static function create(ContainerInterface $container) {
    return new self($container->get('system.manager'));
  }

  /**
   * Constructs a page with placeholder content.
   */
  public function index() {
    return $this->systemManager->getBlockContents();
  }

  /**
   * GET: /cohesionapi/menu_templates.
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   */
  public function menuTemplates() {
    // Loop through the available menu templates.
    $data = [];
    if (($entities = $this->menuTemplateEntities())) {
      foreach ($entities as $entity) {
        $data[] = [
          'value' => $entity->get('id'),
          'label' => $entity->get('label'),
        ];
      }
    }
    $error = empty($data) ? TRUE : FALSE;
    return new CohesionJsonResponse([
      'status' => !$error ? 'success' : 'error',
      'data' => !$error ? $data : [],
    ]);
  }

  /**
   * Provides a generic title callback for template collection view.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\Core\Entity\EntityInterface $_entity
   *   (optional) An entity, passed in directly from the request attributes.
   *
   * @return string|null
   *   The title for the collection view page, if an entity was found.
   */
  public function title(RouteMatchInterface $route_match, ?EntityInterface $_entity = NULL) {
    $route_name_parts = explode('.', $route_match->getRouteName());
    $route_type = end($route_name_parts);

    $master_template = $route_match->getParameter('cohesion_master_templates');
    if ($route_type == 'edit_form' && $master_template instanceof MasterTemplates) {
      return t('Edit @type: @label', [
        '@type' => $master_template->getEntityType()->getLabel(),
        '@label' => Markup::create('<em>' . $master_template->label() . '</em>'),
      ]);
    }

    if (($content_entity_type = $route_match->getParameter('content_entity_type'))) {
      $entity_types = \Drupal::entityTypeManager()->getDefinitions();
      if (isset($entity_types[$content_entity_type])) {
        $entity_type = $entity_types[$content_entity_type];
        return ($entity_type->get('bundle_label')) ? $entity_type->get('bundle_label') : $entity_type->get('label');
      }
    }
  }

  /**
   *
   * @return array of menu template entities
   */
  private function menuTemplateEntities() {
    try {
      $entity_ids = \Drupal::entityQuery('cohesion_menu_templates')
        ->accessCheck(TRUE)
        ->condition('status', TRUE)
        ->condition('selectable', TRUE)
        ->execute();

      return \Drupal::service('entity_type.manager')->getStorage('cohesion_menu_templates')->loadMultiple($entity_ids);
    }
    catch (PluginNotFoundException $ex) {
      Error::logException('cohesion', $ex);
    }
    return [];
  }

}
