<?php

namespace Drupal\cohesion_templates\Form;

use Drupal\cohesion\Form\CohesionDisableForm;

/**
 * Class MasterTemplateDisableForm.
 *
 * Builds the form to disable custom style.
 *
 * @package Drupal\cohesion_templates\Form
 */
class MasterTemplateDisableForm extends CohesionDisableForm {

  /**
   * {@inheritdoc}
   */
  public function getDescription() {

    return $this->t('Disabling a <em>Master template</em> will stop it from being used.
     The configuration of your <em>Master template</em> will remain so you can enable it later.');
  }

}
