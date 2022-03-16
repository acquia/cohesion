<?php

namespace Drupal\cohesion_custom_styles\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\Controller\EntityController;
use Drupal\Core\Link;

/**
 * Class CustomStylesController.
 *
 * Returns responses for custom style type routes.
 *
 * @package Drupal\cohesion_custom_styles\Controller
 */
class CustomStylesController extends EntityController implements ContainerInjectionInterface {

  /**
   * Displays add content links for available custom style types.
   *
   * @return array - A render array for a list of the custom style types that can be added
   */
  public function addPage($entity_type_id) {
    // Get complete list of custom style types.
    $types = [];

    foreach ($this->entityTypeManager->getStorage('custom_style_type')->loadMultiple() as $type) {
      $types[$type->id()] = [
        'label' => $type->label(),
        'description' => '',
        'add_link' => Link::createFromRoute($type->label(), 'entity.cohesion_custom_style.add_form', ['custom_style_type' => $type->id()]),
      ];
    }
    // Sort custom style type list in ascending order.
    array_multisort($types, SORT_ASC);

    // Send this to entity-add-list.html.twig via system.module.
    $build = [
      '#theme' => 'entity_add_list',
      '#bundles' => $types,
      '#add_bundle_message' => t('There are no available custom style types. Go to the batch import page to import the list of custom style types.'),
      '#cache' => [
        'tags' => $this->entityTypeManager->getDefinition('custom_style_type')->getListCacheTags(),
      ],
    ];

    return $build;
  }

}
