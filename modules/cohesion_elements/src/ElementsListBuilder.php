<?php

namespace Drupal\cohesion_elements;

use Drupal\cohesion\CohesionListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ElementsListBuilder.
 *
 * Provides a listing of Site Studio components and helpers.
 *
 * @package Drupal\cohesion_elements
 */
class ElementsListBuilder extends CohesionListBuilder implements FormInterface {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * ElementsListBuilder constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, FormBuilderInterface $form_builder, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($entity_type, $storage);
    $this->formBuilder = $form_builder;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static($entity_type, $container->get('entity_type.manager')->getStorage($entity_type->id()), $container->get('form_builder'), $container->get('entity_type.manager'));
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dx8_' . $this->entityType->id() . '_list';
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = parent::buildHeader();

    // Override type title.
    $header['type']['data'] = $this->t('Machine Name (id)');

    $header['weight'] = [
      'weight' => t('Weight'),
    ];

    $header['selectable'] = [
      'data' => $this->t('Selection'),
      'class' => [RESPONSIVE_PRIORITY_MEDIUM],
    ];

    return $header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row = parent::buildRow($entity);

    $row['type'] = $entity->id();

    return $row;
  }

  /**
   * @return array|mixed
   */
  public function render() {
    return $this->formBuilder->getForm($this);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attributes']['class'][] = 'clearfix';
    $form['#attached']['library'][] = 'cohesion/cohesion-list-builder-sort';

    // Build the form tree.
    $form[$this->entityType->id()] = [
      '#type' => 'container',
    ];

    $reflector = new \ReflectionClass($this->entityType->getClass());
    $category_type_id = $reflector->getConstant('CATEGORY_ENTITY_TYPE_ID');

    $categories_query = $this->entityTypeManager->getStorage($category_type_id)->getQuery()
      ->accessCheck(TRUE)
      ->sort('weight', 'asc');

    if ($categories = $this->entityTypeManager->getStorage($category_type_id)->loadMultiple($categories_query->execute())) {
      foreach ($categories as $category) {

        $query = $this->entityTypeManager->getStorage($this->entityType->id())->getQuery()
          ->accessCheck(TRUE)
          ->condition('category', $category->id())
          ->sort('weight', 'asc');

        $entities = $this->entityTypeManager->getStorage($this->entityType->id())->loadMultiple($query->execute());

        // Build the accordions.
        $form[$this->entityType->id()][$category->id()]['accordion'] = [
          '#type' => 'details',
          '#open' => FALSE,
          '#title' => $category->label() . ' (' . $query->count()->execute() . ')',
        ];

        // Build the accordion group tables.
        $this->buildTable($form[$this->entityType->id()][$category->id()]['accordion'], $category, $entities);
      }
    }

    $form['actions'] = [
      '#tree' => FALSE,
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save changes'),
      '#button_type' => 'primary',
    ];

    // Include the Angular css (which controls the cohesion_accordion and other
    // form styling).
    $form['#attached']['library'][] = 'cohesion/cohesion-admin-styles';

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function buildTable(&$form_data, $category, $entities = []) {
    $form_data['table'] = [
      '#type' => 'table',
      '#header' => ($entities) ? $this->buildHeader() : [],
      '#title' => $category->label(),
      '#rows' => [],
      '#empty' => $this->t('There are no @label yet.', ['@label' => mb_strtolower($this->entityType->getLabel() ?? '')]),
      '#cache' => [
        'contexts' => $this->entityType->getListCacheContexts(),
        'tags' => $this->entityType->getListCacheTags(),
      ],
      '#attributes' => [
        'class' => ['coh-style-draggable'],
      ],
    ];

    $form_data['table']['#tabledrag'] = [
      [
        'action' => 'match',
        'relationship' => 'parent',
        'group' => 'table-sort-weight-' . $category->id(),
        'hidden' => TRUE,
        'limit' => 1,
      ],
      [
        'action' => 'order',
        'relationship' => 'sibling',
        'group' => 'table-sort-weight-' . $category->id(),
      ],
    ];

    // Build rows.
    foreach ($entities as $entity) {
      $common_row = $this->buildRow($entity);

      $id = $entity->id();

      $form_data['table'][$id]['label'] = [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#value' => $common_row['label'],
      ];
      $form_data['table'][$id]['#attributes']['class'][] = 'coh-tabledrag-parent';
      $form_data['table'][$id]['#attributes']['class'][] = 'tabledrag-leaf';

      $form_data['table'][$id]['#attributes']['class'][] = 'draggable';

      $form_data['table'][$id]['type'] = [
        '#type' => 'markup',
        '#markup' => $common_row['type'],
      ];

      $form_data['table'][$id]['selectable'] = $common_row['selectable'];

      if (isset($common_row['in_use'])) {
        $form_data['table'][$id]['in_use'] = $common_row['in_use'];
      }

      $form_data['table'][$id]['locked'] = $common_row['locked'];

      $form_data['table'][$id]['operations'] = $common_row['operations'];

      $form_data['table'][$id]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for @title', ['@title' => $entity->label()]),
        '#title_display' => 'invisible',
        '#default_value' => $entity->getWeight(),
        '#attributes' => [
          'class' => [
            'table-sort-weight-' . $category->id(),
          ],
        ],
        '#delta' => 2048,
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $sort_data = $form_state->getValue('table');
    $sort_data = is_array($sort_data) ? $sort_data : (array) $sort_data;
    try {
      $entities = $this->entityTypeManager->getStorage($this->entityType->id())->loadMultiple(array_keys($sort_data));
    }
    catch (\Exception $ex) {
      $entities = [];
    }

    // Update custom style config entity with current order.
    $weight = 0;
    if ($entities) {
      foreach ($entities as $id => $entity) {
        try {
          $config = \Drupal::configFactory()->getEditable($entity->getConfigDependencyName());
          $config->set('weight', $weight);
          $config->save(TRUE);
        }
        catch (\Exception $ex) {
        }

        $weight++;
      }
    }
  }

}
