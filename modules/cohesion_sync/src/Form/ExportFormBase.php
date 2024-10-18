<?php

namespace Drupal\cohesion_sync\Form;

use Drupal\cohesion\UsagePluginManager;
use Drupal\cohesion_sync\PackagerManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Link;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sync Export form base.
 *
 * @package Drupal\cohesion_sync\Form
 */
abstract class ExportFormBase extends ConfigFormBase {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The config storage.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $configStorage;

  /**
   * @var \Drupal\cohesion_sync\PackagerManager
   */
  protected $packagerManager;

  /**
   * @var \Drupal\Core\Entity\EntityRepository
   */
  protected $entityRepository;

  /**
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  protected $configSyncSettings;

  /**
   * @var \Drupal\cohesion\UsagePluginManager
   */
  protected $usagePluginManager;

  /**
   * ExportFormBase constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   * @param \Drupal\Core\Config\StorageInterface $config_storage
   * @param \Drupal\cohesion_sync\PackagerManager $packager_manager
   * @param \Drupal\Core\Entity\EntityRepository $entity_repository
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typed_configmanager
   *   The typed config manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_manager, StorageInterface $config_storage, PackagerManager $packager_manager, EntityRepository $entity_repository, UsagePluginManager $usage_plugin_manager, TypedConfigManagerInterface $typed_configmanager) {
    parent::__construct($config_factory, $typed_configmanager);
    $this->entityTypeManager = $entity_manager;
    $this->configStorage = $config_storage;
    $this->packagerManager = $packager_manager;
    $this->entityRepository = $entity_repository;

    $this->configSyncSettings = $this->config('cohesion.sync.settings');

    $this->usagePluginManager = $usage_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('config.storage'),
      $container->get('cohesion_sync.packager'),
      $container->get('entity.repository'),
      $container->get('plugin.manager.usage.processor'),
      $container->get('config.typed')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'cohesion.sync.settings',
    ];
  }

  /**
   * Show no entity types message with link.
   */
  protected function showNoEntityTypesMessage() {

    $args = [
      '@link' => Link::createFromRoute('Click here to go to the export settings.', 'cohesion_sync.export_settings')->toString(),
    ];

    \Drupal::messenger()->addWarning($this->t('Entities available for export have not been defined. @link', $args), TRUE);
  }

  /**
   * Have the entity types been enabled and they're not blank.
   *
   * @return array|mixed|null
   */
  protected function entityTypesAvailable() {
    if ($types = $this->configSyncSettings->get('enabled_entity_types')) {
      foreach ($types as $count) {
        if ($count) {
          return TRUE;
        }
      }
    }

    return FALSE;
  }

  /**
   * Legacy download button.
   *
   * @param $form
   */
  protected function addLegacyActionsToForm(&$form) {
    $form['legacy']['actions'] = [
      '#type' => 'actions',
      'legacy_download' => [
        '#type' => 'submit',
        '#value' => $this->t('Download legacy file'),
        '#button_type' => 'primary',
        '#id' => 'legacy_download',
      ],
    ];
  }

  /**
   * Download and remove buttons.
   *
   * @param string $label
   *   Value to use as a button for file generation.
   *
   * @return array
   *   Actions for the form.
   */
  protected function addActions(string $label, bool $disableRemoveButton = FALSE): array {
    $actions = [
      '#type' => 'actions',
      'download' => [
        '#type' => 'submit',
        '#value' => $this->t(':label', [':label' => $label]),
        '#button_type' => 'primary',
        '#id' => 'generate',
      ],
      'remove' => [
        '#type' => 'submit',
        '#value' => $this->t('Remove file'),
        '#button_type' => 'danger',
        '#id' => 'remove',
        '#attributes' => ['title' => t('Removes package file from the server file system.')],
      ],
    ];
    if ($disableRemoveButton) {
      $actions['remove']['#attributes']['disabled'] = 'disabled';
    }

    return $actions;
  }

}
