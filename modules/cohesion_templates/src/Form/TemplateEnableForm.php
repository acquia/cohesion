<?php

namespace Drupal\cohesion_templates\Form;

use Drupal\cohesion\Form\CohesionEnableForm;

/**
 * Class TemplateEnableForm.
 *
 * Builds the form to delete Site Studio custom styles entities.
 *
 * @package Drupal\cohesion_templates\Form
 */
class TemplateEnableForm extends CohesionEnableForm {

  /**
   * {@inheritdoc}
   */
  public function getDescription() {

    $description = $this->t('Enabling a @entity_type will make it available for use.', [
      '@entity_type' => ucfirst(strtolower($this->entity->getEntityType()->get('label_singular'))),
    ]);

    if ($this->entity->getEntityTypeId() === 'cohesion_content_templates' && $this->entity->get('view_mode') !== 'full') {
      $description = $this->t('Enabling a Content template will apply it to your view mode.');
    }

    return $description;
  }

}
