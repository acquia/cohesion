<?php

namespace Drupal\cohesion_sync\Form;

use Drupal\cohesion\Entity\CohesionSettingsInterface;
use Drupal\cohesion\UsagePluginManager;
use Drupal\cohesion_sync\Config\CohesionFullPackageStorage;
use Drupal\cohesion_sync\Controller\PackageExportGenerateController;
use Drupal\cohesion_sync\PackagerManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\Exception\FileException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\ByteSizeMarkup;

/**
 * Provides a form for exporting a single configuration file.
 *
 * @internal
 */
class ExportAllForm extends ExportFormBase {

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Cohesion Storage service.
   *
   * @var \Drupal\cohesion_sync\Config\CohesionFullPackageStorage
   */
  protected $fullPackageStorage;

  /**
   * Module Handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The state store.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Site name in a format suitable for filename.
   *
   * @var string
   */
  protected $siteName;

  /**
   * Package file URI in temporary directory.
   *
   * @var string
   */
  protected $packageStateKey;

  /**
   * Date Formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  protected $cohesionSettings;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('config.storage'),
      $container->get('cohesion_sync.packager'),
      $container->get('entity.repository'),
      $container->get('plugin.manager.usage.processor'),
      $container->get('file_system'),
      $container->get('cohesion_sync.full_package_storage'),
      $container->get('module_handler'),
      $container->get('state'),
      $container->get('date.formatter'),
      $container->get('config.typed')
    );
  }

  /**
   * ExportFormBase constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   Entity Manager.
   * @param \Drupal\Core\Config\StorageInterface $config_storage
   *   Config storage.
   * @param \Drupal\cohesion_sync\PackagerManager $packager_manager
   *   Package manager.
   * @param \Drupal\Core\Entity\EntityRepository $entity_repository
   *   Entity Repository.
   * @param \Drupal\cohesion\UsagePluginManager $usage_plugin_manager
   *   Usage plugin manager.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system.
   * @param \Drupal\cohesion_sync\Config\CohesionFullPackageStorage $cohesion_full_package_storage
   *   Cohesion Storage service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   Module Handler service.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state key value store.
   * @paran \Drupal\Core\Datetime\DateFormatterInterface
   *   Date Formatter service.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typed_configmanager
   *   The typed config manager.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    EntityTypeManagerInterface $entity_manager,
    StorageInterface $config_storage,
    PackagerManager $packager_manager,
    EntityRepository $entity_repository,
    UsagePluginManager $usage_plugin_manager,
    FileSystemInterface $file_system,
    CohesionFullPackageStorage $cohesion_full_package_storage,
    ModuleHandlerInterface $moduleHandler,
    StateInterface $state,
    DateFormatterInterface $dateFormatter,
    TypedConfigManagerInterface $typed_configmanager,
  ) {
    parent::__construct(
      $config_factory,
      $entity_manager,
      $config_storage,
      $packager_manager,
      $entity_repository,
      $usage_plugin_manager,
      $typed_configmanager
    );

    $this->configSyncSettings = $this->config('cohesion.sync.settings');
    $this->cohesionSettings = $this->config('cohesion.settings');
    $this->usagePluginManager = $usage_plugin_manager;
    $this->fileSystem = $file_system;
    $this->fullPackageStorage = $cohesion_full_package_storage;
    $this->moduleHandler = $moduleHandler;
    $this->state = $state;
    $this->siteName = preg_replace('/[^a-z0-9]+/', '-', strtolower($this->config('system.site')->get('name')));
    $this->dateFormatter = $dateFormatter;
    $this->packageStateKey = PackageExportGenerateController::getPackageStateKey($this->siteName);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dx8_sync_export_all_form';
  }

  /**
   * Gets legacy filename of this export.
   *
   * @return string
   *   Filename using site name and .package.yml as its postfix.
   */
  private function getLegacyExportFilename() {
    return $this->getSiteName() . '.package.yml';
  }

  /**
   * Gets filename of this export.
   *
   * @return string
   *   Filename using site name and .tar.gz as its postfix.
   */
  private function getExportFilename() {
    return $this->getSiteName() . '.tar.gz';
  }

  /**
   * Get site name.
   *
   * @return string
   *   Cleaned up site name to use in filename
   */
  private function getSiteName(): string {
    return $this->siteName;
  }

  /**
   * Get File URI.
   *
   * @return string
   *   Gets file URI in temporary directory.
   */
  private function getFileUri(): string {
    return 'temporary://' . $this->getExportFilename();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['help'] = [
      '#markup' => $this->t('Export and download the full Site Studio configuration of this site including all dependencies and assets.'),
    ];

    if ($this->entityTypesAvailable() === FALSE) {
      $this->showNoEntityTypesMessage();

      return $form;
    }

    $form['full_package_export'] = [
      '#type' => 'details',
      '#title' => $this
        ->t('Full package export'),
      '#open' => TRUE,
    ];

    if ($this->state->get($this->packageStateKey) && is_file($this->getFileUri())) {
      $stats = stat($this->getFileUri());
      $form['full_package_export']['file'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['file-description-container'],
        ],
      ];
      $form['full_package_export']['file']['label'] = [
        '#type' => 'item',
        '#plain_text' => 'Full package export generated',
      ];
      $form['full_package_export']['file']['size'] = [
        '#type' => 'item',
        '#plain_text' => 'File size: ' . ByteSizeMarkup::create($stats['size']),
      ];
      $form['full_package_export']['file']['changed'] = [
        '#type' => 'item',
        '#plain_text' => 'File last generated ' . $this->dateFormatter->format($stats['mtime']),
      ];
      $form['full_package_export']['file']['link'] = [
        '#title' => $this
          ->t('Download :filename', [':filename' => $this->getExportFilename()]),
        '#type' => 'link',
        '#url' => Url::fromRoute('cohesion_sync.export_all.download', ['filename' => $this->getExportFilename()]),
      ];
      $form['full_package_export']['actions'] = $this->addActions('Regenerate file');
      $form['#attached']['library'][] = 'cohesion_sync/full-package-export-form';
    }
    else {
      $this->deleteFile();
      $form['full_package_export']['#description'] = $this->t('Please use "Generate file" to prepare package export file for download.');
      $form['full_package_export']['actions'] = $this->addActions('Generate file', TRUE);
    }

    // Legacy package export.
    // Only show Legacy export if user has turned it on.
    if ($this->cohesionSettings->get('sync_legacy_visibility')) {
      $form['legacy'] = [
        '#type' => 'details',
        '#title' => $this
          ->t('Legacy full package export'),
        '#open' => FALSE,
      ];
      $form['legacy']['filename'] = [
        '#prefix' => '<p><em class="placeholder">',
        '#suffix' => '</em></p>',
        '#markup' => $this->getLegacyExportFilename(),
      ];
      $this->addLegacyActionsToForm($form);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    switch ($form_state->getTriggeringElement()['#id']) {
      case 'generate':
        $form_state->setRedirect('cohesion_sync.export_all.generate_full_package');
        break;

      case 'remove':
        $this->state->delete($this->packageStateKey);
        $this->deleteFile();
        $this->messenger()->addStatus($this->t('Package file has been successfully removed.'));
        break;

      case 'legacy_download':
        $this->handleLegacyDownloadSubmit($form, $form_state);
    }
  }

  /**
   * Handles legacy package downloads.
   *
   * @param array $form
   *   Form render array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form State object.
   */
  protected function handleLegacyDownloadSubmit(array &$form, FormStateInterface $form_state) {
    // Build the excluded entity types up.
    $excluded_entity_type_ids = [];
    foreach ($this->configSyncSettings->get('enabled_entity_types') as $entity_type_id => $enabled) {
      if (!$enabled) {
        $excluded_entity_type_ids[] = $entity_type_id;
      }
    }

    // Loop over each entity type to get all the entities.
    $entities = [];
    foreach ($this->entityTypeManager->getDefinitions() as $entity_type => $definition) {
      if ($definition->entityClassImplements(CohesionSettingsInterface::class) && !in_array($entity_type, $excluded_entity_type_ids) && $entity_type !== 'custom_style_type') {
        try {
          $entity_storage = $this->entityTypeManager->getStorage($entity_type);
        }
        catch (\Exception $e) {
          continue;
        }

        /** @var \Drupal\Core\Config\Entity\ConfigEntityInterface $entity */
        foreach ($entity_storage->loadMultiple() as $entity) {
          if ($entity->status()) {
            $entities[] = $entity;
          }
        }
      }
    }

    // Force a download.
    $response = $this->packagerManager->sendYamlDownload($this->getLegacyExportFilename(), $entities, $excluded_entity_type_ids);
    try {
      $response->setContentDisposition('attachment', $this->getLegacyExportFilename());
      $form_state->setResponse($response);
    }
    catch (\Throwable $e) {
      // Failed, to build, so ignore the response and just show the error.
    }
  }

  /**
   * Attempts to delete existing package file.
   */
  private function deleteFile() {
    try {
      $this->fileSystem->delete($this->getFileUri());
    }
    catch (FileException $e) {
      // Ignore failed deletes.
    }
  }

}
