<?php

namespace Drupal\cohesion_elements\Form;

use Drupal\cohesion\Form\CohesionDisableSelectionForm;

/**
 * Helper disable selection form.
 *
 * @package Drupal\cohesion_elements\Form
 */
class HelperDisableSelectionForm extends CohesionDisableSelectionForm {

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Disabling selection of your <em>Helper</em> will remove it from the Sidebar browser to prevent further use.');
  }

}
