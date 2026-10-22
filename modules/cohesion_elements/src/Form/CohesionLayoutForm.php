<?php

namespace Drupal\cohesion_elements\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;

/**
 * Cohesion layout form.
 *
 * @package Drupal\cohesion_elements\Form
 */
class CohesionLayoutForm extends EntityForm {

  /**
   * @var \Drupal\cohesion_elements\Entity\CohesionLayout
   */
  protected $entity;

  /**
   * @var mixed
   */
  protected $component_instance_uuid;

  /**
   * @var mixed
   */
  protected $component_id;

  /**
   * @var \Drupal\cohesion_elements\Entity\Component
   */
  protected $component_entity;

  /**
   * @var string
   */
  protected $component_form_json;

  /**
   * @var mixed
   */
  protected $cohesion_layout_revision_id;

  /**
   * @var string
   */
  protected $operation;

  /**
   * @var bool
   */
  protected $submit = TRUE;

  /**
   * CohesionLayoutForm constructor.
   */
  public function __construct() {
    $this->component_instance_uuid = \Drupal::request()->attributes->get('component_instance_uuid');
    $this->component_id = \Drupal::request()->attributes->get('component_id');
    $this->cohesion_layout_revision_id = \Drupal::request()->attributes->get('cohesion_layout_revision');
    $langcode = \Drupal::languageManager()->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();
    $entityTypeManager = \Drupal::service('entity_type.manager');
    /** @var \Drupal\Core\Entity\RevisionableStorageInterface $storage */
    $storage = $entityTypeManager->getStorage('cohesion_layout');
    $current_entity = $storage->loadRevision($this->cohesion_layout_revision_id);

    // Load the entity.
    if ($this->cohesion_layout_revision_id && !($current_entity->hasTranslation($langcode) && $this->entity = $current_entity->getTranslation($langcode))) {
      $this->entity = $current_entity;
    }

    // Load the component JSON.
    if ($this->component_id && ($this->component_entity = \Drupal::service('entity_type.manager')
      ->getStorage('cohesion_component')
      ->load($this->component_id))) {
      // Return the json data.
      $this->component_form_json = $this->component_entity->getJsonValues();
    }
    else {
      return FALSE;
    }

    $this->operation = 'edit';
    $this->setModuleHandler(\Drupal::moduleHandler());
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Continue.
    $form = parent::buildForm($form, $form_state);

    $form['#attributes']['class'] = [
      'cohesion-component-in-context',
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
      '#isContentEntity' => $this->entity instanceof ContentEntityInterface,
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
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);

    $actions['submit']['#ajax'] = [
      'callback' => function ($form, FormStateInterface $form_state) {
        return new AjaxResponse([]);
      },
      'wrapper' => 'cohesion',
    ];
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  protected function copyFormValuesToEntity(EntityInterface $entity, array $form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    if ($this->entity instanceof EntityWithPluginCollectionInterface) {
      // Do not manually update values represented by plugin collections.
      $values = array_diff_key($values, $this->entity->getPluginCollections());
    }

    $entity->setJsonValue($values['json_values']);

  }

}
