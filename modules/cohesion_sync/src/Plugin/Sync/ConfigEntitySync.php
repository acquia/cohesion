<?php

namespace Drupal\cohesion_sync\Plugin\Sync;

use Drupal\cohesion\EntityUpdateManager;
use Drupal\cohesion\UsageUpdateManager;
use Drupal\cohesion_sync\SyncConfigImporter;
use Drupal\cohesion_sync\SyncPluginBase;
use Drupal\config\StorageReplaceDataWrapper;
use Drupal\Core\Config\ConfigImporterException;
use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Config\StorageComparer;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\Extension\ThemeExtensionList;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Config entity sync plugin.
 *
 * @package Drupal\cohesion_sync
 *
 * @Sync(
 *   id = "config_entity_sync",
 *   name = @Translation("Config entity sync"),
 *   interface = "Drupal\Core\Config\Entity\ConfigEntityInterface"
 * )
 */
class ConfigEntitySync extends SyncPluginBase {

  /**
   * Additional core config types to include in the dependency list.
   */
  const ADDITIONAL_CONFIG_TYPES = [
    'entity_form_display',
    'entity_view_display',
    'entity_view_mode',
  ];

  /**
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $configStorage;

  /**
   * @var \Drupal\Core\Config\ConfigManagerInterface
   */
  protected $configManager;

  /**
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * @var \Drupal\Core\Lock\LockBackendInterface
   */
  protected $lock;

  /**
   * @var \Drupal\Core\Config\TypedConfigManagerInterface
   */
  protected $typedConfigManager;

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  protected $moduleInstaller;

  /**
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected $stringTranslation;

  /**
   * @var \Drupal\cohesion\UsageUpdateManager
   */
  protected $usageUpdateManager;

  /**
   * @var \Drupal\cohesion\EntityUpdateManager
   */
  protected $entityUpdateManager;

  /**
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected $extensionListModule;

  /**
   * @var \Drupal\Core\Extension\ThemeExtensionList
   */
  protected $extensionListTheme;

  /**
   * ConfigEntitySync constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\Core\Entity\EntityRepository $entity_repository
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   * @param \Drupal\Core\Config\StorageInterface $config_storage
   * @param \Drupal\Core\Config\ConfigManagerInterface $config_manager
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typed_config
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   * @param \Drupal\Core\Extension\ModuleInstallerInterface $module_installer
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   * @param \Drupal\cohesion\UsageUpdateManager $usage_update_manager
   * @param \Drupal\cohesion\EntityUpdateManager $entity_update_manager
   * @param \Drupal\Core\Extension\ModuleExtensionList $extension_list_module
   * @param \Drupal\Core\Extension\ThemeExtensionList $extension_list_theme
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityRepository $entity_repository, EntityTypeManagerInterface $entity_type_manager, TranslationInterface $string_translation, StorageInterface $config_storage, ConfigManagerInterface $config_manager, EventDispatcherInterface $event_dispatcher, LockBackendInterface $lock, TypedConfigManagerInterface $typed_config, ModuleHandlerInterface $module_handler, ModuleInstallerInterface $module_installer, ThemeHandlerInterface $theme_handler, UsageUpdateManager $usage_update_manager, EntityUpdateManager $entity_update_manager, ModuleExtensionList $extension_list_module, ThemeExtensionList $extension_list_theme) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_repository, $entity_type_manager, $string_translation);
    $this->configStorage = $config_storage;
    $this->configManager = $config_manager;
    $this->eventDispatcher = $event_dispatcher;
    $this->lock = $lock;
    $this->typedConfigManager = $typed_config;
    $this->moduleHandler = $module_handler;
    $this->moduleInstaller = $module_installer;
    $this->themeHandler = $theme_handler;
    $this->stringTranslation = $string_translation;
    $this->usageUpdateManager = $usage_update_manager;
    $this->entityUpdateManager = $entity_update_manager;
    $this->extensionListModule = $extension_list_module;
    $this->extensionListTheme = $extension_list_theme;
  }

  /**
   * Static create method.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.repository'),
      $container->get('entity_type.manager'),
      $container->get('string_translation'),
      $container->get('config.storage'),
      $container->get('config.manager'),
      $container->get('event_dispatcher'),
      $container->get('lock.persistent'),
      $container->get('config.typed'),
      $container->get('module_handler'),
      $container->get('module_installer'),
      $container->get('theme_handler'),
      $container->get('cohesion_usage.update_manager'),
      $container->get('cohesion.entity_update_manager'),
      $container->get('extension.list.module'),
      $container->get('extension.list.theme')
    );
  }

  /**
   * {@inheritdoc}
   *
   * @testme
   */
  public function buildExport($entity) {
    /** @var \Drupal\Core\Config\Entity\ConfigEntityInterface $entity */
    return $this->configStorage->read($entity->getConfigDependencyName());
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies($entity) {
    /** @var \Drupal\Core\Config\Entity\ConfigEntityInterface $entity */
    $dependencies = [];

    $usage_dependencies = $this->usageUpdateManager->getDependencies($entity);
    foreach ($usage_dependencies as $usage_dependency) {
      if (in_array($usage_dependency['type'], self::ADDITIONAL_CONFIG_TYPES)) {
        $results = $this->entityTypeManager->getStorage($usage_dependency['type'])
          ->loadByProperties(['uuid' => $usage_dependency['uuid']]);
        $dependant_entity = reset($results);
        $dependencies['config'][] = [
          'key' => $dependant_entity->getConfigDependencyKey(),
          'dependency_name' => $dependant_entity->getConfigDependencyName(),
          'type' => $usage_dependency['type'],
          'id' => $dependant_entity->id(),
          'uuid' => $dependant_entity->uuid(),
        ];
      }
    }

    // Loop through dependencies returned by the Usage table.
    foreach ($this->usageUpdateManager->buildConfigEntityDependencies($entity) as $item) {
      // Add the entity as a config dependency.
      // $this->addDependency($item['key'], $item['name']);.
      $dependencies[$item['key']][] = $item;
    }

    return $dependencies;
  }

  /**
   * Validate the entry will apply (can throw ConfigImporterException).
   *
   * @param $entry
   * @param \Drupal\cohesion_sync\SyncConfigImporter $config_importer
   */
  private function validate($entry, SyncConfigImporter $config_importer) {
    // Check this doesn't have an entityupdate setting higher than the highest
    // available entity update script on this site.
    if (isset($entry['last_entity_update'])) {
      // Has the imported entity run a higher entityupdate_xxxx script that is
      // available on this site (is newer)?
      if (!$this->entityUpdateManager->pluginIdInRange($entry['last_entity_update'])) {
        throw new ConfigImporterException('This package contains entities created with a later version of Site Studio. Upgrade this site to the latest version of Site Studio before attempting to import this package.');
      }
    }

    // Validate using Drupal core (can throw).
    $config_importer->validate();
  }

  /**
   * {@inheritdoc}
   */
  public function validatePackageEntryShouldApply($entry) {
    parent::validatePackageEntryShouldApply($entry);

    // Validate the entry could be imported.
    $config_name = $this->entityTypeDefinition->getConfigPrefix() . '.' . $entry[$this->id_key];

    // Check the configuration is valid.
    $source_storage = new StorageReplaceDataWrapper($this->configStorage);
    $source_storage->replaceData($config_name, $entry);
    $storage_comparer = new StorageComparer(
      $source_storage,
      $this->configStorage,
      $this->configManager
    );

    // Entity is new or has no changes.
    $config_importer = new SyncConfigImporter(
      $storage_comparer,
      $this->eventDispatcher,
      $this->configManager,
      $this->lock,
      $this->typedConfigManager,
      $this->moduleHandler,
      $this->moduleInstaller,
      $this->themeHandler,
      $this->stringTranslation,
      $this->extensionListModule,
      $this->extensionListTheme
    );

    // If it's a custom style, ask the user what they want to do.
    // Special case as it needs to be loaded by class name
    if ($this->entityTypeStorage->getEntityTypeId() === 'cohesion_custom_style') {
      // Load any entities that contain the same class name.
      $custom_style_ids = \Drupal::entityQuery('cohesion_custom_style')
        ->accessCheck(TRUE)
        ->condition('class_name', $entry['class_name'])
        ->execute();

      // Found a custom style with this class.
      if (count($custom_style_ids) > 0) {
        // Attempt to load the entity and check if it's locked.
        if ($entity = $this->entityTypeStorage->load(reset($custom_style_ids))) {
          // Is this entity locked?
          if (method_exists($entity, 'isLocked')) {
            // If so, ignore updating it.
            if ($entity->isLocked()) {
              return ENTRY_EXISTING_LOCKED;
            }
          }
        }

        // Is it identical to the local entity?
        if ($storage_comparer->createChangelist()->hasChanges()) {
          // Make sure this will apply.
          try {
            $config_importer->validate();
          }
          catch (ConfigImporterException $e) {
            throw new \Exception(strip_tags($e->getMessage()));
          }

          // Ask the user what to do.
          return ENTRY_EXISTING_ASK;
        }
        // No changes compared to the existing entity, so ignore it.
        else {
          return ENTRY_EXISTING_NO_CHANGES;
        }
      }
    }

    // If there is an existing entity, ensure matching ID and UUID.
    /** @var \Drupal\Core\Config\Entity\ConfigEntityBase $entity */
    if ($entity = $this->entityTypeStorage->load($entry[$this->id_key])) {
      // Is this entity locked?
      if (method_exists($entity, 'isLocked')) {
        // If so, ignore updating it.
        if ($entity->isLocked()) {
          return ENTRY_EXISTING_LOCKED;
        }
      }

      // No UUID specified.
      if (!isset($entry['uuid'])) {
        throw new \Exception('An entity with this machine name already exists but the import did not specify a UUID.');
      }

      // Id exists, but UUID is different.
      if ($entry['uuid'] !== $entity->uuid()) {
        // Ask the user what to do.
        return ENTRY_EXISTING_ASK;
      }

      // There were changes compared to the existing entity.
      if ($storage_comparer->createChangelist()->hasChanges()) {
        // Make sure this will apply.
        try {
          $this->validate($entry, $config_importer);
        }
        catch (ConfigImporterException $e) {
          throw new \Exception(strip_tags($e->getMessage()));
        }

        // Ask the user what to do.
        return ENTRY_EXISTING_ASK;
      }
      // No changes compared to the existing entity, so ignore it.
      else {
        return ENTRY_EXISTING_NO_CHANGES;
      }

    }
    // UUID exists, but id is different.
    elseif (isset($entry['uuid']) && $source_entity_machine_name = $this->entityTypeStorage->loadByProperties(['uuid' => $entry['uuid']])) {
      $source_entity_machine_name = reset($source_entity_machine_name);
      throw new \Exception(sprintf('%s with UUID %s already exists but the machine name "%s" of the existing entity does not match the machine name "%s" of the entity being imported.',
        $this->entityTypeDefinition->getLabel(),
        $entry['uuid'],
        $source_entity_machine_name->id(),
        $entry['id']
      ));
    }
    // Entity is new.
    else {
      // Make sure it will apply.
      try {
        $this->validate($entry, $config_importer);
      }
      catch (ConfigImporterException $e) {
        throw new \Exception(strip_tags($e->getMessage()));
      }

      // Apply the entry.
      return ENTRY_NEW_IMPORTED;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function applyPackageEntry($entry) {
    parent::applyPackageEntry($entry);
    // A custom style with this classname already exists.
    if ($this->entityTypeStorage->getEntityTypeId() === 'cohesion_custom_style') {
      try {
        $ids = \Drupal::entityQuery('cohesion_custom_style')
          ->accessCheck(TRUE)
          ->condition('class_name', $entry['class_name'])
          ->execute();

        /** @var \Drupal\cohesion_custom_styles\Entity\CustomStyle $existing_entity */
        // Key must be an array of string or integer otherwise loadMultiple()
        // throws a warning.
        if ($key = key($ids)) {
          if ($existing_entity = $this->entityTypeStorage->load($key)) {
            $existing_entity->delete();
          }
        }
      }
      catch (\Throwable $e) {
        return;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getActionData($entry, $action_state, $type) {
    $action_data = parent::getActionData($entry, $action_state, $type);

    $action_data['entity_type_label'] = $this->entityTypeDefinition->getLabel()
      ->__toString();
    $action_data['entity_label'] = $entry['label'] ?? $entry['name'] ?? $entry['id'];

    $config_name = $this->entityTypeDefinition->getConfigPrefix() . '.' . $entry[$this->id_key];
    $config = $this->configStorage->read($config_name);
    if ($config && $config['uuid'] != $entry['uuid']) {
      $action_data['replace_uuid'] = $config['uuid'];
    }

    return $action_data;
  }

}
