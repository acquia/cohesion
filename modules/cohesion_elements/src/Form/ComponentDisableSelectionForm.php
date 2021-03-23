<?php

namespace Drupal\cohesion_elements\Form;

use Drupal\cohesion\Form\CohesionDisableSelectionForm;

/**
 * Class ComponentDisableSelectionForm.
 *
 * Builds the form to delete Site Studio custom styles entities.
 *
 * @package Drupal\cohesion_elements\Form
 */
class ComponentDisableSelectionForm extends CohesionDisableSelectionForm {

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Disabling selection of your <em>Component</em> will remove it from the Sidebar browser to prevent further use.
     All instances where it’s been used will not be affected.');
  }

}
