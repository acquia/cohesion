<?php

namespace Drupal\cohesion_custom_styles\Form;

use Drupal\cohesion\Form\CohesionDisableSelectionForm;

/**
 * Builds the form to delete Site Studio custom styles entities.
 */
class CustomStylesDisableSelectionForm extends CohesionDisableSelectionForm {

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Disabling selection of your <em>Custom style</em> will remove it from the <em>Custom style</em> select drop-down to prevent further selection.
     Its CSS will not be removed from your Site Studio style sheet so all instances where it’s been selected will not be affected.');
  }

}
