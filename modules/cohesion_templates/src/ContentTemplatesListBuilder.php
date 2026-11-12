<?php

namespace Drupal\cohesion_templates;

use Drupal\cohesion\CohesionListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Link;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ContentTemplatesListBuilder.
 *
 * Provides a listing of Site Studio content templates entities.
 *
 * @package Drupal\cohesion_templates
 */
class ContentTemplatesListBuilder extends CohesionListBuilder {

  /**
   * ContentTemplatesListBuilder constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage) {
    $this->entityTypeId = $entity_type->id();
    $this->storage = $storage;
    $this->entityType = $entity_type;

    $this->limit = FALSE;

    parent::__construct($entity_type, $storage);
  }

  /**
   * Load and sort the templates ready for rendering.
   *
   * Use this opportunity to sort the entities:
   * - Grouped by node_type: Global first, then alphanumeric.
   * - Sort by Full view mode first (built-in at the top), then alphanumeric.
   *
   * @return array
   *   Tree of content templates, organised by entity type.
   */
  public function load() {
    $entity_type = \Drupal::routeMatch()->getParameter('content_entity_type');
    $candidate_template_ids = \Drupal::service('entity_type.manager')->getStorage('cohesion_content_templates')->getQuery()
      ->accessCheck(TRUE)
      ->condition('entity_type', $entity_type)
      ->execute();

    if ($candidate_template_ids) {
      $candidate_templates = $this->storage->loadMultiple($candidate_template_ids);

      $groups = [];
      foreach ($candidate_templates as $entity) {
        $bundle = $entity->get('bundle');
        if (!isset($groups[$bundle])) {
          $groups[$bundle] = [];
        }

        // Order entities using weight, which is then used by uasort().
        $view_mode = $entity->get('view_mode');
        if ($view_mode == 'full') {
          $entity->weight = 1;
        }
        else {
          $entity->weight = 2;
        }
        $groups[$bundle][] = $entity;
      }

      // Sort the entities using the entity class's sort() method.
      // See \Drupal\Core\Config\Entity\ConfigEntityBase::sort().
      foreach ($groups as $bundle => $entity) {
        uasort($groups[$bundle], [$this->entityType->getClass(), 'sort']);
      }

      ksort($groups);
      return $groups;
    }
    else {
      throw new NotFoundHttpException();
    }
  }

  /**
   * Build separate lists for each type of content template.
   *
   * @inheritdoc
   */
  public function render() {
    $build = [];
    $entities = $this->load();
    $entity_type = \Drupal::routeMatch()->getParameter('content_entity_type');

    $bundle_info = \Drupal::service('entity_type.bundle.info');
    $bundles = $bundle_info->getBundleInfo($entity_type);

    foreach ($entities as $title => $template_group) {
      // Check to see if this bundle exists.
      if (isset($bundles[$title]['label'])) {
        $bundle_title = $bundles[$title]['label'];
        $valid_entity_bundle = TRUE;
      }
      else {
        if ($title == '__any__') {
          $bundle_title = $this->t('Global');
          $valid_entity_bundle = TRUE;
        }
        else {
          $bundle_title = '<span class="entity-meta__last-saved">Missing bundle (Machine name: <span class="text-lowercase">' . $title . '</span>)</span>';
          $valid_entity_bundle = FALSE;
        }
      }

      // Render the header.
      $table = [
        'accordion' => [
          '#type' => 'details',
          '#open' => FALSE,
          '#title' => [
            '#markup' => $bundle_title,
          ],
          '#attributes' => ['class' => [!$valid_entity_bundle ? 'color-error' : '']],
          'table' => $this->buildTable($title, $template_group),
        ],
      ];

      // Add button to add custom node full content templates.
      if ($title != '__any__') {
        foreach ($template_group as $entity) {
          // If it has a full view mode then add a link to add a tempalte.
          $add_link = Link::createFromRoute($this->t('+ Add full content template'), 'entity.cohesion_content_templates.add_form', [
            'content_entity_type' => $entity->get('entity_type'),
            'bundle' => $entity->get('bundle'),
          ])->toRenderable();
          $add_link['#attributes']['class'][] = 'button';
          $add_link['#attributes']['class'][] = 'button--primary';
          $table['accordion']['add_template'] = $add_link;
        }
      }

      $build[] = $table;
    }

    // Show something even if no templates have been imported yet.
    if (!$build) {
      $build[] = $table['table'] = [
        '#type' => 'table',
        '#header' => $this->buildHeader(),
        '#title' => $this->getTitle(),
        '#rows' => [],
        '#empty' => $this->t('There are no @label yet.', ['@label' => lcfirst($this->entityType->getLabel())]),
      ];
    }

    return $build;
  }

  /**
   * Build content templates list.
   *
   * @param string $title
   * @param array $template_group
   *
   * @return mixed
   */
  public function buildTable($title, $template_group) {
    $table['table'] = [
      '#type' => 'table',
      '#header' => $this->buildHeader(),
      '#title' => $title,
      '#rows' => [],
      '#empty' => $this->t('There are no @label yet.', [
        '@label' => strtolower($this->entityType->getLabel()),
      ]),
      '#cache' => [
        'contexts' => $this->entityType->getListCacheContexts(),
        'tags' => $this->entityType->getListCacheTags(),
      ],
    ];

    foreach ($template_group as $entity) {

      // Always show for full view modes, global templates and for existing
      // templates created.
      if ($entity->get('view_mode') !== 'full' && $entity->get('bundle') !== '__any__' && !$entity->isModified()) {

        // Get active view modes for bundles.
        $active_view_modes = \Drupal::service('entity_display.repository')->getViewModeOptionsByBundle($entity->get('entity_type'), $entity->get('bundle'));

        $view_modes = [];
        foreach ($active_view_modes as $key => $view_mode) {
          $view_modes[] = $key;
        }

        if (in_array($entity->get('view_mode'), $view_modes)) {
          if ($row = $this->buildRow($entity)) {
            $table['table']['#rows'][$entity->id()] = $row;
          }
        }
      }
      else {
        if ($row = $this->buildRow($entity)) {
          $table['table']['#rows'][$entity->id()] = $row;
        }
      }
    }

    // Only add the pager if a limit is specified.
    if ($this->limit) {
      $table['pager'] = [
        '#type' => 'pager',
      ];
    }

    return $table;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {

    $parent_header = parent::buildHeader();

    $header['label'] = [
      'data' => $this->t('Name'),
    ];
    $header['view_mode'] = [
      'data' => $this->t('View mode'),
      'class' => [RESPONSIVE_PRIORITY_LOW],
    ];
    $header['status'] = [
      'data' => $parent_header['status'],
      'class' => [RESPONSIVE_PRIORITY_MEDIUM],
    ];

    $header['selectable'] = $parent_header['selectable'];
    $header['in_use'] = $parent_header['in_use'];

    $header['operations'] = $parent_header['operations'];

    return $header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $parent_row = parent::buildRow($entity);

    $row['label'] = $parent_row['label'];
    $row['view_mode'] = [
      'data' => $entity->get('view_mode'),
      'class' => [RESPONSIVE_PRIORITY_LOW],
    ];

    $status = $parent_row['status'];
    if ($entity->get('view_mode') === 'full' && $entity->get('bundle') != '__any__' && $entity->get('default') === TRUE) {
      $status['data']['#markup'] .= ', ' . $this->t('default');
    }
    $row['status'] = $status;

    $row['selectable'] = $parent_row['selectable'];
    if ($entity->get('view_mode') !== 'full' || $entity->get('bundle') === '__any__') {
      $row['selectable'] = '-';
    }

    $row['in_use'] = $parent_row['in_use'];

    $row['operations'] = $parent_row['operations'];

    return $row;

  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);

    // Remove duplicate and delete actions on non full content template.
    if ($entity->get('view_mode') !== 'full') {
      $operations['delete']['title'] = t('Reset');
      unset($operations['duplicate']);
      unset($operations['set_default']);
      unset($operations['enable_selection']);
      unset($operations['disable_selection']);
    }

    if (!$entity->isModified()) {
      $operations['create'] = $operations['edit'];
      $operations['create']['title'] = t('Create');
      unset($operations['edit']);
      unset($operations['delete']);
      unset($operations['duplicate']);
      unset($operations['disable']);
      unset($operations['enable']);
      unset($operations['set_default']);
    }

    // If a "global" template hide the duplicate operation.
    if ($entity->get('bundle') === '__any__') {
      unset($operations['duplicate']);
    }

    return $operations;
  }

}
