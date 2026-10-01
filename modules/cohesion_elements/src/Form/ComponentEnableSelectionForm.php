<?php

namespace Drupal\cohesion_elements\Form;

use Drupal\cohesion\Form\CohesionEnableSelectionForm;

/**
 * Class ComponentEnableSelectionForm.
 *
 * Builds the form to delete Site Studio custom styles entities.
 *
 * @package Drupal\cohesion_elements\Form
 */
class ComponentEnableSelectionForm extends CohesionEnableSelectionForm {

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Enabling selection of your <em>Component</em> will include it in the Sidebar browser.');
  }

}
