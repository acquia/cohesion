<?php

namespace Drupal\cohesion_custom_styles;

use Drupal\cohesion\CohesionListBuilder;
use Drupal\cohesion_custom_styles\Entity\CustomStyle;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Utility\Error;

/**
 * Custom styles list builder.
 *
 * @package Drupal\cohesion_custom_styles
 */
class CustomStylesListBuilder extends CohesionListBuilder implements FormInterface {

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
   * CustomStylesListBuilder constructor.
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
    return new self(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('form_builder'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dx8_custom_styles_list';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attributes']['class'][] = 'clearfix';
    $form['#attached']['library'][] = 'cohesion/cohesion-list-builder-sort';

    // Build the form tree.
    $form['styles'] = [
      '#type' => 'container',
    ];

    // Group by custom style types.
    if ($custom_style_types = $this->entityTypeManager->getStorage('custom_style_type')
      ->loadMultiple()) {
      // Make sure the custom style types are in alphabetical order.
      ksort($custom_style_types);
      $custom_styles = $this->load();

      foreach ($custom_style_types as $custom_style_type) {
        $custom_style_type_id = $custom_style_type->id();
        // Filter entities by custom style group ID.
        $grouped_entities = array_filter($custom_styles, function ($value) use ($custom_style_type_id) {
          return ($custom_style_type_id === $value->get('custom_style_type')) ? TRUE : FALSE;
        });

        // Build the accordions.
        $form['styles'][$custom_style_type_id]['accordion'] = [
          '#type' => 'details',
          '#open' => FALSE,
          '#title' => $custom_style_type->label() . ' (' . count($grouped_entities) . ')',
        ];

        $title = $custom_style_type->label();

        // Build the accordion group tables.
        $this->buildTable($form['styles'][$custom_style_type_id]['accordion'], $title, $grouped_entities, $custom_style_type_id);
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

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = parent::buildHeader();

    $header['weight'] = [
      'weight' => t('Weight'),
    ];

    $header['type'] = [
      'data' => t('Type'),
      'class' => [RESPONSIVE_PRIORITY_LOW],
    ];

    $header['status'] = [
      'data' => t('Status'),
      'class' => [RESPONSIVE_PRIORITY_MEDIUM],
    ];

    return $header;
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
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);

    if (!$entity->getParentId()) {
      $operations['extend'] = [
        'title' => 'Extend style',
        'url' => $entity->toUrl('extend-form'),
        'weight' => 45,
      ];
    }

    if ($entity->getParentId()) {
      try {
        $parent_entity = $this->entityTypeManager->getStorage('cohesion_custom_style')
          ->load($entity->getParentId());
        if ($parent_entity && !$parent_entity->status()) {
          unset($operations['enable']);
        }
      }
      catch (\Exception $e) {

      }
    }

    return $operations;
  }

  /**
   * @return \Drupal\Core\Entity\EntityInterface[]
   */
  public function load() {
    return CustomStyle::loadParentChildrenOrdered();
  }

  /**
   * {@inheritdoc}
   */
  public function buildTable(&$form_data, $group_title, $style_entities = [], $type_id = NULL) {
    $form_data['table'] = [
      '#type' => 'table',
      '#header' => ($style_entities) ? $this->buildHeader() : [],
      '#title' => $group_title,
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
        'group' => 'table-sort-weight-' . $type_id,
        'hidden' => TRUE,
        'limit' => 1,
      ],
      [
        'action' => 'order',
        'relationship' => 'sibling',
        'group' => 'table-sort-weight-' . $type_id,
      ],
    ];

    // Build rows.
    foreach ($style_entities as $entity) {
      $common_row = parent::buildRow($entity);
      $label = [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#value' => $common_row['label'],
      ];

      try {
        $type_entity = $this->entityTypeManager->getStorage('custom_style_type')
          ->load($entity->getCustomStyleType());
      }
      catch (InvalidPluginDefinitionException $ex) {
        Error::logException('cohesion', $ex);
        $type_entity = NULL;
      }

      $id = $entity->id();

      // Extended style.
      if ($entity->getParentId()) {
        // Indent children.
        $form_data['table'][$id]['label'] = [
          [
            '#theme' => 'indentation',
            '#size' => 1,
          ],
          $label,
        ];
        // Lock parent so no child can be dragged into it.
        $form_data['table'][$id]['#attributes']['class'][] = 'coh-tabledrag-parent-locked';
        $form_data['table'][$id]['#attributes']['class'][] = 'tabledrag-root';
        $form_data['table'][$id]['#attributes']['class'][] = 'coh-extended-style';
        $form_data['table'][$id]['label']['#attributes']['class'][] = 'cohesion-next-level-icon';
      }
      // Parent style.
      else {
        $form_data['table'][$id]['label'] = $label;
        $form_data['table'][$id]['#attributes']['class'][] = 'coh-tabledrag-parent';
        $form_data['table'][$id]['#attributes']['class'][] = 'tabledrag-leaf';
      }

      $form_data['table'][$id]['#attributes']['class'][] = 'draggable';
      $form_data['table'][$id]['class_name'] = [
        '#type' => 'markup',
        '#markup' => $entity->getClass(),
      ];

      $form_data['table'][$id]['type'] = [
        '#type' => 'markup',
        '#markup' => $type_entity ? $type_entity->label() : NULL,
      ];

      $form_data['table'][$id]['status'] = $common_row['status'];

      $form_data['table'][$id]['selectable'] = $common_row['selectable'];

      $form_data['table'][$id]['in_use'] = $common_row['in_use'];

      $form_data['table'][$id]['locked'] = $common_row['locked'];

      $form_data['table'][$id]['operations'] = $common_row['operations'];

      $form_data['table'][$id]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for @title', ['@title' => $entity->label()]),
        '#title_display' => 'invisible',
        '#default_value' => $entity->getWeight(),
        '#attributes' => [
          'class' => [
            'table-sort-weight-' . $type_id,
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
    $table = $form_state->getValue('table');

    $temp_sort_data = [];
    $i = 0;
    foreach ($table as $entity_id => $value) {
      $temp_sort_data[] = [
        'index' => $i,
        'entity_id' => $entity_id,
        'value' => $value,
      ];
      $i++;
    }
    uasort($temp_sort_data, function ($a, $b) {
      if ($a['value']['weight'] == $b['value']['weight']) {
        return $a['index'] - $b['index'];
      }
      return ($a['value']['weight'] < $b['value']['weight']) ? -1 : 1;
    });

    $sort_data = [];
    foreach ($temp_sort_data as $temp_sort_datum) {
      $sort_data[] = $temp_sort_datum['entity_id'];
    }

    try {
      $entities = $this->entityTypeManager->getStorage('cohesion_custom_style')->loadMultiple($sort_data);
    }
    catch (\Exception $ex) {
      $entities = [];
    }

    // Update custom style config entity with current order.
    if ($entities) {
      $custom_style_type = NULL;
      $weight = 0;
      foreach ($entities as $id => $entity) {

        // Store the current order so we can use it to sort custom styles in
        // stylesheet.json.
        $config_name = $entity->getConfigDependencyName();
        try {
          $config = \Drupal::configFactory()->getEditable($config_name);
          $config->set('weight', $weight);
          $config->save(TRUE);
        }
        catch (\Exception $ex) {

        }

        $weight++;
      }

      // Re-save all the custom styles via a batch process to ensure they are
      // in the correct order in the output .css.
      if (!empty($entities)) {
        $form_state->setRedirect('cohesion_custom_style.batch_resave');
      }
    }
  }

}
