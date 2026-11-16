<?php

namespace Drupal\cohesion_sync\Form;

use Drupal\cohesion\UsagePluginManager;
use Drupal\cohesion_sync\Config\CohesionFullPackageStorage;
use Drupal\cohesion_sync\Controller\PackageExportGenerateController;
use Drupal\cohesion_sync\Entity\Package;
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
class ExportPackageForm extends ExportFormBase {

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
   * Package entity.
   *
   * @var \Drupal\cohesion_sync\Entity\Package
   */
  protected $package;

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
   * @param \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   *   Date Formatter service.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typed_config_manager
   *    The typed config manager.
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
    TypedConfigManagerInterface $typed_config_manager,
  ) {
    parent::__construct(
      $config_factory,
      $entity_manager,
      $config_storage,
      $packager_manager,
      $entity_repository,
      $usage_plugin_manager,
      $typed_config_manager
    );

    $this->configSyncSettings = $this->config('cohesion.sync.settings');
    $this->usagePluginManager = $usage_plugin_manager;
    $this->fileSystem = $file_system;
    $this->fullPackageStorage = $cohesion_full_package_storage;
    $this->moduleHandler = $moduleHandler;
    $this->state = $state;
    $this->dateFormatter = $dateFormatter;
    $this->typedConfigManager = $typed_config_manager;
  }

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
      $container->get('config.typed'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dx8_sync_export_package_form';
  }

  /**
   * Gets filename of this export.
   *
   * @return string
   *   Filename using site name and .tar.gz as its postfix.
   */
  private function getExportFilename() {
    return $this->package->id() . '.tar.gz';
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
  public function buildForm(array $form, FormStateInterface $form_state, ?Package $package = NULL) {
    if ($package === NULL) {
      $this->messenger()->addError('Package export page requires existing package entity.');
      $this->redirect('entity.cohesion_sync_package.collection');
    }
    $this->package = $package;
    $this->packageStateKey = PackageExportGenerateController::getPackageStateKey($this->package->id());

    $form['return'] = [
      '#type' => 'link',
      '#title' => $this->t('Back to package list'),
      '#url' => Url::fromRoute('entity.cohesion_sync_package.collection'),
      '#attributes' => [
        'class' => ['button', 'button--primary'],
      ],
    ];

    $form['full_package_export'] = [
      '#type' => 'details',
      '#title' => $this
        ->t('Package export'),
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
        '#plain_text' => $this->t('Package ":label" generated as .tar.gz', [':label' => $this->package->label()]),
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
        '#url' => Url::fromRoute('cohesion_sync.export_all.download', ['filename' => $this->getExportFilename($package)]),
      ];
      $form['full_package_export']['actions'] = $this->addActions('Regenerate file');
      $form['#attached']['library'][] = 'cohesion_sync/full-package-export-form';
    }
    else {
      $this->deleteFile();
      $form['full_package_export']['#description'] = $this->t('Please use "Generate file" to prepare package export file for download.');
      $form['full_package_export']['actions'] = $this->addActions('Generate file', TRUE);
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    switch ($form_state->getTriggeringElement()['#id']) {
      case 'generate':
        $form_state->setRedirect('cohesion_sync.export.generate_package', ['package' => $this->package->id()]);
        break;

      case 'remove':
        $this->state->delete($this->packageStateKey);
        $this->deleteFile();
        $this->messenger()->addStatus($this->t('Package file has been successfully removed.'));
        break;
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
