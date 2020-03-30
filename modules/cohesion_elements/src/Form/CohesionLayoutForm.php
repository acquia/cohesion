<?php

namespace Drupal\cohesion_elements\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CohesionLayoutForm.
 *
 * @package Drupal\cohesion_elements\Form
 */
class CohesionLayoutForm extends ContentEntityForm {

  /**
   * @var mixed
   */
  private $component_instance_uuid;

  /**
   * @var mixed
   */
  private $component_form_json;

  /**
   * {@inheritdoc}
   */
  public function __construct($entity_manager, $entity_type_bundle_info, $time) {
    parent::__construct($entity_manager, $entity_type_bundle_info, $time);

    // Stash the component uuid from the path.
    $this->component_instance_uuid = \Drupal::request()->attributes->get('component_instance_uuid');

    // Load the component form data.
    $component_id = \Drupal::request()->attributes->get('component_id');

    if ($component_id && ($component_entity = \Drupal::service('entity_type.manager')
      ->getStorage('cohesion_component')
      ->load($component_id))) {
      // Return the json data.
      $this->component_form_json = $component_entity->getJsonValues();
    }

  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['#attributes']['class'] = [
      'cohesion-component-in-context',
    ];

    $form['actions']['submit']['#ajax'] = [
      'callback' => '\Drupal\cohesion_elements\Controller\CohesionLayoutModalController::cohesionLayoutAjax',
      'wrapper' => 'cohesion',
    ];

    $json_values = $this->entity->get('json_values')->getValue();
    $json_values = array_shift($json_values);

    $form['cohesion'] = [
      // Drupal\cohesion\Element\CohesionField.
      '#type' => 'cohesionfield',
      '#json_values' => $json_values['value'],
      '#json_mapper' => '{}',
      '#classes' => ['cohesion-component-in-context'],
      '#entity' => $this->entity,
      '#cohFormGroup' => 'in_context',
      '#cohFormId' => 'component',
    ];

    $form['cohesion']['#token_browser'] = 'all';

    // Add the component uuid for the app to use.
    $form['#attached']['drupalSettings']['cohesion']['componentInstanceUuid'] = $this->component_instance_uuid;
    $form['#attached']['drupalSettings']['cohesion']['componentFormJson'] = $this->component_form_json;

    // Add the shared attachments.
    _cohesion_shared_page_attachments($form);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /* @var \Drupal\cohesion_elements\Entity\CohesionLayout $entity */
    $entity = $this->getEntity();
    $values = $form_state->getValues();

    if (isset($values['json_values'])) {
      $entity->setJsonValue($values['json_values']);
      $entity->save();
    }
  }

}
