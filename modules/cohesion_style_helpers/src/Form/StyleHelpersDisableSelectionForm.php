<?php

namespace Drupal\cohesion_style_helpers\Form;

use Drupal\cohesion\Form\CohesionDisableSelectionForm;

/**
 * Class StyleHelpersDisableSelectionForm.
 *
 * Builds the form to delete Site Studio custom styles entities.
 *
 * @package Drupal\cohesion_style_helpers\Form
 */
class StyleHelpersDisableSelectionForm extends CohesionDisableSelectionForm {

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Disabling selection of your <em>Style helper</em> will remove it from the <em>Style helper</em> menu to prevent further selection. All instances where it’s been selected will not be affected.');
  }

}
