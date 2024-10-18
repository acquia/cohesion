<?php

namespace Drupal\cohesion_elements\Form;

use Drupal\cohesion\ApiUtils;
use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\cohesion\Services\JsonXss;
use Drupal\cohesion_elements\Entity\ComponentTag;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Uuid\UuidInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Component form.
 *
 * @package Drupal\cohesion_elements\Form
 */
class ComponentForm extends ElementBaseForm {

  /**
   * ComponentForm constructor.
   *
   * @param \Drupal\cohesion\ApiUtils $apiUtils
   * @param \Drupal\cohesion\Services\JsonXss $jsonXss
   * @param \Drupal\Component\Uuid\UuidInterface $uuid
   */
  public function __construct(
    ApiUtils $apiUtils,
    JsonXss $jsonXss,
    protected UuidInterface $uuid,
  ) {
    parent::__construct($apiUtils, $jsonXss);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('cohesion.api.utils'),
      $container->get('cohesion.xss'),
      $container->get('uuid'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $form['details']['#summary_attributes'] = ['data-ssa-help-link' => \Drupal::service('cohesion.support_url')->getSupportUrlPrefix() . 'component-form-builder-details'];
    $form['details']['#attached']['library'][] = 'cohesion/cohesion-accordion-element';

    $tagsDefaultValue = !empty($this->entity->getTag()) ? ComponentTag::loadMultiple($this->entity->getTag()) : [];

    $form['details']['tag'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'cohesion_component_tag',
      '#title' => 'Tags',
      '#description' => t('Add existing tags to a component, if a tag does not exist it will be created.'),
      '#tags' => TRUE,
      '#default_value' => $tagsDefaultValue,
      '#selection_handler' => 'default',
      '#autocreate' => [
        'bundle' => 'cohesion_component_tag',
      ],
    ];

    $form_state->setCached(FALSE);
    // Tell Angular that this is a component sidebar.
    $form['#attached']['drupalSettings']['cohesion']['isComponentForm'] = TRUE;
    if ($this->moduleHandler->moduleExists('tmgmt')) {
      $form['#attached']['drupalSettings']['cohesion']['tmgmt'] = TRUE;
    }
    return $form;
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

    if ($json_values = $form_state->getValue('json_values')) {
      $canvasInstance = new LayoutCanvas($form_state->getValue('json_values'));

      $machine_names = [];
      $undefined_machines_names = [];
      foreach ($canvasInstance->iterateModels('component_form') as $model) {
        if ($model->getElement()->getProperty(['type']) == 'form-field') {

          $machine_name = $model->getProperty(['settings', 'machineName']);
          $element_title = $model->getProperty(['settings', 'title']);
          if ($machine_name == '') {
            $undefined_machines_names[$model->getUUID()] = $element_title;
          }
          else {
            $machine_names[$machine_name][$model->getUUID()] = $element_title;
          }

        }
      }

      $error_count = 0;
      $layout_canvas_error = [];
      if (!empty($undefined_machines_names)) {
        $error_count++;
        $form_state->setErrorByName('cohesion_' . $error_count, $this->t('Undefined machine name(s). Please make sure to define a machine name for these form elements: %machine_names', ['%machine_names' => implode(', ', $undefined_machines_names)]));
        $layout_canvas_error = array_merge($layout_canvas_error, array_keys($undefined_machines_names));
      }

      foreach ($machine_names as $element_machine_name) {
        if (count($element_machine_name) > 1) {
          $error_count++;
          $form_state->setErrorByName('cohesion_' . $error_count, $this->t('Duplicate machine names. Please make sure to define unique machine names form these elements: %machine_names', ['%machine_names' => implode(', ', $element_machine_name)]));
          $layout_canvas_error = array_merge($layout_canvas_error, array_keys($element_machine_name));
        }
      }
    }

    if (!empty($layout_canvas_error)) {
      $form['#attached']['drupalSettings']['cohesion']['layout_canvas_errors'] = $layout_canvas_error;
    }

    // Check if the machine name is empty.
    if (empty($form_state->getValue('machine_name'))) {
      $form_state->setErrorByName('machine_name', $this->t('The machine name cannot be empty.'));
    }

    // Format the tags data.
    $tags = [];
    if ($values = $form_state->getValue('tag')) {
      foreach ($values as $value) {
        // If it's an "entity" then we need to create it.
        if (isset($value['entity'])) {
          // Get the current user & check we have permissions to create a tag.
          $user = \Drupal::currentUser();
          if ($user->hasPermission('administer component tags')) {
            $newTag = $this->createTag($value['entity']);
            // Save the tag.
            $newTag->save();
            // Attach the tag to the component.
            $tags[] = $newTag->get('id');
          }
          // If not show an error message.
          else {
            \Drupal::messenger()->addError($this->t('You do not have permission to create a component tag.'));
          }
        }
        else {
          $tags[] = $value['target_id'];
        }
      }
    }
    $form_state->setValue('tag', $tags);

  }

  /**
   * @param $tagEntity
   * @return \Drupal\cohesion_elements\Entity\ComponentTag
   */
  private function createTag($tagEntity) {

    $machine_name = preg_replace("/[^A-Za-z0-9\s]/", '', strtolower($tagEntity->get('label')));
    $machine_name = str_replace('-', '_', $machine_name);
    $machine_name = str_replace(' ', '_', $machine_name);

    $prefix = ComponentTag::ENTITY_MACHINE_NAME_PREFIX;
    $machine_name = $prefix . $machine_name;

    // Create the new tag.
    return new ComponentTag([
      'uuid' => $this->uuid->generate(),
      'id' => $machine_name,
      'label' => $tagEntity->get('label'),
      'class' => 'category-1',
    ], 'cohesion_component_tag');
  }

}
