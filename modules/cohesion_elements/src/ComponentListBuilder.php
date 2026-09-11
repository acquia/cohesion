<?php

namespace Drupal\cohesion_elements;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\cohesion_elements\Entity\ComponentCategory;
use Drupal\cohesion_elements\Form\ComponentListBuilderFilterForm;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Components list builder.
 */
class ComponentListBuilder extends ElementsListBuilder {

  /**
   * The query.
   *
   * @var \Drupal\Core\Config\Entity\Query\Query
   */
  protected $query;

  public function __construct(
    EntityTypeInterface $entity_type,
    EntityStorageInterface $storage,
    FormBuilderInterface $form_builder,
    EntityTypeManagerInterface $entity_type_manager,
    protected CustomComponentsService $customComponentsService,
    protected Request $currentRequest,
    protected LoggerChannelFactoryInterface $loggerFactory,
    protected ConfigFactoryInterface $configFactory,
    protected MessengerInterface $messengerService,
  ) {
    parent::__construct($entity_type, $storage, $form_builder, $entity_type_manager);
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
      $container->get('custom.components'),
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('logger.factory'),
      $container->get('config.factory'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $keys = $this->currentRequest->query->all('tags');
    $build['components_admin_filter_form'] = $this->formBuilder->getForm(ComponentListBuilderFilterForm::class, $keys);
    // Keep the draggable list form intact by placing it under a distinct key
    // instead of array-merging, which can scatter children and actions.
    $build['components_list'] = parent::render();

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    $entity_ids = $this->getEntityIds();
    return $this->storage->loadMultipleOverrideFree($entity_ids);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityIds() {
    $tags = $this->currentRequest->query->all('tags');

    if ($tags) {
      $this->query->condition('tag.*', $tags, 'IN');
    }

    return $this->query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = parent::buildHeader();
    unset($header['type']);

    return $header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row = parent::buildRow($entity);

    $label = (string) $entity->label();
    $id = (string) $entity->id();

    $row['label'] = "$label<br><small class=\"component-machine-name\">$id</small>";

    // Fetch tag details from the entity.
    $tags = $entity->getTagDetailsByIDs();
    $row['tag'] = '';

    // Check if $tags is empty.
    if (empty($tags)) {
      // Set a default class if there are no tags.
      $row['tag'] = '<div class="coh-tag-color-item none"></div>';
    } else {
      $row['tag'] .= '<div class="coh-tag-color-items">';
      // Loop through the tags array and build the HTML string.
      foreach ($tags as $tag) {
        // Get the first letter of the label and capitalize it.
        $firstLetter = strtoupper($tag['label'][0]);

        // Concatenate the HTML structure for each tag into $row['type'].
        $row['tag'] .= '<div class="coh-tag-color-item ' . $tag['class'] . '">';
        $row['tag'] .= "<div class='coh-tag-label' data-title='$tag[label]'>" . $firstLetter . '</div>';
        $row['tag'] .= '</div>';
      }
      $row['tag'] .= '</div>';
    }
    unset($row['type']);

    return $row;
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Let the parent build the full draggable list builder form, including
    // the actions/submit area so the submit button is rendered inside the form.
    $form = parent::buildForm($form, $form_state);

    // Attach any additional libraries specific to components.
    $form['#attached']['library'][] = 'cohesion_elements/component-list-tags';

    $reflector = new \ReflectionClass($this->entityType->getClass());
    $category_type_id = $reflector->getConstant('CATEGORY_ENTITY_TYPE_ID');

    $categories_query = $this->entityTypeManager->getStorage($category_type_id)->getQuery()
      ->accessCheck(TRUE)
      ->sort('weight', 'asc');

    if ($categories = $this->entityTypeManager->getStorage($category_type_id)->loadMultiple($categories_query->execute())) {
      foreach ($categories as $category) {

        $selected_tags = $this->currentRequest->query->all('tags');
        $dropzone_type = $this->currentRequest->query->get('dropzone_type', 'all');
        $entities = [];
        $component_count = 0;
        $base_query = $this->entityTypeManager->getStorage($this->entityType->id())->getQuery()
          ->accessCheck(TRUE)
          ->condition('category', $category->id())
          ->sort('weight', 'asc');

        if ($dropzone_type === 'all') {
          if ($selected_tags) {
            $base_query->condition('tag.*', $selected_tags, 'IN');
          }
          $this->query = $base_query;
          $entities = $this->load();
        }
        else {
          $this->query = $base_query;
          $entities = $this->load();

          $filtered_entities = [];
          foreach ($entities as $entity) {
            // Tag filtering
            $tag_ids = [];
            $tag_field = $entity->get('tag');

            if (is_object($tag_field) && method_exists($tag_field, 'getValue')) {
              foreach ($tag_field->getValue() as $tag_ref) {
                if (isset($tag_ref['target_id'])) {
                  $tag_ids[] = $tag_ref['target_id'];
                }
              }
            } elseif (is_array($tag_field)) {
              $tag_ids = $tag_field;
            }

            $tags_match = empty($selected_tags) || !empty(array_intersect($tag_ids, $selected_tags));

            // Dropzone filtering
            $is_dropzone = $this->customComponentsService->isDropzoneComponent($entity);

            $dz_match = ($dropzone_type === 'all')
              || ($dropzone_type === 'dropzone' && $is_dropzone)
              || ($dropzone_type === 'non-dropzone' && !$is_dropzone);

            if ($tags_match && $dz_match) {
              $filtered_entities[] = $entity;
            }
          }
          $entities = $filtered_entities;
        }

        $custom_components = [];
        if ($source_components = $this->customComponentsService->getComponentsInCategory(ComponentCategory::load($category->id()))) {
          $filteredCustomComponents = [];
          foreach ($source_components as $custom_component) {
            $customComponentTagIds = $this->customComponentsService->getTagIds($custom_component);
            $tags_match = empty($selected_tags) || !empty(array_intersect($customComponentTagIds, $selected_tags));

            // Determine if the component is a dropzone.
            $is_dropzone = $this->customComponentsService->isDropzoneComponent($custom_component);
            $dz_match =
              ($dropzone_type === 'all') ||
              ($dropzone_type === 'dropzone' && $is_dropzone) ||
              ($dropzone_type === 'non-dropzone' && !$is_dropzone);

            if ($tags_match && $dz_match) {
              $filteredCustomComponents[] = $custom_component;
            }
          }
          $custom_components = $this->customComponentsService->formatListAsComponents($filteredCustomComponents);
        }

        sort($custom_components);
        $count = count($entities) + count($custom_components);

        if ($count !== 0) {
          // Build the accordions for this category.
          $form[$this->entityType->id()][$category->id()]['accordion'] = [
            '#type' => 'details',
            '#open' => FALSE,
            '#title' => $category->label() . ' (' . $count . ')',
          ];

          // Build the accordion group tables.
          $this->buildTable(
            $form[$this->entityType->id()][$category->id()]['accordion'],
            $category,
            $entities,
            $custom_components
          );
        }
      }
    }

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

        if (isset($common_row['tag'])) {
          $form_data['custom_components']['table'][$id]['tag'] = [
            '#type' => 'markup',
            '#markup' => $common_row['tag'],
          ];
        }

        $form_data['custom_components']['table'][$id]['selectable']['data']['#markup'] = '-';

        if (isset($common_row['in_use'])) {
          $form_data['custom_components']['table'][$id]['in_use'] = $this->customComponentsService->getInUseMarkup($customComponent);
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
                'machine_name' => $id,
              ]),
            ],
          ],
        ];
      }
    }

    return $form_data;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $changes_made = FALSE;

    // Process top-level table weights.
    if ($topLevel = $form_state->getValue('table')) {
      $changes_made = $this->processTableData($topLevel) || $changes_made;
    }

    // Process nested category tables.
    $values = $form_state->getValues();
    if ($category_tables = $values[$this->entityType->id()] ?? []) {
      foreach ($category_tables as $category) {
        if ($table = $category['accordion']['table'] ?? []) {
          $changes_made = $this->processTableData($table) || $changes_made;
        }
      }
    }

    if ($changes_made) {
      $this->messengerService->addStatus($this->t('Component order has been updated.'));
    }
  }

  /**
   * Process table data for regular components.
   *
   * @param array $sort_data
   *   The sort data from form submission.
   *
   * @return bool
   *   TRUE if changes were made, FALSE otherwise.
   */
  protected function processTableData($sort_data): bool {
    if (empty($sort_data)) {
      return FALSE;
    }

    // Sort IDs by weight and load entities.
    $ids = array_keys($sort_data);
    usort($ids, fn($a, $b) => ($sort_data[$a]['weight'] ?? 0) <=> ($sort_data[$b]['weight'] ?? 0));

    try {
      $entities = $this->entityTypeManager->getStorage($this->entityType->id())->loadMultiple($ids);
    }
    catch (\Exception $ex) {
      $this->loggerFactory->get('cohesion_elements')->error('Failed to load components: @message', ['@message' => $ex->getMessage()]);
      $this->messengerService->addError($this->t('Failed to load components for reordering. Please try again or contact an administrator.'));
      return FALSE;
    }

    // Find entities that need weight updates.
    $entities_to_save = [];
    foreach ($ids as $weight => $id) {
      if (!isset($entities[$id])) {continue;
      }

      $entity = $entities[$id];
      $current_weight = $this->getEntityWeight($entity);

      if ($current_weight !== $weight) {
        $entity->set('weight', $weight);
        $entities_to_save[] = $entity;
      }
    }

    // Save changes using config API for performance.
    if ($entities_to_save) {
      try {
        foreach ($entities_to_save as $entity) {
          $config = $this->configFactory->getEditable($entity->getConfigDependencyName());
          $config->set('weight', $this->getEntityWeight($entity));
          $config->save(TRUE);
        }
      }
      catch (\Exception $ex) {
        $this->loggerFactory->get('cohesion_elements')->error('Failed to save component weights: @message', ['@message' => $ex->getMessage()]);
        $this->messengerService->addError($this->t('Failed to save component order. Please try again.'));
        throw $ex;
      }
    }

    return !empty($entities_to_save);
  }

  /**
   * Get entity weight value safely.
   *
   * @param object $entity
   *   The entity.
   *
   * @return int
   *   The weight value.
   */
  private function getEntityWeight($entity): int {
    $weight_field = $entity->get('weight');
    return is_object($weight_field) ? ($weight_field->value ?? 0) : ($weight_field ?? 0);
  }

}
