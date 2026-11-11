<?php

namespace Drupal\cohesion_custom_styles\Form;

use Drupal\cohesion\Form\CohesionEnableForm;

/**
 * Builds the form to disable custom style.
 */
class CustomStylesEnableForm extends CohesionEnableForm {

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Enabling a <em>Custom style</em> will apply its CSS to your style sheet.');
  }

}
