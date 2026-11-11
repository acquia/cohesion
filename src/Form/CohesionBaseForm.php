<?php

namespace Drupal\cohesion\Form;

use Drupal\cohesion\ApiUtils;
use Drupal\cohesion\Services\JsonXss;
use Drupal\cohesion_elements\Entity\Component;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base form for site studio cohesionfield base forms.
 *
 * @package Drupal\cohesion\Form
 */
class CohesionBaseForm extends EntityForm {

  /**
   * @var \Drupal\cohesion\ApiUtils*/
  protected $apiUtils;

  /**
   * @var \Drupal\cohesion\Services\JsonXss*/
  protected $jsonXss;

  /**
   * CohesionBaseForm constructor.
   *
   * @param \Drupal\cohesion\ApiUtils $api_utils
   * @param \Drupal\cohesion\Services\JsonXss $json_xss
   */
  public function __construct(ApiUtils $api_utils, JsonXss $json_xss) {
    $this->apiUtils = $api_utils;
    $this->jsonXss = $json_xss;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return \Drupal\cohesion\Form\CohesionBaseForm|\Drupal\Core\Entity\EntityForm
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
    // Load the service required to construct this class.
      $container->get('cohesion.api.utils'),
      $container->get('cohesion.xss')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $operation = $this->getOperation();
    switch ($operation) {
      case 'add':
        $form['#title'] = t('Create %label', [
          '%label' => $this->entityTypeManager->getStorage($this->entity->getEntityTypeId())->getEntityType()->getSingularLabel(),
        ]);
        break;

      case 'edit':
        $form['#title'] = $this->t('Edit %label', [
          '%label' => strtolower($this->entity->label()),
        ]);
        break;

      case 'duplicate':
        // Clone the entity.
        // Create a duplicate with a new UUID.
        $this->entity = $this->entity->createDuplicate();

        $form['#title'] = $this->t('Duplicate of %label', [
          '%label' => strtolower($this->entity->label()),
        ]);

        break;
    }

    /** @var \Drupal\cohesion\Entity\CohesionConfigEntityBase $entity */
    $entity = $this->entity;
    $form_class = str_replace('_', '-', $entity->getEntityTypeId()) . '-' . str_replace('_', '-', $entity->id() ?? '') . '-form';
    $form_class_entity = str_replace('_', '-', $entity->getEntityTypeId()) . '-edit-form';

    $jsonValue = $entity->getJsonValues() ? $entity->getJsonValues() : "{}";
    $jsonMapper = $entity->getJsonMapper() ? $entity->getJsonMapper() : "{}";

    if (!$this->jsonXss->userCanBypass()) {
      // Initial entity xss paths.
      $form_state->setTemporaryValue('xss_paths_entity', $this->jsonXss->buildXssPaths($jsonValue));
    }

    // Retain field values if validation error.
    if ($response = $this->getRequest()->request->all()) {

      $jsonValue = ($response && isset($response['json_values'])) ? $response['json_values'] : $jsonValue;
      $jsonMapper = ($response && isset($response['json_mapper'])) ? $response['json_mapper'] : $jsonMapper;

      if (!$this->jsonXss->userCanBypass()) {
        // Save the response xss paths.
        $form_state->setTemporaryValue('xss_paths_response', $this->jsonXss->buildXssPaths($jsonValue));
      }
    }
    else {
      // New form or initial load (form has never been submitted).
      $form_state->setTemporaryValue('xss_paths_response', $form_state->getTemporaryValue('xss_paths_entity'));
    }

    // Regenerate UUID for duplicate component entity.
    // @todo this logic should be in the child form.
    if ($this->getOperation() == 'duplicate' && $entity instanceof Component) {
      $jsonValue = $this->apiUtils->uniqueJsonKeyUuids($jsonValue);
    }

    // Stash the Xss paths for this entity.
    if (!$this->jsonXss->userCanBypass()) {
      $form['#attached']['drupalSettings']['cohesion']['xss_paths'] = $form_state->getTemporaryValue('xss_paths_response');
    }

    // Field instance.
    $form['cohesion'] = [
      // Drupal\cohesion\Element\CohesionField.
      '#type' => 'cohesionfield',
      '#json_values' => $jsonValue,
      '#json_mapper' => $jsonMapper,
      '#classes' => [$form_class_entity, $form_class],
      '#entity' => $entity,
      '#cohFormGroup' => $entity->getAssetGroupId(),
      '#cohFormId' => $entity->id(),
      '#isContentEntity' => $entity instanceof ContentEntityInterface,
    ];

    if ($entity->isLayoutCanvas()) {
      $form['cohesion']['#canvas_name'] = 'config_layout_canvas';
    }

    $form['details'] = [
      '#type' => 'details',
      '#title' => t('Details'),
      '#weight' => -99,
      'label' => [
        '#type' => 'textfield',
        '#title' => $this->t('Title'),
        '#maxlength' => 255,
        '#default_value' => $entity->label(),
        '#required' => TRUE,
        '#access' => TRUE,
        '#weight' => 0,
      ],
      '#open' => 'panel-open',
    ];
    $form['details']['#attached']['library'][] = 'cohesion/cohesion-accordion-element';

    if ($operation == 'duplicate') {
      $form['details']['label']['#default_value'] = t('Duplicate of @duplicate', ['@duplicate' => $form['details']['label']['#default_value']]);
    }

    if ($this->entity->getEntityType()->hasKey('status')) {
      $form['status'] = [
        '#title' => $this->t('Enable'),
        '#type' => 'checkbox',
        '#default_value' => $entity->isModified() ? $entity->status() : TRUE,
        '#weight' => 10,
      ];
    }

    if ($this->entity->getEntityType()->hasKey('selectable')) {
      $form['selectable'] = [
        '#title' => $this->t('Enable selection'),
        '#type' => 'checkbox',
        '#default_value' => $entity->isModified() ? $entity->isSelectable() : TRUE,
        '#weight' => 10,
      ];
    }

    // Add the shared attachments.
    _cohesion_shared_page_attachments($form);

    /* You will need additional form elements for your custom properties. */
    // Add a submit button that handles the submission of the form.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];
    return $form;
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // Xss validation.
    if (!$this->jsonXss->userCanBypass()) {
      $original_entity_xss_paths = $form_state->getTemporaryValue('xss_paths_entity');
      $highlighted_elements = [];

      foreach ($this->jsonXss->buildXssPaths($form_state->getValue('json_values')) as $path => $new_value) {
        // Only test if the user changed the value or it's a new value.
        // If it's the same, no need to test.
        if (!isset($original_entity_xss_paths[$path]) || $original_entity_xss_paths[$path] !== $new_value) {
          // Drupal error.
          $form_state->setErrorByName('cohesion', $this->t('You do not have permission to add tags and attributes that fail XSS validation.'));

          // So set the XSS paths to the initial so the user has a chance to
          // change something they've edited otherwise the illegal value they've
          // just entered will be detected as a XSS entry and disabled).
          $form['#attached']['drupalSettings']['cohesion']['xss_paths'] = $form_state->getTemporaryValue('xss_paths_entity');

          // Highlighted element.
          $highlighted_elements[] = explode('.', $path)[0];
        }
      }

      // Highlight elements with errors.
      // See processCohesionError().
      $cohesion_layout_canvas_error = &drupal_static('cohesion_layout_canvas_error');
      $cohesion_layout_canvas_error = array_unique($highlighted_elements);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state, $redirect = NULL) {
    // Set modified on save
    // Only on form and not on entity as modified should be set upon user action
    // not code.
    $this->entity->setModified();

    $status = parent::save($form, $form_state);

    // Show status message.
    $message = $this->t('@verb the @type %label.', [
      '@verb' => ($status == SAVED_NEW) ? 'Created' : 'Saved',
      '@type' => $this->entity->getEntityType()->getSingularLabel(),
      '%label' => $this->entity->label(),
    ]);
    \Drupal::messenger()->addMessage($message);

    \Drupal::request()->query->remove('destination');

    $element = $form_state->getTriggeringElement();
    if (isset($element['#continue']) && $element['#continue']) {
      $form_state->setRedirectUrl($this->entity->toUrl());
    }
    elseif ($redirect) {
      $form_state->setRedirectUrl($redirect);
    }
    else {
      $form_state->setRedirectUrl($this->entity->toUrl('collection'));
    }
    return $status;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    // Add a "Continue" button.
    $actions['continue'] = $actions['submit'];
    // If the "Continue" button is clicked, redirect back to same page.
    $actions['continue']['#continue'] = TRUE;
    $actions['continue']['#dropbutton'] = 'save';
    $actions['continue']['#value'] = t('Save and continue');
    $actions['continue']['#weight'] = 0;

    // Add a "Save" button.
    $actions['enable'] = $actions['submit'];
    $actions['enable']['#continue'] = FALSE;
    $actions['enable']['#dropbutton'] = 'save';
    $actions['enable']['#value'] = t('Save');
    $actions['enable']['#weight'] = 1;

    // Remove the "Save" button.
    $actions['submit']['#access'] = FALSE;

    return $actions;
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
   * Set the entity ID based on the machine_name field in the form or generate
   * a random id if no machine_name field.
   *
   * @param $entity
   * @param $form_state
   */
  public function setEntityIdFromForm($entity, $form_state) {
    // If the form has a machine name field, use it as the id for the entity..
    if ($machine_name = $form_state->getValue('machine_name')) {
      $entity->set('id', $this->entity->getEntityMachineNamePrefix() . $machine_name);
    }
    // If form doesn't have a machine name field, generate a random ID for the
    // entity.
    else {
      if ($entity->isNew() || $entity->id() === NULL) {
        $entity->set('id', implode('_', [
          hash('crc32b', $entity->uuid()),
        ]));
      }
    }
  }

  /**
   * Check to see if the machine name is unique.
   *
   * @param $value
   *
   * @return bool
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function checkUniqueMachineName($value) {

    $query = $this->entityTypeManager->getStorage($this->entity->getEntityTypeId())->getQuery();
    $parameter = $this->entity->getEntityMachineNamePrefix() . $value;
    $query->condition('id', $parameter);
    $query->accessCheck(TRUE);
    $entity_ids = $query->execute();

    return count($entity_ids) > 0;
  }

}
