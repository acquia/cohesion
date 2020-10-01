<?php

namespace Drupal\cohesion_sync\Form;

use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\cohesion\UsagePluginManager;
use Drupal\cohesion_sync\PackagerManager;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class ExportFormBase.
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
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_manager, StorageInterface $config_storage, PackagerManager $packager_manager, EntityRepository $entity_repository, UsagePluginManager $usage_plugin_manager) {
    parent::__construct($config_factory);
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
    return new static($container->get('config.factory'), $container->get('entity_type.manager'), $container->get('config.storage'), $container->get('cohesion_sync.packager'), $container->get('entity.repository'), $container->get('plugin.manager.usage.processor'));
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
    \Drupal::messenger()->addWarning($this->t('Entities available for export have not been defined. <a href="/admin/cohesion/sync/export_settings">Click here to go to the export settings.</a>'), 'warning');
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
   * Download and push buttons.
   *
   * @param $form
   */
  protected function addActionsToForm(&$form) {
    $form['actions'] = [
      '#type' => 'actions',
      'download' => [
        '#type' => 'submit',
        '#value' => $this->t('Download file'),
        '#button_type' => 'primary',
        // '#disabled' => TRUE
      ],
    ];
  }

}
