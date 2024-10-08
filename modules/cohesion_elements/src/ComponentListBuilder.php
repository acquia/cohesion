<?php

namespace Drupal\cohesion_elements;

use Drupal\cohesion_elements\Entity\ComponentCategory;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Components list builder.
 */
class ComponentListBuilder extends ElementsListBuilder {

  /**
   * Custom Components service.
   *
   * @var \Drupal\cohesion_elements\CustomComponentsService
   */
  protected $customComponentsService;

  /**
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\cohesion_elements\CustomComponentsService $customComponentsService
   */
  public function __construct(
    EntityTypeInterface $entity_type,
    EntityStorageInterface $storage,
    FormBuilderInterface $form_builder,
    EntityTypeManagerInterface $entity_type_manager,
    CustomComponentsService $customComponentsService,
  ) {
    parent::__construct($entity_type, $storage, $form_builder, $entity_type_manager);
    $this->customComponentsService = $customComponentsService;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new self(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('form_builder'),
      $container->get('entity_type.manager'),
      $container->get('custom.components')
    );
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    parent::buildForm($form, $form_state);

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

        // Add custom components.
        if ($custom_components = $this->customComponentsService->getComponentsInCategory(ComponentCategory::load($category->id()))) {
          $custom_components = $this->customComponentsService->formatListAsComponents($custom_components);
        }

        sort($custom_components);

        // Count UI & Custom components.
        $count = $query->count()->execute() + count($custom_components);

        // Build the accordions.
        $form[$this->entityType->id()][$category->id()]['accordion'] = [
          '#type' => 'details',
          '#open' => FALSE,
          '#title' => $category->label() . ' (' . $count . ')',
        ];

        // Build the accordion group tables.
        $this->buildTable($form[$this->entityType->id()][$category->id()]['accordion'], $category, $entities, $custom_components);
      }
    }

    $form['actions'] = [
      '#tree' => FALSE,
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Save changes'),
        '#button_type' => 'primary',
      ],
    ];

    // Include the css (which controls the cohesion_accordion
    // and other form styling).
    $form['#attached']['library'][] = 'cohesion/cohesion-admin-styles';

    return $form;
  }

  public function buildTable(&$form_data, $category, $entities = [], $customComponentEntities = []) {
    parent::buildTable($form_data, $category, $entities);

    // If we have any custom components then add another table
    // below the UI components.
    if ($customComponentEntities) {
      $form_data['custom_components']['header'] = [
        '#type' => 'markup',
        '#markup' => '<h6>Custom Components</h6>',
      ];

      $form_data['custom_components']['table'] = [
        '#type' => 'table',
        '#header' => $this->buildHeader() ?? [],
        '#title' => $category->label(),
        '#rows' => [],
        '#empty' => $this->t('There are no custom components yet.'),
        '#cache' => [
          'contexts' => $this->entityType->getListCacheContexts(),
          'tags' => $this->entityType->getListCacheTags(),
        ],
      ];

      // Build rows.
      foreach ($customComponentEntities as $customComponent) {
        $common_row = $this->buildRow($customComponent);

        $id = $customComponent->id();

        $form_data['custom_components']['table'][$id]['label'] = [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#value' => $common_row['label'],
        ];

        $form_data['custom_components']['table'][$id]['type'] = [
          '#type' => 'markup',
          '#markup' => $common_row['type'],
        ];

        $form_data['custom_components']['table'][$id]['selectable']['data']['#markup'] = '-';

        if (isset($common_row['in_use'])) {
          $form_data['custom_components']['table'][$id]['in_use'] = [
            '#markup' => $this->customComponentsService->getInUseMarkup($customComponent),
          ];
        }

        $form_data['custom_components']['table'][$id]['locked']['data']['#markup'] = '-';

        // Override the operations to only show a customised 'edit' link.
        $form_data['custom_components']['table'][$id]['operations']['data'] = [
          '#type' => 'operations',
          '#links' => [
            'edit' => [
              'title' => $this->t('Edit'),
              'weight' => 10,
              'url' => Url::fromRoute('cohesion_elements.custom_component.builder', [
                'machine_name' => $customComponent->id(),
              ]),
            ],
          ],
        ];
      }
    }

    return $form_data;
  }

}
