<?php

namespace Drupal\cohesion\Services;

use Drupal\cohesion\UsageUpdateManager;
use Drupal\cohesion_base_styles\Entity\BaseStyles;
use Drupal\cohesion_custom_styles\Entity\CustomStyle;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

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
   * RebuildInuseBatch constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   */
  public function __construct(ModuleHandlerInterface $module_handler, UsageUpdateManager $usage_update_manager, EntityRepositoryInterface $entity_repository, TranslationInterface $stringTranslation) {
    $this->moduleHandler = $module_handler;
    $this->usageUpdateManager = $usage_update_manager;
    $this->entityRepository = $entity_repository;
    $this->stringTranslation = $stringTranslation;
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

    // Setup the batch.
    $batch = [
      'title' => $this->t('Rebuilding entities in use.'),
      'operations' => [],
      'finished' => '\Drupal\cohesion\Services\RebuildInuseBatch::finishedCallback',
    ];

    $batch['operations'][] = [
      '\Drupal\cohesion\Services\RebuildInuseBatch::startCallback',
      [],
    ];

    // Save the entities that have changed.
    foreach ($changed_entities as $entity) {
      // Only rebuild entities that have been activated.
      $batch['operations'][] = [
        '_resave_entity', [
          'entity' => $entity,
          'realsave' => TRUE,
        ],
      ];
    }

    // If style guide is used and the in use list contains a style guide manager instance
    // Get the style guide in use entities and add them to the list rather than the style guide manager entity
    // Ex: If a color is change and that color is used in a style guide manager instance then we need to rebuild
    // the entities where the token for this color is used rather than the style guide manager entity.
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

    $forms = [];

    // Save entities that use these entities.
    foreach ($in_use_list as $uuid => $type) {

      try {
        $entity = $this->entityRepository->loadEntityByUuid($type, $uuid);

        if ($entity instanceof ContentEntityInterface) {
          // Batch process the content entities.
          $batch['operations'][] = [
            '_resave_content_entity',
            ['entity' => $entity],
          ];
        }
        elseif ($entity instanceof CustomStyle || $entity instanceof BaseStyles) {
          $api_plugin = $entity->getApiPluginInstance();
          if ($api_plugin) {
            $api_plugin->setEntity($entity);
            $forms = array_merge($forms, $api_plugin->getForms());
          }
        }
        else {
          $batch['operations'][] = [
            '_resave_entity', [
              'entity' => $entity,
              'realsave' => FALSE,
            ],
          ];
        }

      }
      catch (\Exception $e) {
      }
    }

    $batch['operations'][] = [
      '_cohesion_style_save',
      ['forms' => $forms],
    ];

    $operations[] = ['cohesion_templates_secure_directory', []];

    // Clear the render cache.
    $batch['operations'][] = [
      '\Drupal\cohesion\Services\RebuildInuseBatch::clearRenderCache',
      [],
    ];

    // Setup and run the batch.
    return batch_set($batch);
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

    // Copy the live stylesheet.json to temporary:// so styles don't get wiped when  re-importing.
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
      drupal_flush_all_caches();
      Cache::invalidateTags(['dx8-form-data-tag']);
      $message = t('Entities in use have been rebuilt.');
    }
    else {
      $message = t('Entities in use rebuild failed to complete.');
    }

    \Drupal::messenger()->addMessage($message);
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
