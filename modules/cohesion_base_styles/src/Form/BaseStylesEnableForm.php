<?php

namespace Drupal\cohesion_base_styles\Form;

use Drupal\cohesion\Form\CohesionEnableForm;

/**
 * Class BaseStylesEnableForm.
 *
 * Builds the form to disable custom style.
 *
 * @package Drupal\cohesion_base_styles\Form
 */
class BaseStylesEnableForm extends CohesionEnableForm {

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Enabling a <em>Base style</em> will apply its CSS to your style sheet.');
  }

}
