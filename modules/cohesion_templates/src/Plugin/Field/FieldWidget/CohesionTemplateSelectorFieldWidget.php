<?php

namespace Drupal\cohesion_templates\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'cohesion_template_selector_widget' widget.
 *
 * @FieldWidget(
 *   id = "cohesion_template_selector_widget",
 *   label = @Translation("Site Studio template selector widget"),
 *   description = @Translation("Site Studio template selector widget."),
 *   field_types = {
 *     "cohesion_template_selector"
 *   }
 * )
 */
class CohesionTemplateSelectorFieldWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    // Get the entity this form is working on.
    if (method_exists($items, 'getParent')) {
      $entity = $items->getParent()->getValue();
    }
    else {
      return [];
    }

    if ($entity->getEntityTypeId() == 'field_config') {
      $target_entity_type = \Drupal::entityTypeManager()->getStorage($entity->getTargetEntityTypeId());
      $bundle = $entity->getTargetBundle();
      $entity_type = $target_entity_type->getEntityTypeId();
    }
    elseif ($entity instanceof ContentEntityInterface) {
      $bundle = $entity->bundle();
      $entity_type = $entity->getEntityTypeId();
    }
    else {
      // The cohesion template selector field is supported only on content
      // entity and field config.
      return [];
    }

    // Get list of templates for this content type.
    $template_ids = \Drupal::service('entity_type.manager')->getStorage('cohesion_content_templates')->getQuery()
      ->accessCheck(TRUE)
      ->condition('entity_type', $entity_type)
      ->condition('bundle', $bundle)
      ->condition('view_mode', 'full')
      ->condition('status', TRUE)
      ->condition('selectable', TRUE)
      ->sort('default', 'DESC')
      ->execute();

    $template_storage = \Drupal::entityTypeManager()->getStorage('cohesion_content_templates');
    $templates = $template_storage->loadMultiple($template_ids);

    $options = [
      '__default__' => t('Default template'),
    ];

    foreach ($templates as $key => $template) {
      if ($template->status() || $items[$delta]->selected_template == $key) {
        $options[$key] = t('%label', ['%label' => $template->get('label')]);
      }
    }

    $element['selected_template'] = [
      '#type' => 'select',
      '#title' => t('Select template:'),
      '#description' => t('Select a template that will be used to display this content.'),
      '#options' => $options,
      '#default_value' => (isset($options[$items[$delta]->selected_template])) ? $items[$delta]->selected_template : '__default__',
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as &$item) {
      if (isset($item['selected_template']['content'])) {
        $item['selected_template'] = $item['selected_template']['content'];
      }
    }
    return $values;
  }

}
