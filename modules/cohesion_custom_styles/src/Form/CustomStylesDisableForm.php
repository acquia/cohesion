<?php

namespace Drupal\cohesion_custom_styles\Form;

use Drupal\cohesion\Form\CohesionDisableForm;

/**
 * Builds the form to disable custom style.
 */
class CustomStylesDisableForm extends CohesionDisableForm {

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Disabling a <em>Custom style</em> will remove its CSS from your style sheet and disable it from selection.
     Any extended styles will also be disabled. The configuration of your <em>Custom style</em> will remain so you can enable it later.');
  }

}
