<?php

namespace Drupal\cohesion_style_guide\Plugin\Usage;

use Drupal\cohesion\UsagePluginBase;
use Drupal\cohesion_style_guide\Entity\StyleGuideManager;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Style guide manager usage plugin.
 *
 * @package Drupal\cohesion_style_guide\Plugin\Usage
 *
 * @Usage(
 *   id = "style_guide_manager_usage",
 *   name = @Translation("Style guide manager usage"),
 *   entity_type = "cohesion_style_guide_manager",
 *   scannable = TRUE,
 *   scan_same_type = TRUE,
 *   group_key = "style_guide_manager_type",
 *   group_key_entity_type = "style_guide_manager_type",
 *   exclude_from_package_requirements = FALSE,
 *   exportable = TRUE,
 *   config_type = "site_studio",
 *   scan_groups = {"core", "site_studio"},
 *   can_be_excluded = TRUE
 * )
 */
class StyleGuideManagerUsage extends UsagePluginBase {

  /**
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * StyleGuideManagerUsage constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\Core\StreamWrapper\StreamWrapperManager $stream_wrapper_manager
   * @param \Drupal\Core\Database\Connection $connection
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, StreamWrapperManager $stream_wrapper_manager, Connection $connection, ThemeHandlerInterface $theme_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $stream_wrapper_manager, $connection);
    $this->themeHandler = $theme_handler;
  }

  /**
   *
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('stream_wrapper_manager'),
      $container->get('database'),
      $container->get('theme_handler'));
  }

  /**
   * {@inheritdoc}
   */
  public function scanForInstancesOfThisType($data, EntityInterface $entity) {
    $entities = parent::scanForInstancesOfThisType($data, $entity);

    // Get all the custom styles used used.
    foreach ($data as $entry) {
      if ($entry['type'] == 'coh_style_guide_manager') {
        $entities[] = [
          'type' => 'cohesion_style_guide_manager',
          'uuid' => $entry['style_guide_uuid'],
          'subid' => NULL,
        ];
      }

    }

    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  public function getScannableData(EntityInterface $entity) {
    /** @var \Drupal\cohesion_style_guide\Entity\StyleGuideManager $entity */
    $scanable_data = [];

    // Always add the JSON model and form blobs.
    $scanable_data[] = [
      'type' => 'json_string',
      'value' => $entity->getJsonValues(),
      'decoded' => $entity->getDecodedJsonValues(),
    ];

    $scanable_data[] = [
      'type' => 'coh_style_guide',
      'style_guide_uuid' => $entity->get('style_guide_uuid'),
    ];

    // Each style guide manager is dependent on its parent(s) style guide
    // manager
    // If the theme of the style guide manager we are saving has parent(s) theme
    // and these parent theme have a style guide manager as well, attach them
    // as scannable data as they should be registered as dependencies
    if (isset($this->themeHandler->listInfo()[$entity->get('theme')]->base_themes)) {
      foreach ($this->themeHandler->listInfo()[$entity->get('theme')]->base_themes as $base_theme_id => $base_theme_name) {
        $style_guide_managers = $this->entityTypeManager->getStorage('cohesion_style_guide_manager')
          ->getQuery()
          ->accessCheck(TRUE)
          ->condition('theme', $base_theme_id)
          ->condition('style_guide_uuid', $entity->get('style_guide_uuid'))
          ->execute();

        $style_guide_managers_id = array_shift($style_guide_managers);

        if ($style_guide_managers_id) {

          $style_guide_manager = StyleGuideManager::load($style_guide_managers_id);
          if ($style_guide_manager) {
            $scanable_data[] = [
              'type' => 'coh_style_guide_manager',
              'style_guide_uuid' => $style_guide_manager->uuid(),
            ];
          }
        }
      }
    }

    return $scanable_data;
  }

}
