<?php

namespace Drupal\cohesion_base_styles\Form;

use Drupal\cohesion\Form\CohesionDisableForm;

/**
 * Class BaseStylesDisableForm.
 *
 * Builds the form to disable custom style.
 *
 * @package Drupal\cohesion_base_styles\Form
 */
class BaseStylesDisableForm extends CohesionDisableForm {

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Disabling a <em>Base style</em> will remove its CSS from your style sheet. 
    The configuration of your <em>Base style</em> will remain so you can enable it later.');
  }

}
