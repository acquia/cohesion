<?php

namespace Drupal\cohesion\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Class CohesionBaseForm.
 *
 * @package Drupal\cohesion\Form
 */
class CohesionStyleBuilderForm extends CohesionBaseForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $entityTypeId = $this->entity->getEntityTypeId();

    $form['#attributes']['class'][] = 'cohesion-style-builder-edit-form';

    $form['cohesion']['#token_browser'] = 'style-guide';

    // If a style helper then we want to set the entity type to custom style so
    // the preview style can be posted in the same way as custom styles.
    if ($entityTypeId === 'cohesion_style_helpers') {
      $entityTypeId = 'cohesion_custom_style';
    }

    $url = Url::fromRoute('drupal_data_endpoint.preview_style', ['entity_type_id' => $entityTypeId]);
    $form['#attached']['drupalSettings']['cohesion']['urls']['build-style'] = [
      'url' => $url->toString(),
      'method' => 'POST',
    ];

    return $form;
  }

}
