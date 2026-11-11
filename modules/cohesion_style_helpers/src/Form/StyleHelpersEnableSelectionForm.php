<?php

namespace Drupal\cohesion_style_helpers\Form;

use Drupal\cohesion\Form\CohesionEnableSelectionForm;

/**
 * Class StyleHelpersEnableSelectionForm.
 *
 * Builds the form to delete Site Studio custom styles entities.
 *
 * @package Drupal\cohesion_style_helpers\Form
 */
class StyleHelpersEnableSelectionForm extends CohesionEnableSelectionForm {

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Enabling selection of your <em>Style helper</em> will include it in the <em>Style helper</em> menu.');
  }

}
