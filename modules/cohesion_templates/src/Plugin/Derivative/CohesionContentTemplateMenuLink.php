<?php

namespace Drupal\cohesion_templates\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CohesionContentTemplateMenuLink.
 *
 * Derivative class that provides the menu links for the Content Template.
 *
 * @package Drupal\cohesion_templates\Plugin\Derivative
 */
class CohesionContentTemplateMenuLink extends DeriverBase implements ContainerDeriverInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Creates a ProductMenuLink instance.
   *
   * @param $base_plugin_id
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   */
  public function __construct($base_plugin_id, EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new self($base_plugin_id, $container->get('entity_type.manager'));
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {

    $links = [];

    $templates_ids = $this->entityTypeManager->getStorage('cohesion_content_templates')->getQuery()
      ->accessCheck(TRUE)
      ->execute();

    if ($templates_ids) {
      $entity_types = $this->entityTypeManager->getDefinitions();
      $candidate_template_storage = $this->entityTypeManager->getStorage('cohesion_content_templates');
      $candidate_templates = $candidate_template_storage->loadMultiple($templates_ids);
      foreach ($candidate_templates as $entity) {
        if (!isset($links['cohesion_template_menu_' . $entity->get('entity_type')])) {
          if (isset($entity_types[$entity->get('entity_type')])) {
            $entity_type = $entity_types[$entity->get('entity_type')];
            $links['cohesion_template_menu_' . $entity->get('entity_type')] = [
              'title' => ($entity_type->get('bundle_label')) ? $entity_type->get('bundle_label') : $entity_type->get('label'),
              'route_name' => 'entity.cohesion_content_templates.collection',
              'parent' => 'entity.cohesion_content_templates',
              'route_parameters' => ['content_entity_type' => $entity->get('entity_type')],
            ] + $base_plugin_definition;
          }
        }
      }
    }

    return $links;
  }

}
