<?php

namespace Drupal\cohesion_custom_styles\Plugin\rest\resource;

use Drupal\cohesion\CohesionResourceBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Deprecated.
 *
 * Provides a Custom style type Resource.
 *
 * @RestResource(
 *   id = "cohesion_custom_style_type",
 *   label = @Translation("Custom style types"),
 *   uri_paths = {
 *     "canonical" = "/cohesionapi/custom_style_types",
 *   }
 * )
 */
class CustomStyleTypeResource extends CohesionResourceBase {

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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
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
   * Get all custom style types.
   *
   * @return \Drupal\rest\ResourceResponse
   */
  public function get() {
    $data = [];

    foreach ($custom_style_types = $this->entityTypeManager->getStorage('custom_style_type')->loadMultiple() as $type) {
      $data[$type->id()] = [
        'label' => $type->get('label'),
        'value' => $type->get('id'),
      ];
    }

    ksort($data);
    $data = array_values($data);
    $response = new ResourceResponse($data);
    $response->addCacheableDependency(CacheableMetadata::createFromRenderArray([
      '#cache' => [
        'tags' => [
          'config:custom_style_type_list',
        ],
      ],
    ]));

    return $response;
  }

}
