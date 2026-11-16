<?php

namespace Drupal\cohesion_elements\Controller;

use Drupal\cohesion\UsageUpdateManager;
use Drupal\cohesion_elements\CustomComponentsService;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Custom components controller.
 *
 * @package Drupal\cohesion_elements\Controller
 */
class CustomComponentController extends ControllerBase {

  /**
   * Custom Components service.
   *
   * @var \Drupal\cohesion_elements\CustomComponentsService
   */
  protected $customComponents;

  /**
   * Site Studio usage update manager
   *
   * @var \Drupal\cohesion\UsageUpdateManager
   */
  protected $usageUpdateManager;

  /**
   * @param \Drupal\cohesion_elements\CustomComponentsService $customComponents
   * @param \Drupal\cohesion\UsageUpdateManager $usage_update_manager
   */
  public function __construct(
    CustomComponentsService $customComponents,
    UsageUpdateManager $usage_update_manager,
  ) {
    $this->customComponents = $customComponents;
    $this->usageUpdateManager = $usage_update_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): CustomComponentController {
    return new self(
      $container->get('custom.components'),
      $container->get('cohesion_usage.update_manager')
    );
  }

  /**
   * Displays add links for the available bundles.
   * Redirects to the add form if there's only one bundle available.
   *
   * @param $machine_name
   *
   * @return array
   */
  public function inUse($machine_name) {

    // Fetch the custom component.
    $custom_component = $this->customComponents->getComponent($machine_name);
    // Format it.
    $entity = $this->customComponents->formatAsComponent($custom_component);

    $list = $entity->getInUseMessage();

    $rows = function ($result = []) {
      $rows_data = [];
      foreach ($result as $entity) {
        $rows_data[] = [
          [
            'data' => new FormattableMarkup('<a href=":link">@name</a>', [
              ':link' => $entity['url'],
              '@name' => $entity['name'],
            ]),
          ],
        ];
      }
      return $rows_data;
    };

    $in_use_entities = $this->usageUpdateManager->getFormattedInUseEntitiesList($entity);

    foreach ($in_use_entities as $type => $result) {
      $list[] = [
        '#type' => 'details',
        '#open' => TRUE,
        '#title' => $type,
        'table' => [
          '#type' => 'table',
          '#header' => [],
          '#rows' => $rows($result),
        ],
      ];
    }

    return $list;
  }

  /**
   * The title for the custom component in use page, if a custom component
   * was found.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|void
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function inUseTitle(RouteMatchInterface $route_match) {
    $machine_name = $route_match->getParameter('machine_name');

    if ($component = $this->customComponents->getComponent($machine_name)) {
      return $this->t('In use: %name', ['%name' => $component['name']]);
    }
  }

}
