<?php

namespace Drupal\cohesion_templates\Form;

use Drupal\cohesion\Form\CohesionDisableForm;

/**
 * Builds the form to disable custom style.
 */
class ContentTemplateDisableForm extends CohesionDisableForm {

  /**
   * {@inheritdoc}
   */
  public function getDescription() {

    if ($this->entity->getEntityTypeId() === 'cohesion_content_templates' && $this->entity->get('view_mode') !== 'full') {
      return $this->t('Disabling a <em>Content template</em> will stop it from being used by your view mode.
       The configuration of your <em>Content template</em> will remain so you can enable it later.');
    }

    return $this->t('Disabling a <em>Content template</em> will stop it from being used.
     The configuration of your <em>Content template</em> will remain so you can enable it later.');
  }

}
