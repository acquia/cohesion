<?php

namespace Drupal\cohesion_sync\EventSubscriber\Import;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\EntityUpdateManager;
use Drupal\cohesion\UsageUpdateManager;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Config\ConfigImporter;
use Drupal\Core\Config\ConfigImporterEvent;
use Drupal\Core\Config\Entity\ConfigEntityTypeInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\TranslationManager;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Base class for config import subscribers that handle validation.
 *
 * @package Drupal\cohesion_sync\EventSubscriber
 */
abstract class CohesionConfigImportSubscriberBase implements EventSubscriberInterface {

  const ERROR_MESSAGE = "Cannot import @entity_type '@label' (id: @id). This entity is missing populated fields. If you proceed, content in these fields will be lost.";

  const ERROR_VERSION_MESSAGE = "Cannot import @entity_name. This entity has been created with a later version of Site Studio. Upgrade this entity to the latest version of Site Studio before attempting to import.";

  /**
   * Content types.
   *
   * @var array
   */
  protected $contentTypes = [];

  /**
   * Config types.
   *
   * @var array
   */
  protected $configTypes = [];

  /**
   * EntityTypeManager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Extra Validation flag.
   *
   * @var bool
   */
  protected $validationFlag;

  /**
   * UsageUpdateManager service.
   *
   * @var \Drupal\cohesion\UsageUpdateManager
   */
  protected $usageUpdateManager;

  /**
   * The current active database's master connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Translation Manager.
   *
   * @var \Drupal\Core\StringTranslation\TranslationManager
   */
  protected $translationManager;

  /**
   * Entity Update Manager
   *
   * @var \Drupal\cohesion\EntityUpdateManager
   */
  protected $entityUpdateManager;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ConfigEvents::IMPORT_VALIDATE][] = ['onConfigImporterValidate', 20];
    return $events;
  }

  /**
   * Checks if config change breaks entities and returns array of them if so.
   *
   * @param array $in_use_list
   *   Array of entities used by config.
   * @param array $source_config
   *   Array containing updated config.
   * @param array $target_config
   *   Array containing current config.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *
   * @return array
   *   Array of broken entities, keyed by UUIDs.
   */
  abstract protected function checkForBrokenEntities(
    array $in_use_list,
    array $source_config,
    array $target_config,
  ) : array;

  /**
   * CohesionConfigImportSubscriberBase constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   EntityTypeManager service.
   * @param \Drupal\cohesion\UsageUpdateManager $usageUpdateManager
   *   UsageUpdateManager service.
   * @param \Drupal\Core\Database\Connection $database
   *   The current active database's master connection.
   * @param \Drupal\Core\StringTranslation\TranslationManager $translationManager
   *   Translation manager service.
   * @param \Drupal\cohesion\EntityUpdateManager $entityUpdateManager
   *   Entity update manager service.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    UsageUpdateManager $usageUpdateManager,
    Connection $database,
    TranslationManager $translationManager,
    EntityUpdateManager $entityUpdateManager,
  ) {
    $cohesion_sync_import_options = &drupal_static('cohesion_sync_import_options');
    if (isset($cohesion_sync_import_options['extra-validation'])) {
      $this->validationFlag = (bool) $cohesion_sync_import_options['extra-validation'];
    }
    else {
      $this->validationFlag = FALSE;
    }

    $this->entityTypeManager = $entityTypeManager;
    $this->usageUpdateManager = $usageUpdateManager;
    $this->database = $database;
    $this->translationManager = $translationManager;
    $this->entityUpdateManager = $entityUpdateManager;
  }

  /**
   * Validates config items in incoming config import.
   *
   * @param \Drupal\Core\Config\ConfigImporterEvent $event
   *   Config Importer event.
   */
  public function onConfigImporterValidate(ConfigImporterEvent $event) {

    $config_importer = $event->getConfigImporter();
    $change_list = $event->getChangelist();

    foreach ($change_list as $op => $changes) {
      if (in_array($op, ['create', 'update'])) {
        foreach ($changes as $name) {
          $config = $config_importer->getStorageComparer()
            ->getSourceStorage()->read($name);
          if (isset($config['last_entity_update']) && !$this->entityUpdateManager->pluginIdInRange($config['last_entity_update'])) {
            $config_importer->logError(
              $this->translationManager->translate(self::ERROR_VERSION_MESSAGE, [
                '@entity_name' => $name,
              ])
            );
          }
          // If any elements are set to disabled we need to check if the package
          // contains any of the disabled elements to stop the import.
          if (isset($config['json_values'])) {
            $this->checkDisabledElements($config['json_values'], $config_importer);
          }
        }
      }
    }

    if (!$this->validationFlag) {
      return;
    }

    foreach ($config_importer->getUnprocessedConfiguration('update') as $name) {
      if (str_contains($name, $this::CONFIG_PREFIX) === FALSE) {
        continue;
      }

      $source_config = $config_importer->getStorageComparer()
        ->getSourceStorage()->read($name);
      $target_config = $config_importer->getStorageComparer()
        ->getTargetStorage()->read($name);
      if ($source_config['json_values'] === $target_config['json_values']) {
        continue;
      }

      $in_use_list = $this->usageUpdateManager
        ->getInUseEntitiesListByUuid($target_config['uuid']);
      if (empty($in_use_list)) {
        continue;
      }

      $broken_entities = $this->checkForBrokenEntities(
        $in_use_list,
        $source_config,
        $target_config
      );

      if (!empty($broken_entities)) {
        $this->handleBrokenEntities($broken_entities, $config_importer);
      }
    }
  }

  /**
   * @param $jsonValues
   * @param $config_importer
   *
   * Check for elements that have been disabled.
   */
  protected function checkDisabledElements($jsonValues, $config_importer) {
    $messages = [];
    if ($disabledElements = \Drupal::service('cohesion_element_usage.manager')->getDisabledElements()) {
      $json = Json::decode($jsonValues);

      foreach ($json['canvas'] as $item) {
        if (in_array($item['uid'], $disabledElements)) {
          $messages[] = $this->translationManager->translate('The %element element is used within this package, but it is currently disabled on this site. See <a href="@url" target="_blank">element toggle settings</a>.', [
            '%element' => $item['title'],
            '@url' => Url::fromRoute('cohesion_elements.configuration.elements_toggle_settings')->toString(),
          ]);
        }
      }

      if ($messages) {
        foreach ($messages as $message) {
          $config_importer->logError($message);
        }
      }
    }

  }

  /**
   * Loads CohesionLayout entities by using parent entity uuid and type.
   *
   * @param string $type
   *   Entity type.
   * @param string $uuid
   *   Entity UUID.
   *
   * @return \Drupal\cohesion\Entity\EntityJsonValuesInterface[]
   *   Array of CohesionLayout entities.
   */
  protected function getCohesionLayout(string $type, string $uuid): array {

    $entities = [];
    $failedToLoadContentType = FALSE;
    if ($this->isContentType($type)) {
      $parent_id = $this->database->select($this->contentTypes()[$type]['table'], $this->contentTypes()[$type]['alias'])
        ->condition($this->contentTypes()[$type]['uuid_field'], $uuid)
        ->fields($this->contentTypes()[$type]['alias'], [$this->contentTypes()[$type]['id_field']])
        ->execute()->fetchField();
      if ($parent_id !== FALSE) {
        $entities = $this->entityTypeManager->getStorage('cohesion_layout')
          ->loadByProperties([
            'parent_id' => $parent_id,
            'parent_type' => $type,
          ]);
      }
      else {
        $failedToLoadContentType = TRUE;
      }
    }
    if ($this->isConfigType($type) || $failedToLoadContentType) {
      $entities = $this->entityTypeManager->getStorage($type)->loadByProperties(['uuid' => $uuid]);
    }

    return array_filter($entities, function ($value) {
      return $value instanceof EntityJsonValuesInterface;
    });

  }

  /**
   * Checks if $type is for ConfigType entity definition.
   *
   * @param string $type
   *   Entity type.
   *
   * @return bool
   *   Boolean.
   */
  protected function isConfigType(string $type): bool {
    return isset($this->configTypes()[$type]);
  }

  /**
   * Checks if $type is for ContentType entity definition.
   *
   * @param string $type
   *   Entity type.
   *
   * @return bool
   *   Boolean.
   */
  protected function isContentType(string $type): bool {
    return isset($this->contentTypes()[$type]);
  }

  /**
   * Fetches contentTypes array.
   *
   * @return array
   *   Content Types.
   */
  protected function contentTypes(): array {
    if (empty($this->contentTypes)) {
      $this->buildTypeDefinitions();
    }
    return $this->contentTypes;
  }

  /**
   * Fetches configTypes array.
   *
   * @return array
   *   Config types.
   */
  protected function configTypes(): array {
    if (empty($this->configTypes)) {
      $this->buildTypeDefinitions();
    }
    return $this->configTypes;
  }

  /**
   * Builds contentTypes and configTypes arrays based on entity definitions.
   */
  protected function buildTypeDefinitions() {
    $definitions = $this->entityTypeManager->getDefinitions();

    foreach ($definitions as $definition) {
      if ($definition instanceof ContentEntityTypeInterface) {
        $table = $definition->getBaseTable();
        $keys = $definition->getKeys();
        if ($table !== NULL
          && isset($keys['id'])
          && isset($keys['uuid'])
        ) {
          $this->contentTypes[$definition->id()] = [
            'table' => $table,
            'alias' => $definition->id(),
            'id_field' => $keys['id'],
            'uuid_field' => $keys['uuid'],
          ];
        }
      }
      elseif ($definition instanceof ConfigEntityTypeInterface) {
        $this->configTypes[$definition->id()] = $definition->id();
      }
    }
  }

  /**
   * Handles error messages for broken entities.
   *
   * @param array $broken_entities
   *   Array of broken entities keyed by UUIDs.
   * @param \Drupal\Core\Config\ConfigImporter $configImporter
   *   ConfigImporter.
   */
  protected function handleBrokenEntities(array $broken_entities, ConfigImporter $configImporter) {
    foreach ($broken_entities as $broken_entity) {
      $configImporter->logError(
        $this->translationManager->translate(self::ERROR_MESSAGE, [
          '@entity_type' => $broken_entity['type'],
          '@label' => $broken_entity['label'],
          '@id' => $broken_entity['id'],
        ])
      );
      $configImporter->logError(
        $this->translationManager->formatPlural(
          count($broken_entity['affected_entities']),
          '1 entity affected:',
          '@count entities affected:'
        )
      );
      foreach ($broken_entity['affected_entities'] as $broken) {
        $configImporter->logError(
          $this->translationManager->translate(
            '@entity_type \'@label\' (id: @id)', [
              '@entity_type' => $broken['type'],
              '@label' => $broken['label'],
              '@id' => $broken['id'],
            ])
        );
      }
    }
  }

}
