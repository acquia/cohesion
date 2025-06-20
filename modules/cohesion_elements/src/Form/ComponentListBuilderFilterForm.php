<?php

namespace Drupal\cohesion_elements\Form;

use Drupal\cohesion_elements\Entity\ComponentTag;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the component admin overview filter form.
 *
 * @internal
 */
class ComponentListBuilderFilterForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'components_admin_filter_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $keys = []) {
    $form['#attributes'] = ['class' => ['search-form']];
    $form['filter'] = [
      '#type' => 'details',
      '#title' => $this->t('Filter components'),
      '#open' => TRUE,
      '#attributes' => ['class' => ['container-inline']],
    ];

    $form['filter']['tags'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Select tags'),
      '#title_display' => 'invisible',
      '#default_value' => !empty($keys) ? ComponentTag::loadMultiple($keys) : [],
      '#selection_handler' => 'default',
      '#target_type' => 'cohesion_component_tag',
      '#tags' => TRUE,
      '#placeholder' => 'Filter by tags',
    ];

    $form['filter']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Filter'),
    ];

    if ($keys) {
      $form['filter']['reset'] = [
        '#type' => 'submit',
        '#value' => $this->t('Reset'),
        '#submit' => ['::resetForm'],
        '#limit_validation_errors' => [],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // format the tags.
    $tags = [];
    $values = $form_state->getValue('tags', []);

    if (is_array($values)) {
      foreach ($values as $value) {
        $tags[] = $value['target_id'];
      }
    }

    $form_state->setRedirect('entity.cohesion_component.collection', [], [
      'query' => [
        'tags' => $tags,
      ],
    ]);
  }

  /**
   * Resets the filter selections.
   */
  public function resetForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('entity.cohesion_component.collection');
  }

}
