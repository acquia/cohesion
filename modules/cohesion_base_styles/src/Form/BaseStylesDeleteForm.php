<?php

namespace Drupal\cohesion_base_styles\Form;

use Drupal\cohesion\Form\CohesionDeleteForm;

/**
 * Class BaseStylesDeleteForm.
 *
 * Builds the form to delete Site Studio custom styles entities.
 *
 * @package Drupal\cohesion_base_styles\Form
 */
class BaseStylesDeleteForm extends CohesionDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Deleting a <em>Base style</em> will remove its CSS from your style sheet and delete the configuration of your <em>Base style</em>.
           This action cannot be undone.');
  }

}
