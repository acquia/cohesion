<?php

namespace Drupal\cohesion_elements\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class ComponentForm.
 *
 * @package Drupal\cohesion_elements\Form
 */
class ComponentForm extends ElementBaseForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $form['details']['has_quick_edit'] = [
      '#type' => 'checkbox',
      '#title' => t('Enable inline editing'),
      '#default_value' => $this->entity->get('has_quick_edit') !== NULL ? $this->entity->get('has_quick_edit') : TRUE,
      '#weight' => 4,
    ];

    $form['details']['#help_link'] = \Drupal::service('cohesion.support_url')->getSupportUrlPrefix() . 'component-form-builder-details';

    $form_state->setCached(FALSE);
    // Tell Angular that this is a component sidebar.
    $form['#attached']['drupalSettings']['cohesion']['isComponentForm'] = TRUE;
    return $form;
  }

}
