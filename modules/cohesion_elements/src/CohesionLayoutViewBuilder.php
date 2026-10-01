<?php

namespace Drupal\cohesion_elements;

use Drupal\cohesion_elements\Event\CohesionLayoutViewBuilderEvent;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\RevisionableStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CohesionLayoutViewBuilder.
 *
 * Render controller for cohesion_layout.
 *
 * @package Drupal\cohesion_elements
 */
class CohesionLayoutViewBuilder extends EntityViewBuilder {

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    $instance = parent::createInstance($container, $entity_type);
    $instance->tokenEntityMapper = $container->get('token.entity_mapper');
    $instance->contextCacheMetadata = $container->get('cohesion_templates.context.cache_metadata');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    return $instance;
  }

  /** @var object Token entity mapper service */
  protected $tokenEntityMapper;

  /** @var object Context cache metadata service */
  protected $contextCacheMetadata;

  /** @var \Drupal\Core\Entity\EntityTypeManagerInterface Entity type manager */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $entity, $view_mode = 'full', $langcode = NULL) {
    /** @var \Drupal\Core\Entity\EntityInterface $host */
    $host = $entity->getParentEntity();
    $parent_field_name = $entity->get('parent_field_name')->value;

    if ($host instanceof RevisionableInterface && $parent_field_name) {
      $storage = $this->entityTypeManager->getStorage($host->getEntityTypeId());
      $revision_id_key = $host->getEntityType()->getKey('revision');
      if ($revision_id_key) {
        // Use accessCheck(FALSE) to avoid node access query alters that can
        // cause SQL errors with modules like content_access. Those modules
        // incorrectly reference 'nid' on revision field tables, but those
        // tables use 'entity_id' instead. Access is verified below via
        // $candidate->access('view').
        $revision_ids = $storage->getQuery()
          ->allRevisions()
          ->accessCheck(FALSE)
          ->condition($parent_field_name . '.target_id', $entity->id())
          ->condition($parent_field_name . '.target_revision_id', $entity->getRevisionId())
          ->sort($revision_id_key, 'DESC')
          ->range(0, 1)
          ->execute();
        if ($revision_ids) {
          $host_revision_id = array_key_first($revision_ids);
          if ((int) $host->getRevisionId() !== (int) $host_revision_id) {
            if ($storage instanceof RevisionableStorageInterface) {
              $candidate = $storage->loadRevision($host_revision_id);
              // Use candidate revision only if user can view it.
              if ($candidate && $candidate->access('view')) {
                $host = $candidate;
              }
            }
          }
        }
      }
    }

    // Apply language translation if langcode provided and host exists.
    if ($host && !empty($langcode) && $host->isTranslatable() && $host->hasTranslation($langcode)) {
      $translated_host = $host->getTranslation($langcode);
      // Only use translation if it's accessible.
      if ($translated_host->access('view')) {
        $host = $translated_host;
      }
    }

    $entities = [];
    $cache_tags = [];
    if ($host) {
      $token_type = $this->tokenEntityMapper->getTokenTypeForEntityType($host->getEntityTypeId(), $host->getEntityTypeId());
      $entities[$token_type] = $host;

      $cache_tags[] = 'layout_formatter.' . $host->uuid();
    }

    // Set up some variables.
    $variables = $entities;
    $variables['layout_builder_entity'] = [
      'entity' => $entity,
      'entity_type_id' => $entity->getEntityTypeId(),
      'id' => $entity->id(),
      'revision_id' => $entity->getRevisionId(),
    ];

    $context_cache_metadata = $this->contextCacheMetadata;
    $context_names = $entity->getTwigContexts();
    if (!empty($context_names)) {
      $cache = $context_cache_metadata->getContextsCacheMetadata($context_names);
    }
    else {
      $cache = [
        'tags' => [],
        'contexts' => [],
      ];
    }

    $cache['tags'] = array_merge($cache['tags'], $entity->getCacheTags(), $cache_tags);
    $cache['contexts'] = array_merge($cache['contexts'], $entity->getCacheContexts());

    // Tell the field to render as a "cohesion_layout".
    $build = [
      '#type' => 'inline_template',
      '#template' => $entity->getTwig(),
      '#context' => $variables,
      '#cache' => $cache,
    ];

    $content = '<style>' . $entity->getStyles() . '</style>';
    $build['#attached'] = ['cohesion' => [$content]];

    // Let other module alter the view build
    $event = new CohesionLayoutViewBuilderEvent($build, $entity);
    $event_dispatcher = \Drupal::service('event_dispatcher');
    $event_dispatcher->dispatch($event, $event::ALTER);
    $build = $event->getBuild();

    return $build;
  }

}
