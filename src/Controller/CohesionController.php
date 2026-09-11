<?php

namespace Drupal\cohesion\Controller;

use Drupal\cohesion\CohesionJsonResponse;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Drupal\system\SystemManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CohesionController.
 *
 * Controller routines for Site Studio admin index page.
 *
 * @package Drupal\cohesion\Controller
 */
class CohesionController extends ControllerBase {

  /**
   * @var \Drupal\system\SystemManager
   */
  protected $systemManager;

  /**
   * @var mixed
   */
  protected $file_name;

  /**
   * CohesionController constructor.
   *
   * @param \Drupal\system\SystemManager $systemManager
   *
   */
  public function __construct(SystemManager $systemManager) {
    $this->systemManager = $systemManager;
    $file_name = \Drupal::request()->query->get('file_name');
    $this->file_name = $file_name;
  }

  /**
   * The admin landing page (admin/cohesion).
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Controller's container.
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new self($container->get('system.manager'));
  }

  /**
   * Constructs a page with placeholder content.
   *
   * @return array
   */
  public function index() {
    return $this->systemManager->getBlockContents();
  }

  /**
   * Get an array of the available cohesion entity types.
   *
   * @return array
   */
  public static function getCohesionEnityTypes() {
    $results = [];
    foreach (\Drupal::service('entity_type.manager')->getDefinitions() as $value) {
      /** @var EntityTypeInterface $value */
      if ($value->entityClassImplements('\Drupal\cohesion\Entity\CohesionSettingsInterface')) {
        $results[$value->get('id')] = $value->getLabel()->render();
      }
    }
    return $results;
  }

  /**
   * Log JS errors to Drupal DB logs.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   */
  public static function errorLogger(Request $request) {
    if (($error_data = Json::decode($request->getContent())) && isset($error_data['message'])) {
      \Drupal::service('settings.endpoint.utils')->logError($error_data['message']);
    }
    return new CohesionJsonResponse([]);
  }

}
