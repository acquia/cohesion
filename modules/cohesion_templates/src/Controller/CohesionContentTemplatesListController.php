<?php

namespace Drupal\cohesion_templates\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class CohesionContentTemplatesListController.
 *
 * Returns responses for Templates routes.
 *
 * @package Drupal\cohesion_templates\Controller
 */
class CohesionContentTemplatesListController extends ControllerBase {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface*/
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Entity\EntityTypeInterface|null*/
  protected $entityTypeDefinition;

  /**
   * CohesionContentTemplatesListController constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\Core\Entity\Query\QueryInterface $entity_query
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityTypeDefinition = $this->entityTypeManager->getDefinition('cohesion_content_templates');
  }

  /**
   *
   */
  public static function create(ContainerInterface $container) {
    return new self($container->get('entity_type.manager'));
  }

  /**
   * Shows the block administration page.
   *
   * @param string|null $theme
   *   Theme key of block list.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return array
   *   A render array as expected by drupal_render().
   */
  public function listing($entity_type) {

    $templates_ids = $this->entityTypeManager->getStorage('cohesion_content_templates')->getQuery()
      ->accessCheck(TRUE)
      ->execute();

    if ($templates_ids) {
      $candidate_template_storage = $this->entityTypeManager->getStorage('cohesion_content_templates');
      $candidate_templates = $candidate_template_storage->loadMultiple($templates_ids);
      $bundles = [];
      foreach ($candidate_templates as $entity) {
        if (!isset($bundles[$entity->get('entity_type')])) {
          $bundles[$entity->get('entity_type')] = $entity->get('entity_type');
        }
      }

      $entity_types = $this->entityTypeManager->getDefinitions();
      foreach ($bundles as $entity_type_name) {
        $entity_type = $entity_types[$entity_type_name];
        $types[$entity_type_name] = [
          'label' => ($entity_type->get('bundle_label')) ? $entity_type->get('bundle_label') : $entity_type->get('label'),
          'description' => t('Manage your @settings_label templates', ['@settings_label' => strtolower($entity_type->getLabel())]),
          'add_link' => Link::createFromRoute($entity_type->getLabel(), 'entity.cohesion_content_templates.collection', ['content_entity_type' => $entity_type_name]),
        ];
      }

      $build = [
        '#theme' => 'entity_add_list',
        '#bundles' => $types,
        '#add_bundle_message' => t('There are no available content templates. Go to the batch import page to import the list of content templates.'),
        '#cache' => [
          'contexts' => $this->entityTypeDefinition->getListCacheContexts(),
          'tags' => $this->entityTypeDefinition->getListCacheTags(),
        ],
      ];

      return $build;
    }
    else {
      throw new NotFoundHttpException();
    }
  }

}
