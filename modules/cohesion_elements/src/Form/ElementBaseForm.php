<?php

namespace Drupal\cohesion_elements\Form;

use Drupal\cohesion\Form\CohesionBaseForm;
use Drupal\cohesion_elements\Controller\ElementsController;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;

/**
 * Element base form.
 *
 * @package Drupal\cohesion_elements\Form
 */
abstract class ElementBaseForm extends CohesionBaseForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    // Prepare the upload directory.
    $preview_upload_location = 'public://element-preview-images';
    if (!file_exists($preview_upload_location)) {
      @ \Drupal::service('file_system')->mkdir($preview_upload_location, 0777, TRUE);
    }
    @ \Drupal::service('file_system')->chmod($preview_upload_location, 0777);

    // Now handle the form.
    $form = parent::form($form, $form_state);
    $form['cohesion']['#token_browser'] = 'all';
    $form['cohesion']['#cohFormId'] = $this->entity->getAssetName();
    unset($form['cohesion']['#json_mapper']);

    $form_class = str_replace('_', '-', $this->entity->getEntityTypeId()) . '-' . str_replace('_', '-', $this->entity->id() ?? '') . '-form';
    $form['#attributes']['class'][] = $form_class;

    // Set Drupal field endpoint.
    $language_none = \Drupal::languageManager()->getLanguage(LanguageInterface::LANGCODE_NOT_APPLICABLE);

    $form['#attached']['drupalSettings']['cohesion']['contextualKey'] = Url::fromRoute('cohesion.entity_fields', [
      'entity_type' => '__any__',
      'entity_bundle' => '__any__',
    ], ['language', $language_none])->toString();

    // Show categories.
    $form['details']['category'] = [
      '#type' => 'select',
      '#title' => $this->t('Category'),
      '#options' => [],
      '#required' => TRUE,
      '#access' => TRUE,
      '#weight' => 2,
    ];

    // Add new category link.
    $add_category_url = Url::fromRoute($this->entity->getEntityTypeId() == 'cohesion_component' ? 'entity.cohesion_component_category.add_form' : 'entity.cohesion_helper_category.add_form');

    // Only show if user has access to this route.
    if ($add_category_url->access()) {
      $form['details']['add_category'] = [
        '#prefix' => '<p>',
        '#suffix' => '</p>',
        '#title' => $this->t('Add new category.'),
        '#type' => 'link',
        '#url' => $add_category_url,
        '#weight' => 3,
      ];
    }

    $categories = ElementsController::getElementCategories($this->entity->getCategoryEntityTypeId(), TRUE);
    foreach ($categories as $key => $value) {
      $form['details']['category']['#options'][$key] = $value['label'];
    }
    $form['details']['category']['#default_value'] = $this->entity->get('category') != '' ? $this->entity->get('category') : 'general';

    // Preview image upload.
    $form['details']['preview_image'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Preview image'),
      '#upload_validators' => [
        'file_validate_is_image' => [],
        'file_validate_extensions' => ['gif png jpg jpeg'],
        'file_validate_size' => [25600000],
      ],
      '#theme' => 'image_widget',
      '#preview_image_style' => 'dx8_component_preview',
      '#upload_location' => $preview_upload_location,
      '#required' => FALSE,
      '#weight' => 4,
    ];

    if ($preview_image_entity = $this->entity->getPreviewImage()) {
      $form['details']['preview_image']['#default_value'][] = $preview_image_entity;
    }

    $form['details']['machine_name'] = [
      '#title' => $this->t('Machine name'),
      '#maxlength' => 32 - strlen($this->entity->getEntityMachineNamePrefix()),
      '#access' => TRUE,
      '#weight' => 1,
      '#description' => $this->entity->getEntityMachineNamePrefix(),
      '#description_display' => 'before',
      '#default_value' => str_replace($this->entity->getEntityMachineNamePrefix(), '', $this->entity->id() ?? ''),
      '#type' => 'ajax_machine_name',
      '#required' => FALSE,
      '#machine_name' => [
        'source' => ['details', 'label'],
        'label' => $this->t('Machine name'),
        'replace_pattern' => '[^a-z0-9\_]+',
        'replace' => '_',
        'field_prefix' => $this->entity->getEntityMachineNamePrefix(),
        'exists' => [$this, 'checkUniqueMachineName'],
        'entity_type_id' => $this->entity->getEntityTypeId(),
        'entity_id' => $this->entity->id(),
      ],
      '#disabled' => !$this->entity->canEditMachineName(),
    ];

    $storage = $this->entityTypeManager->getStorage('entity_view_mode');
    $entity_ids = $storage->getQuery()->accessCheck(TRUE)->execute();
    $entities = $storage->loadMultiple($entity_ids);
    $entity_types = $this->entityTypeManager->getDefinitions();

    $entity_type_options = [];
    foreach ($entities as $entity) {
      if (!isset($entity_type_options[$entity->getTargetType()])) {
        $entity_type = $entity_types[$entity->getTargetType()];
        $field_route = $entity_type->get('field_ui_base_route');
        $view_builder = $entity_type->hasHandlerClass('view_builder');
        if ($field_route && $view_builder) {
          $entity_type_options[$entity_type->id()] = ($entity_type->get('bundle_label')) ? $entity_type->get('bundle_label') : $entity_type->get('label');
        }
      }
    }
    $entity_type_options['dx8_templates'] = $this->t('Site Studio templates');

    $active_index = [];
    $entity = $this->entity;

    $bundle_access_data = $entity->get('bundle_access');
    $entity_type_access_data = $entity->get('entity_type_access');

    if (empty($form_state->get('entity_types_count'))) {
      $count = count($bundle_access_data) ?: 1;
      $form_state->set('entity_types_count', $count);
    }
    // Set default removed index.
    if (empty($form_state->get('removed_index'))) {
      $form_state->set('removed_index', []);
    }

    $form['availability'] = [
      '#type' => 'details',
      '#title' => $this->t('Availability'),
      '#weight' => -99,
      '#summary_attributes' => ['data-ssa-help-link' => \Drupal::service('cohesion.support_url')->getSupportUrlPrefix() . 'component-form-builder-component-availability'],
    ];
    $form['availability']['#attached']['library'][] = 'cohesion/cohesion-accordion-element';

    $form['availability']['add'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'entity-type-wrapper',
      ],
      '#weight' => 5,
    ];

    $form['availability']['add']['types_container'] = [
      '#type' => 'html_tag',
      '#tag' => 'ol',
      '#attributes' => [
        'class' => ['ssa-list-group'],
      ],
    ];

    for ($i = 0; $i < $form_state->get('entity_types_count'); $i++) {

      // Skip removed row.
      if (in_array($i, $form_state->get('removed_index'))) {
        continue;
      }
      // We need store the index of all entity types so we can
      // Availability.
      $active_index[] = $i;

      if (isset($entity_type_access_data[$i]) && !$form_state->getTriggeringElement()) {
        $entity_type_access = $entity_type_access_data[$i];
      }
      elseif ($form_state->hasValue('entity_type_access_' . $i) && $form_state->getTriggeringElement()) {
        $entity_type_access = $form_state->getValue('entity_type_access_' . $i, $this->entity->getEntityTypeAccess());
      }
      else {
        $entity_type_access = [];
      }

      $form['availability']['add']['types_container'][$i]['item'] = [
        '#type' => 'html_tag',
        '#tag' => 'li',
        '#attributes' => [
          'class' => ['ssa-list-group-item'],
        ],
      ];

      $form['availability']['add']['types_container'][$i]['item']['row']['entity_type_access_' . $i] = [
        '#type' => 'select',
        '#title' => $this->t('Available on entity type:'),
        '#empty_option' => 'All',
        '#options' => $entity_type_options,
        '#access' => TRUE,
        '#weight' => 3,
        '#default_value' => $entity_type_access,
        '#ajax' => [
          'callback' => '::updateBundle',
          'wrapper' => 'bundle-wrapper-' . $i,
        ],
        '#prefix' => '<div class="coh-entity-container">',
        '#attributes' => [
          'entity-type-select-index' => $i,
        ],
      ];

      $form['availability']['add']['types_container'][$i]['item']['row']['bundle_wrapper'] = [
        '#type' => 'container',
        '#attributes' => [
          'id' => 'bundle-wrapper-' . $i,
        ],
        '#weight' => 4,
        '#suffix' => '</div>',
      ];

      if (isset($bundle_access_data[$i]) && !$form_state->getTriggeringElement()) {
        $bundle_access_values = $bundle_access_data[$i];
      }
      elseif ($form_state->hasValue('bundle_access_' . $i) && $form_state->getTriggeringElement()) {
        $bundle_access_values = $form_state->getValue('bundle_access_' . $i, $this->entity->getEntityTypeAccess());
      }
      else {
        $bundle_access_values = [];
      }

      if (!empty($entity_type_access)) {
        $form['availability']['add']['types_container'][$i]['item']['row']['bundle_wrapper']['bundle_access_' . $i] = [
          '#type' => 'checkboxes',
          '#options' => $this->getBundleByEntityType($entity_type_access),
          '#title' => $this->t('Bundles'),
          '#default_value' => $bundle_access_values,
          '#weight' => 5,
        ];
      }

      $form['availability']['add']['types_container'][$i]['item']['row']['remove'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remove'),
        '#submit' => ['::removeOneCallback'],
        '#name' => 'remove_button_name_' . $i,
        '#ajax' => [
          'callback' => '::ajaxCallback',
          'wrapper' => 'entity-type-wrapper',
        ],
        '#attributes' => [
          'class' => [],
          'data-remove-index' => $i,
        ],
        '#prefix' => '<div class="coh-btn-container">',
        '#suffix' => '</div>',
        '#weight' => 6,
        // Hide 'remove' button from first type option.
        '#access' => $i === 0 ? FALSE : TRUE,
      ];
    }

    $form_state->set('active_index', $active_index);

    $form['availability']['add']['add_button_container'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['coh-btn-container'],
      ],
    ];

    $form['availability']['add']['add_button_container']['add_type'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add another type'),
      '#button_type' => 'primary',
      '#submit' => ['::addOne'],
      '#name' => 'add_more_button',
      '#ajax' => [
        'callback' => '::ajaxCallback',
        'wrapper' => 'entity-type-wrapper',
      ],
    ];

    // Weight.
    $form['weight'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Weight'),
      '#maxlength' => 3,
      '#default_value' => $this->entity->getWeight(),
      '#weight' => 10,
      '#access' => FALSE,
    ];

    return $form;
  }

  /**
   * Ajax callback for the bundle dropdown.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  public function updateBundle(array $form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $i = (isset($triggering_element['#attributes']['entity-type-select-index']) && $triggering_element['#attributes']['entity-type-select-index'] !== NULL) ? $triggering_element['#attributes']['entity-type-select-index'] : NULL;

    return $form['availability']['add']['types_container'][$i]['item']['row']['bundle_wrapper'] ?: [];
  }

  /**
   * Submit handler for the "add-one-more" button.
   *
   * Increments the max counter and causes a rebuild.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function addOne(array &$form, FormStateInterface $form_state) {
    $count = $form_state->get('entity_types_count');
    $count++;
    $form_state->set('entity_types_count', $count);
    $form_state->setRebuild();
  }

  /**
   * Callback for both ajax add/remove buttons.
   *
   * Selects and returns the fieldset with entity types and bundles.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return mixed
   */
  public function ajaxCallback(array &$form, FormStateInterface $form_state) {
    return $form['availability']['add'];
  }

  /**
   * Submit handler for the "remove one" button.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function removeOneCallback(array &$form, FormStateInterface $form_state) {
    $removed_index = $form_state->get('removed_index');
    $triggering_element = $form_state->getTriggeringElement();
    // Get removed index from trigger object.
    $trigger_index = $triggering_element['#attributes']['data-remove-index'] ?? NULL;

    if ($trigger_index) {
      $removed_index = array_merge($removed_index, [$trigger_index]);
      $form_state->set('removed_index', $removed_index);
    }

    $form_state->setRebuild();
  }

  /**
   * Returns colors that correspond with the given temperature.
   *
   * @param string $entity_type
   *   The entity type.
   *
   * @return array
   *   An associative array of colors that correspond to the given color
   *   temperature, suitable to use as form options.
   */
  protected function getBundleByEntityType($entity_type) {
    $bundle_info = \Drupal::service('entity_type.bundle.info');
    $bundle_options = [];
    if (($bundles = $bundle_info->getBundleInfo($entity_type))) {
      foreach ($bundles as $bundle_key => $bundle) {
        $bundle_options[$bundle_key] = $bundle['label'];
      }
    }
    // Add custom bundle type for DX8 templates.
    if ($entity_type == 'dx8_templates') {
      $bundle_options['dx8_templates'] = $this->t('Templates');
    }

    return $bundle_options;
  }

  /**
   * Required by machine name field validation.
   *
   * @param $value
   *
   * @return bool
   */
  public function exists($value) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state, $redirect = NULL) {
    /** @var \Drupal\cohesion_elements\Entity\CohesionElementEntityBase $entity ; */
    $entity = $this->entity;

    // Merge entity types and bundles data.
    [$types, $bundles] = $this->filterEntityTypes($form_state);
    $entity->set('entity_type_access', $types);
    $entity->set('bundle_access', $bundles);

    // Set entity id.
    $this->setEntityIdFromForm($entity, $form_state);

    // Save the preview image file uri.
    $preview_image = $form_state->getValue('preview_image');

    if (is_array($preview_image) && isset($preview_image[0])) {
      $file_entity = File::load($preview_image[0]);
      $entity->setPreviewImage($file_entity->getFileUri());
    }else{
      $entity->setPreviewImage('');
    }

    return parent::save($form, $form_state);
  }

  /**
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  protected function filterEntityTypes(FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $select_index = $form_state->get('active_index');
    $results = [];
    $data = ['types' => [], 'bundles' => []];
    // Extract entity types and corresponding bundles from form state values.
    $select_data = array_filter($values, function ($key) {
      return ((strpos($key, 'entity_type_access') !== FALSE) || (strpos($key, 'bundle_access') !== FALSE)) ?: FALSE;
    }, ARRAY_FILTER_USE_KEY);

    // Merge common type options to prevent duplications.
    $merge_options = function ($value = []) {
      $results = [];
      foreach ($value as $val) {
        $results = array_merge($results, $val);
      }
      return array_unique($results);
    };

    foreach ($select_index as $i) {
      $type = $select_data['entity_type_access_' . $i] ?? NULL;
      $bundles = isset($select_data['bundle_access_' . $i]) ? array_values($select_data['bundle_access_' . $i]) : [];
      if ($type && $bundles) {
        $results[$type][$i] = array_filter($bundles);
      }
    }

    foreach ($results as $type => $options) {
      $data['types'][] = $type;
      $data['bundles'][] = $merge_options($options);
    }
    return [$data['types'] ?: [], $data['bundles'] ?: []];
  }

  /**
   * Validate the Element form.
   *
   * @inheritdoc
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // Check if the machine name is empty.
    if (empty($form_state->getValue('machine_name'))) {
      $form_state->setErrorByName('machine_name', $this->t('The machine name cannot be empty.'));
    }
  }

}
