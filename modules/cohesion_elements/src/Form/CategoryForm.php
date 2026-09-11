<?php

namespace Drupal\cohesion_elements\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Category form.
 *
 * @package Drupal\cohesion_elements\Form
 */
class CategoryForm extends EntityForm {

  /**
   * @var \Drupal\cohesion_elements\Entity\ElementCategoryBase*/
  protected $entity;

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    // Title and machine name.
    $form['details']['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#maxlength' => 255,
      '#default_value' => $this->entity->label(),
      '#required' => TRUE,
      '#access' => TRUE,
      '#weight' => 0,
    ];

    $form['details']['machine_name'] = [
      '#title' => $this->t('Machine name'),
      '#maxlength' => 32 - strlen($this->entity->getEntityMachineNamePrefix()),
      '#access' => TRUE,
      '#weight' => 1,
      '#description_display' => 'before',
      '#default_value' => str_replace($this->entity->getEntityMachineNamePrefix(), '', $this->entity->id() ?? ''),
      '#type' => 'ajax_machine_name',
      '#required' => FALSE,
      '#machine_name' => [
        'source' => ['details', 'label'],
        'label' => t('Machine name'),
        'replace_pattern' => '[^a-z0-9\_]+',
        'replace' => '_',
        'field_prefix' => $this->entity->getEntityMachineNamePrefix(),
        'exists' => [$this, 'checkUniqueMachineName'],
        'entity_type_id' => $this->entity->getEntityTypeId(),
        'entity_id' => $this->entity->id(),
      ],
      '#disabled' => !$this->entity->canEditMachineName(),
    ];

    // COHESION_ELEMENTS_COHESION_COMPONENT_CATEGORY_COUNT.
    $options = [];
    for ($i = 1; $i <= COHESION_ELEMENTS_COHESION_COMPONENT_CATEGORY_COUNT; $i++) {
      $options['category-' . $i] = 'category-' . $i;
    }

    $form['details']['class'] = [
      '#type' => 'color_class_radios',
      '#required' => FALSE,
      '#options' => $options,
      '#default_value' => $this->entity->getClass(),
      '#weight' => 2,
    ];

    // Apply Angular styling to this form.
    $form['#attributes']['class'][] = 'coh-form';

    // Include the Angular css (which controls the cohesion_accordion and other
    // form styling).
    $form['#attached']['library'][] = 'cohesion/cohesion-admin-styles';
    $form['#attached']['library'][] = 'cohesion_elements/component-category';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Check machine name is not empty.
    if (empty($form_state->getValue('machine_name'))) {
      $form_state->setErrorByName('machine_name', $this->t('The machine name cannot be empty.'));
    }

    // Check selected class.
    if (empty($form_state->getValue('class'))) {
      $form_state->setErrorByName('class', $this->t('You must select a color.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state, $redirect = NULL) {
    // Set entity id from supplied machine name.
    $this->entity->set('id', $this->entity->getEntityMachineNamePrefix() . $form_state->getValue('machine_name'));

    if ($this->entity->isNew()) {
      // New weight should be higher than all other entities.
      $storage = $this->entityTypeManager->getStorage($this->entity->getEntityTypeId());
      $query = $storage->getQuery()
        ->accessCheck(TRUE)
        ->range(0, 1)
        ->sort('weight', 'desc');

      if ($ids = $query->execute()) {
        if ($weight_entity = $storage->load(reset($ids))) {
          $this->entity->setWeight($weight_entity->getWeight() + 1);
        }
      }
      else {
        // There are no categories, so safely set weight to 1.
        $this->entity->setWeight(1);
      }
    }

    // Save it and get the status.
    $status = parent::save($form, $form_state);

    // Show status message.
    $message = $this->t('@verb the @type %label.', [
      '@verb' => ($status == SAVED_NEW) ? 'Created' : 'Saved',
      '@type' => $this->entity->getEntityType()->getSingularLabel(),
      '%label' => $this->entity->label(),
    ]);
    \Drupal::messenger()->addMessage($message);

    // Redirect to the entity collection page.
    $form_state->setRedirectUrl($this->entity->toUrl('collection'));
    return $status;
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
    if ($this->entityTypeManager->getStorage($this->entity->getEntityTypeId())->load($this->entity->getEntityMachineNamePrefix() . $value)) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

}
