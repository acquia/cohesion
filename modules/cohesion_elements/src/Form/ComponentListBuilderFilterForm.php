<?php

namespace Drupal\cohesion_elements\Form;

use Drupal\cohesion_elements\Entity\ComponentTag;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the component admin overview filter form.
 *
 * @internal
 */
class ComponentListBuilderFilterForm extends FormBase {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $form = new static();
    $form->setRequestStack($container->get('request_stack'));
    return $form;
  }

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

    $form['filter']['dropzone_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Dropzone'),
      '#title_display' => 'invisible',
      '#options' => [
        'all' => $this->t('All'),
        'dropzone' => $this->t('Dropzone'),
        'non-dropzone' => $this->t('Non-dropzone'),
      ],
      '#default_value' => $this->requestStack->getCurrentRequest()->query->get('dropzone_type', 'all'),
    ];

    $form['filter']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Filter'),
    ];

    $dropzone_type = $this->requestStack->getCurrentRequest()->query->get('dropzone_type', 'all');
    if ($keys || $dropzone_type !== 'all') {
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

    $dropzone_type = $form_state->getValue('dropzone_type', 'all');
    $form_state->setRedirect('entity.cohesion_component.collection', [], [
      'query' => [
        'tags' => $tags,
        'dropzone_type' => $dropzone_type,
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
