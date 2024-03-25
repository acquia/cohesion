<?php

namespace Drupal\cohesion_style_helpers\Form;

use Drupal\cohesion\Form\CohesionDeleteForm;

/**
 * Class StyleHelpersDeleteForm.
 *
 * Builds the form to delete Site Studio custom styles entities.
 *
 * @package Drupal\cohesion_style_helpers\Form
 */
class StyleHelpersDeleteForm extends CohesionDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Deleting a <em>Style helper</em> will delete it’s configuration. This action cannot be undone.');
  }

}
