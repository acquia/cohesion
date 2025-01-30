<?php

namespace Drupal\cohesion\Services;

use Drupal\cohesion\UsageUpdateManager;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\Entity\ConfigEntityTypeInterface;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class RebuildInuseBatch.
 *
 * Saves website settings and all entities used by those entities.
 *
 * @package Drupal\cohesion_website_settings
 */
class RebuildInuseBatch {
  use StringTranslationTrait;

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * @var \Drupal\cohesion\UsageUpdateManager
   */
  protected $usageUpdateManager;

  /**
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * RebuildInuseBatch constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   */
  public function __construct(ModuleHandlerInterface $module_handler, UsageUpdateManager $usage_update_manager, EntityRepositoryInterface $entity_repository, TranslationInterface $stringTranslation, EntityTypeManagerInterface $entityTypeManager) {
    $this->moduleHandler = $module_handler;
    $this->usageUpdateManager = $usage_update_manager;
    $this->entityRepository = $entity_repository;
    $this->stringTranslation = $stringTranslation;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Entry point into the batch run.
   *
   * @param $in_use_list
   *   - the list of in used entities to be saved
   * @param $changed_entities
   *   - the lsit of entities changed to be saved
   *
   * @return void
   */
  public function run($in_use_list, $changed_entities = []) {
    $operations = $this->getOperations($in_use_list, $changed_entities);
    if(empty($operations)) {
      return;
    }

    // Setup the batch.
    $batch = [
      'title' => $this->t('Rebuilding entities in use.'),
      'operations' => [],
      'finished' => '\Drupal\cohesion\Services\RebuildInuseBatch::finishedCallback',
      'progressive' => FALSE,
    ];

    $batch['operations'][] = [
      '\Drupal\cohesion\Services\RebuildInuseBatch::startCallback',
      [],
    ];

    $batch['operations'] = array_merge($batch['operations'], $operations);

    $batch['operations'][] = ['cohesion_templates_secure_directory', []];

    // Clear the render cache.
    $batch['operations'][] = [
      '\Drupal\cohesion\Services\RebuildInuseBatch::clearRenderCache',
      [],
    ];

    // Setup and run the batch.
    batch_set($batch);
  }

  /**
   * @param $in_use_list
   * @param $changed_entities
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function getOperations($in_use_list, $changed_entities = []) {
    $operations = [];

    // Save the entities that have changed.
    foreach ($changed_entities as $entity) {
      // Only rebuild entities that have been activated.
      $operations[] = [
        '_resave_entity', [$entity, TRUE],
      ];
    }

    // If style guide is used and the in use list contains a style guide manager
    // instance. Get the style guide in use entities and add them to the list
    // rather than the style guide manager entity. Ex: If a color is change and
    // that color is used in a style guide manager instance then we need to
    // rebuild the entities where the token for this color is used rather than
    // the style guide manager entity.
    if ($this->moduleHandler->moduleExists('cohesion_style_guide')) {
      foreach ($in_use_list as $uuid => $type) {
        if ($type == 'cohesion_style_guide_manager') {
          $style_guide_manager = $this->entityRepository->loadEntityByUuid('cohesion_style_guide_manager', $uuid);
          if ($style_guide_manager) {
            $style_guide = $this->entityRepository->loadEntityByUuid('cohesion_style_guide', $style_guide_manager->get('style_guide_uuid'));
            $in_use_list = array_merge($in_use_list, $this->usageUpdateManager->getInUseEntitiesList($style_guide));
          }
          unset($in_use_list[$uuid]);
        }
      }
    }

    // Reverse in use list so entities can be processed by type
    // and set each set of uuids to the rebuild_max_entity
    $entity_to_process = Settings::get('rebuild_max_entity', 10);
    $in_use_by_type = [];
    foreach ($in_use_list as $uuid => $type) {
      // Add a new entry on the entity type array if number of entity exceeds
      // rebuild_max_entity
      if(!isset($in_use_by_type[$type]) || count(end($in_use_by_type[$type])) >= $entity_to_process) {
        $in_use_by_type[$type][][] = $uuid;
      }
      else {
        $end = end($in_use_by_type[$type]);
        $in_use_by_type[$type][key($end)][] = $uuid;
      }
    }

    // Save entities that use these entities.
    foreach ($in_use_by_type as $entity_type_id => $uuids_list) {

      $entity_type_storage = $this->entityTypeManager->getStorage($entity_type_id);
      $entity_type = $entity_type_storage->getEntityType();
      if($entity_type instanceof ConfigEntityTypeInterface) {
        foreach ($uuids_list as $uuids) {

          $ids = $entity_type_storage->getQuery()
            ->accessCheck(FALSE)
            ->condition('status', TRUE)
            ->condition('modified', TRUE)
            ->condition('uuid', $uuids, 'IN')
            ->execute();

          if(!empty($ids)) {
            $operations[] = [
              '_resave_config_entity', [$ids, $entity_type_id, FALSE],
            ];
          }
        }
      }elseif ($entity_type instanceof ContentEntityTypeInterface) {
        foreach ($uuids_list as $uuids) {

          $content_ids = $entity_type_storage->getQuery()
            ->accessCheck(FALSE)
            ->condition('uuid', $uuids, 'IN')
            ->execute();

          $ids = $this->entityTypeManager->getStorage('cohesion_layout')->getQuery()
            ->accessCheck(FALSE)
            ->condition('parent_type', $entity_type_id)
            ->condition('parent_id', $content_ids, 'IN')
            ->execute();

          if(!empty($ids)) {
            $operations[] = [
              '_resave_cohesion_layout_entity',
              [$ids, FALSE],
            ];
          }
        }
      }
    }

    return $operations;
  }

  /**
   * Start the batch process.
   *
   * @param $context
   */
  public static function startCallback(&$context) {
    $running_dx8_batch = &drupal_static('running_dx8_batch');
    // Initial state.
    $running_dx8_batch = TRUE;

    // Copy the live stylesheet.json to temporary:// so styles don't get wiped
    // when  re-importing.
    \Drupal::service('cohesion.local_files_manager')->liveToTemp();
  }

  /**
   * The batch run has finished. Clean up and show a status message.
   *
   * @param $success
   * @param $results
   * @param $operations
   */
  public static function finishedCallback($success, $results, $operations) {
    $messenger = \Drupal::messenger();

    // The 'success' parameter means no fatal PHP errors were detected. All
    // other error management should be handled using 'results'.
    if ($success) {
      // Stop the batch.
      $running_dx8_batch = &drupal_static('running_dx8_batch');
      $running_dx8_batch = TRUE;

      // Copy the stylesheets back.
      /** @var LocalFilesManager $local_file_manager */
      $local_file_manager = \Drupal::service('cohesion.local_files_manager');
      $local_file_manager->tempToLive();
      \Drupal::service('cohesion.template_storage')->commit();

      // Workaround for cache flush clearing existing messages.
      $all = $messenger->all();
      drupal_flush_all_caches();
      foreach ($all as $status => $messages) {
        foreach ($messages as $markup) {
          $messenger->addMessage($markup, $status);
        }
      }

      Cache::invalidateTags(['dx8-form-data-tag']);
      $message = t('Entities in use have been rebuilt.');
    }
    else {
      $message = t('Entities in use rebuild failed to complete.');
    }

    $messenger->addMessage($message);
  }

  /**
   * The entire render cache needs clearing when rebuilding website settings
   * because the rebuild is not recursive (it only rebuilds entities that
   * directly reclare their use of a website settings). For example, a website
   * settings could be used in a style and that style can be used on an entity,
   * there is a chance that an entity will not show an updated website settings.
   *
   * @param $context
   */
  public static function clearRenderCache(&$context) {
    $context['message'] = t('Flushing render cache.');

    if (!isset($context['results']['error'])) {
      $renderCache = \Drupal::service('cache.render');
      $renderCache->invalidateAll();
    }
  }

}
