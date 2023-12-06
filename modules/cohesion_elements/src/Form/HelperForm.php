<?php

namespace Drupal\cohesion_elements\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Helper form.
 *
 * @package Drupal\cohesion_elements\Form
 */
class HelperForm extends ElementBaseForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $form['details']['#help_link'] = \Drupal::service('cohesion.support_url')->getSupportUrlPrefix() . 'helper-edit-details';

    $form_state->setCached(FALSE);
    return $form;
  }

}
