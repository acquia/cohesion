<?php

namespace Drupal\cohesion_elements\Form;

use Drupal\cohesion\Form\CohesionEnableSelectionForm;

/**
 * Helper enable selection form.
 *
 * @package Drupal\cohesion_elements\Form
 */
class HelperEnableSelectionForm extends CohesionEnableSelectionForm {

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Enabling selection of your <em>Helper</em> will include it in the Sidebar browser.');
  }

}
