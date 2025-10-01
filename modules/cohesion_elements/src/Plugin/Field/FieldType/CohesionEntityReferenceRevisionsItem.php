<?php

namespace Drupal\cohesion_elements\Plugin\Field\FieldType;

use Drupal\cohesion_elements\Entity\CohesionLayout;
use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_reference_revisions\Plugin\Field\FieldType\EntityReferenceRevisionsItem;

/**
 * Defines the 'cohesion_entity_reference_revisions' entity field type.
 *
 * Supported settings (below the definition's 'settings' key) are:
 * - target_type: The entity type to reference. Required.
 * - target_bundle: (optional): If set, restricts the entity bundles which may
 *   may be referenced. May be set to an single bundle, or to an array of
 *   allowed bundles.
 *
 * @FieldType(
 *   id = "cohesion_entity_reference_revisions",
 *   label = @Translation("Site Studio Entity reference revisions"),
 *   description = @Translation("An entity field containing a CohesionLayout
 *   entity reference to a specific revision."), category = "Site Studio",
 *   no_ui = FALSE, class =
 *   "\Drupal\cohesion_elements\Plugin\Field\FieldType\CohesionEntityReferenceRevisionsItem",
 *   list_class =
 *   "\Drupal\entity_reference_revisions\EntityReferenceRevisionsFieldItemList",
 *   default_formatter = "cohesion_entity_reference_revisions_entity_view",
 *   default_widget = "cohesion_layout_builder_widget", cardinality = 1
 * )
 */
class CohesionEntityReferenceRevisionsItem extends EntityReferenceRevisionsItem {

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $element['target_type'] = [
      '#type' => 'textfield',
      '#value' => $this->getSetting('target_type'),
      '#required' => TRUE,
      '#disabled' => TRUE,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::fieldSettingsForm($form, $form_state);

    if (!empty($form_state->getUserInput()) && isset($form_state->getUserInput()['settings']['access_elements'])) {
      $must_confirm = $form_state->getUserInput()['settings']['access_elements'] == 1;
    }
    else {
      $must_confirm = $this->getSetting('access_elements') == 1;
    }

    $form['access_elements'] = [
      '#type' => 'details',
      '#title' => t('Element access'),
      '#open' => $must_confirm,
      '#tree' => TRUE,
      '#process' => [[get_class($this), 'formProcessMergeParent']],
    ];

    $form['access_elements']['warning'] = [
      '#prefix' => '<div class="messages messages--warning">',
      '#suffix' => '</div>',
      '#markup' => t('This feature is not recommended. It provides content creators with access to primitive elements. Content added to primitive elements will be stored as JSON along with layout information. You will not be able to separate content from layout at a later date.'),
    ];

    $form['access_elements']['access_elements'] = [
      '#type' => 'select',
      '#title' => t('Allow access to elements on this field.'),
      '#options' => [
        0 => 'No',
        1 => 'Yes',
      ],
      '#default_value' => $this->getSetting('access_elements') ?? 0,
      '#required' => TRUE,
      '#ajax' => TRUE,
    ];

    if ($must_confirm) {
      // Add a confirmation checkbox to the form for enabling elements on a
      // content entity.
      $form['access_elements']['confirm_access_elements'] = [
        '#type' => 'checkbox',
        '#title' => t('Confirm you understand the implications of enabling access to elements on a content entity.'),
        '#default_value' => FALSE,
        '#required' => TRUE,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
      'access_elements' => 0,
    ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function preSave() {
    if ($this->entity && $this->entity instanceof CohesionLayout) {

      $this->entity->setHost($this->getEntity());
      $this->entity->isDefaultRevision($this->entity->getHost()
        ->isDefaultRevision());

      // Save if during a dx8 batch (rebuild/in use)
      $running_dx8_batch = &drupal_static('running_dx8_batch');
      if ($running_dx8_batch) {
        $this->entity->setNeedsSave(TRUE);
      }
    }

    parent::preSave();
  }

}
