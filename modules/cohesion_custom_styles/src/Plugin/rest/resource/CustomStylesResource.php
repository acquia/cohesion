<?php

namespace Drupal\cohesion_custom_styles\Plugin\rest\resource;

use Drupal\cohesion\CohesionResourceBase;
use Drupal\cohesion_custom_styles\Entity\CustomStyle;
use Drupal\cohesion_custom_styles\Entity\CustomStyleType;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Deprecated.
 *
 * Provides a Custom styles Resource.
 *
 * @RestResource(
 *   id = "cohesion_custom_styles",
 *   label = @Translation("Custom styles"),
 *   uri_paths = {
 *     "canonical" = "/cohesionapi/custom_styles/{custom_style_type}",
 *   }
 * )
 */
class CustomStylesResource extends CohesionResourceBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Constructs a Drupal\rest\Plugin\rest\resource\EntityResource object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\entityTypeManagerManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, $serializer_formats, LoggerInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest')
    );
  }

  /**
   * Get all custom styles.
   *
   * @param \Drupal\cohesion_custom_styles\Entity\CustomStyleType $custom_style_type
   *
   * @return \Drupal\rest\ResourceResponse
   */
  public function get(CustomStyleType $custom_style_type) {
    $data = [];

    $storage = $this->entityTypeManager->getStorage('cohesion_custom_style');

    // Get parent custom styles.
    $condition_value = [$custom_style_type->get('id'), 'generic'];
    $entity_ids = $storage->getQuery()
      ->accessCheck(TRUE)
      ->sort('weight')
      ->notExists('parent')
      ->condition('custom_style_type', $condition_value, 'IN')
      ->condition('status', TRUE)
      ->execute();

    // Execute the query.
    if ($entities = $storage->loadMultiple($entity_ids)) {
      /** @var \Drupal\cohesion_custom_styles\Entity\CustomStyle $entity */
      foreach ($entities as $entity) {
        // Add the parent if it is selectable.
        if ($entity->isSelectable()) {
          $data[] = $this->entityToMapped($entity);
        }

        // Add custom style children entities.
        $children = [];
        $children_ids = $storage->getQuery()
          ->accessCheck(TRUE)
          ->condition('parent', $entity->getClass())
          ->condition('status', TRUE)
          ->condition('selectable', TRUE)
          ->sort('weight')
          ->execute();
        if ($children_ids && count($children_ids) > 0) {
          foreach ($storage->loadMultiple($children_ids) as $child_entity) {
            /** @var \Drupal\cohesion_custom_styles\Entity\CustomStyle $child_entity */
            if ($child_entity->isSelectable()) {
              $children[] = $this->entityToMapped($child_entity);
            }
          }
        }

        // Add the children.
        $data = array_merge($data, $children);
      }
    }

    $response = new ResourceResponse($data);
    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray([
      '#cache' => [
        'tags' => [
          'config:cohesion_custom_style_list',
        ],
      ],
    ]));

    return $response;
  }

  /**
   * Maps config entity to something Angular expects.
   *
   * @param \Drupal\cohesion_custom_styles\Entity\CustomStyle $entity
   *
   * @return array
   */
  private function entityToMapped(CustomStyle $entity) {
    $mapped_object = [];
    $mapped_object['label'] = $entity->get('label');
    $class_name = $entity->get('class_name');
    $mapped_object['value'] = ltrim($class_name, '.');
    // Add custom style group.
    $type_id = $entity->get('custom_style_type');
    // Add group if custom style  type is generic.
    if ('generic' === $type_id) {
      $custom_style_type_entity = $this->entityTypeManager->getStorage('custom_style_type')->load($type_id);
      if ($custom_style_type_entity) {
        $mapped_object['group'] = $custom_style_type_entity->get('label');
      }

    }
    return $mapped_object;
  }

  /**
   * {@inheritdoc}
   */
  protected function getBaseRoute($canonical_path, $method) {
    $route = parent::getBaseRoute($canonical_path, $method);

    $parameters = $route->getOption('parameters') ?: [];
    $parameters['custom_style_type']['type'] = 'entity:custom_style_type';
    $route->setOption('parameters', $parameters);

    return $route;
  }

}
