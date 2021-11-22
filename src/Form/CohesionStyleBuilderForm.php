<?php

namespace Drupal\cohesion\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class CohesionBaseForm.
 *
 * @package Drupal\cohesion\Form
 */
class CohesionStyleBuilderForm extends CohesionBaseForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $form['#attributes']['class'][] = 'cohesion-style-builder-edit-form';

    $form['cohesion']['#token_browser'] = 'style-guide';

    return $form;
  }

}
