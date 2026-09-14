<?php

namespace Drupal\cohesion\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\system\SystemManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Asset groups to be imported.
 */
const JSON_ASSET_GROUPS = ["form_defaults", "in_context", "frontend_edit",
  "website_settings", "base_styles", "custom_styles", "master_template",
  "node_layout", "component_content", "template", "static_assets",
  "element_properties", "style_builder", "form_elements", "form_element_forms",
  "form_validation", "elements", "element_validation", "element_categories",
  "element_forms", "resources", "property_group_options", "element_templates",
  "helper", "component", "custom_component", "style_guide",
  "canvas", "js_settings",
];

/**
 * Class AdministrationController.
 *
 * Controller routines for Administration settings page.
 *
 * @package Drupal\cohesion\Controller
 */
class AdministrationController extends ControllerBase {

  /**
   * System Manager Service.
   *
   * @var \Drupal\system\SystemManager
   */
  protected $systemManager;

  /**
   * Constructs a new SystemController.
   *
   * AdministrationController constructor.
   *
   * @param \Drupal\system\SystemManager $systemManager
   */
  public function __construct(SystemManager $systemManager) {
    $this->systemManager = $systemManager;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new self($container->get('system.manager'));
  }

  /**
   * Constructs a page with placeholder content.
   *
   * @return array
   */
  public function index() {
    return $this->systemManager->getBlockContents();
  }

  /**
   * Import Site Studio assets for the API and create entities.
   *
   * @param bool $cron
   *
   * @return array|null|\Symfony\Component\HttpFoundation\RedirectResponse
   */
  public static function batchAction($cron = FALSE) {
    // Clean the scratch directory.
    \Drupal::service('cohesion.local_files_manager')->resetScratchDirectory();

    $operations = [];
    $operations[] = ['cohesion_website_settings_process_batch_import_start', []];

    // Batch process assets in groups.
    foreach (JSON_ASSET_GROUPS as $group) {
      $data = [
        'data' => ['element_group' => $group],
      ];
      $operations[] = [
        'cohesion_website_settings_process_batch_import',
        [$data],
      ];
    }

    $operations[] = ['cohesion_website_settings_process_batch', []];
    // Enable website settings.
    $operations[] = [
      'cohesion_website_settings_enable_website_settings',
      [
        [
          'base_unit_settings',
          'responsive_grid_settings',
          'color_palette',
          'font_libraries',
        ],
      ],
    ];

    $operations[] = ['cohesion_base_styles_process_batch', []];
    $operations[] = ['cohesion_custom_styles_process_batch', []];
    $operations[] = ['cohesion_templates_process_batch', []];
    $operations[] = ['cohesion_templates_generate_content_process_batch', []];
    $operations[] = ['cohesion_templates_secure_directory', []];

    // Run the batch import.
    $batch = [
      'title' => t('Importing elements'),
      'operations' => $operations,
      'finished' => 'cohesion_website_settings_batch_import_finished',
      'file' => \Drupal::service('extension.path.resolver')->getPath('module', 'cohesion_website_settings') . '/cohesion_website_settings.module',
    ];

    if ($cron) {
      return $batch;
    }
    else {
      batch_set($batch);
      return batch_process(Url::fromRoute('cohesion.configuration.account_settings')->toString());
    }
  }

}
