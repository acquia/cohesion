<?php

namespace Drupal\cohesion_custom_styles\Form;

use Drupal\cohesion\Form\CohesionDeleteForm;

/**
 * Builds the form to delete Site Studio custom styles entities.
 */
class CustomStylesDeleteForm extends CohesionDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Deleting a <em>Custom style</em> will remove its CSS from your style sheet and delete the configuration of your <em>Custom style</em>. 
       Any extended styles will also be deleted. This action cannot be undone.');
  }

}
