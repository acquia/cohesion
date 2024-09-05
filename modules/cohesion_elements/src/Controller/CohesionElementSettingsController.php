<?php

namespace Drupal\cohesion_elements\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\system\SystemManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CohesionElementSettingsController.
 *
 * Controller routines for Site Studio admin index page.
 *
 * @package Drupal\cohesion_elements\Controller
 */
class CohesionElementSettingsController extends ControllerBase {

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

}
