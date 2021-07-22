<?php

namespace Drupal\cohesion_elements\Form;

use Drupal\cohesion\Form\CohesionDeleteForm;

/**
 * Class HelperDeleteForm.
 *
 * @package Drupal\cohesion_elements\Form
 */
class HelperDeleteForm extends CohesionDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Deleting a <em>Helper</em> will delete it’s configuration. This action cannot be undone.');
  }

}
