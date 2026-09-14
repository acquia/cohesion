<?php

namespace Drupal\cohesion_sync\Controller;

use Drupal\cohesion_sync\PackagerManager;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Import report controller.
 *
 * @package Drupal\cohesion_sync\Controller
 */
class ImportReportController extends ControllerBase {

  /**
   * @var \Drupal\cohesion_sync\PackagerManager*/
  protected $packagerManager;

  /**
   * @var array*/
  protected $action_data;

  /**
   * OperationExportController constructor.
   *
   * @param \Drupal\cohesion_sync\PackagerManager $packagerManager
   */
  public function __construct(PackagerManager $packagerManager) {
    $this->packagerManager = $packagerManager;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new self($container->get('cohesion_sync.packager'));
  }

  /**
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse | mixed
   */
  public function index(Request $request) {
    $page = [];

    // Found session data, so build the report.
    if ($this->action_data = \Drupal::service('tempstore.private')->get('sync_report')->get('report')) {

      $page['ENTRY_NEW_IMPORTED'] = $this->buildTable($this->t('New entities imported'), ENTRY_NEW_IMPORTED, 'entry-new-imported');
      $page['ENTRY_EXISTING_OVERWRITTEN'] = $this->buildTable($this->t('Existing entities - Overwritten'), ENTRY_EXISTING_OVERWRITTEN, 'entry-existing-overwritten');
      $page['ENTRY_EXISTING_IGNORED'] = $this->buildTable($this->t('Existing entities - Keep existing'), ENTRY_EXISTING_IGNORED, 'entry-existing-ignored');
      $page['ENTRY_EXISTING_LOCKED'] = $this->buildTable($this->t('Existing entities - Locked and ignored'), ENTRY_EXISTING_LOCKED, 'entry-existing-locked');
      $page['ENTRY_EXISTING_NO_CHANGES'] = $this->buildTable($this->t('Existing entities - No changes detected'), ENTRY_EXISTING_NO_CHANGES, 'entry-existing-no-changes');

      return $page;
    }
    // No session data was set, so redirect back to the import page.
    else {
      return new RedirectResponse('/admin/cohesion/sync/import');
    }
  }

  /**
   * @param $title
   * @param $status
   * @param $import_name
   *   (This only exists so that Cypress can count the results easily.)
   *
   * @return array
   */
  private function buildTable($title, $status, $import_name) {
    $table = [
      '#type' => 'table',
      '#caption' => [
        '#markup' => '<h3>' . $title . '</h3>',
      ],
      '#attributes' => [
        'data-drupal-selector' => $import_name,
      ],
      '#header' => [
        $this->t('Entity'),
        $this->t('Type'),
      ],
    ];

    $count = 0;

    foreach ($this->action_data as $i => $entry) {
      if ($entry['entry_action_state'] === $status) {
        $table[$i]['title'] = [
          '#markup' => $entry['entity_label'],
          '#wrapper_attributes' => [
            'class' => [''],
          ],
        ];
        $table[$i]['type'] = [
          '#markup' => $entry['entity_type_label'],
          '#wrapper_attributes' => [
            'class' => ['system-status-report-counters__item--third-width'],
          ],
        ];

        $count += 1;
      }
    }

    // Only return the table if there were actually some results.
    return $count > 0 ? $table : [];
  }

}
