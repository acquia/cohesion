<?php

namespace Drupal\cohesion_custom_styles\Form;

use Drupal\cohesion\Form\CohesionStyleBuilderForm;
use Drupal\cohesion_custom_styles\Entity\CustomStyleType;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Class ContentTemplatesForm.
 *
 * @package Drupal\cohesion_custom_styles\Form
 */
class CustomStylesForm extends CohesionStyleBuilderForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $operation = $this->getOperation();

    // Get the custom style type from url or from the entity.
    if ($operation == 'add') {
      $request = \Drupal::request();
      $custom_style_type_id = $request->attributes->get('custom_style_type');
    }
    else {
      $custom_style_type_id = $this->entity->get('custom_style_type');
    }
    /** @var \Drupal\cohesion_custom_styles\Entity\CustomStyleType $custom_style_type */
    $custom_style_type = $this->entityTypeManager->getStorage('custom_style_type')->load($custom_style_type_id);

    if ($operation == 'extend') {
      $parent_class = $this->entity->getClass();

      // Make a clean duplicate.
      // create a duplicate with a new UUID.
      $this->entity = $this->entity->createDuplicate();
      $this->entity->setDefaultValues();
      $this->entity->set('parent', $parent_class);

    }

    $form = parent::form($form, $form_state);

    if ($operation == 'add') {
      $form['#title'] = $this->t('Create %label', [
        '%label' => strtolower($custom_style_type->label()),
      ]);
    }

    if ($operation == 'extend') {

      $form['#title'] = $this->t('Extend %label', [
        '%label' => strtolower($this->entity->label()),
      ]);

      // Set Title to Extended from: ...
      $form['details']['label']['#default_value'] = $this->t('Extended from @extended', ['@extended' => $form['details']['label']['#default_value']]);

      $form['#attached']['drupalSettings']['cohesion']['clearModel'] = TRUE;
    }

    // Boot angular with the given custom style type.
    $form['#attached']['drupalSettings']['cohesion']['formGroup'] = 'custom_styles';
    $form['#attached']['drupalSettings']['cohesion']['formId'] = $custom_style_type->id();
    $form['#attached']['drupalSettings']['cohOnInitForm'] = \Drupal::service('settings.endpoint.utils')
      ->getCohFormOnInit('custom_styles', $custom_style_type->id());
    $form['#attached']['drupalSettings']['cohesion']['custom_style_type'] = $this->entity->get('custom_style_type');

    // Attached to DrupalSettings javascript to have access to the Parent class
    // name if any.
    if ($this->entity->getParentId()) {
      $storage = \Drupal::entityTypeManager()->getStorage('cohesion_custom_style');
      $parent = $storage->load($this->entity->getParentId());
      if ($parent) {
        $form['#attached']['drupalSettings']['cohesion']['customStyleParentClass'] = str_replace('.', '', $parent->getClass());
      }
    }

    $form['details']['label']['#attributes']['class'] = ['machine-name-source'];

    // Add a class name field - this should match the class name from the json.
    // It should be added to the variable table with it's usages.
    // If it changes it should be taken out the variable table upon save.
    $form['details']['class_name'] = $form['details']['label'];
    $form['details']['class_name']['#description'] = COHESION_CUSTOM_STYLES_CLASS_PREFIX;
    $form['details']['class_name']['#title'] = $this->t('Class Name') . ' ';
    $form['details']['class_name']['#attributes']['class'] = ['class-name'];
    $form['details']['class_name']['#description_display'] = 'before';
    $form['details']['class_name']['#default_value'] = str_replace(COHESION_CUSTOM_STYLES_CLASS_PREFIX, '', $this->entity->get('class_name') ?? '');
    $form['details']['class_name']['#type'] = 'machine_name';
    $form['details']['class_name']['#required'] = FALSE;
    $form['details']['class_name']['#disabled'] = !$this->entity->isNew();
    $form['details']['class_name']['#machine_name'] = [
      'source' => ['details', 'label'],
      'label' => $this->t('Class name'),
      'replace_pattern' => '[^a-z0-9\-]+',
      'replace' => '-',
      'field_prefix' => COHESION_CUSTOM_STYLES_CLASS_PREFIX,
      'exists' => [$this, 'checkUniqueMachineName'],
    ];

    // Stash the current class name.
    $form['original_class_name'] = [
      '#type' => 'hidden',
      '#value' => $this->entity->getClass(),
    ];

    // Show custom style type hidden from user.
    $form['details']['custom_style_type'] = [
      '#type' => 'hidden',
      '#default_value' => $custom_style_type->id(),
      '#required' => TRUE,
      '#access' => TRUE,
    ];

    // Show custom style type (read-only) for display purposes only.
    $form['details']['custom_style_type_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Custom style type'),
      '#maxlength' => 255,
      '#default_value' => $custom_style_type->label(),
      '#disabled' => TRUE,
      '#required' => TRUE,
      '#access' => TRUE,
      '#weight' => 1,
    ];

    // Available in CKEditor as block
    if ($custom_style_type->getElement() != '') {
      // Is the custom type type available in the WYSIWYG?
      $form['available_in_wysiwyg'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Make available in CKEditor editor as block style'),
        '#maxlength' => 255,
        '#default_value' => $this->entity->get('available_in_wysiwyg'),
        '#disabled' => FALSE,
        '#access' => TRUE,
        '#weight' => 9,
      ];
    }
    else {
      $form['available_in_wysiwyg'] = [
        '#type' => 'hidden',
        '#default_value' => FALSE,
      ];
    }

    // Available in CKEditor as inline
    if ($custom_style_type->getElement() != '' || $custom_style_type->id() == 'generic') {
      $form['available_in_wysiwyg_inline'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Make available in CKEditor editor as text style'),
        '#maxlength' => 255,
        '#default_value' => $this->entity->get('available_in_wysiwyg_inline'),
        '#disabled' => FALSE,
        '#access' => TRUE,
        '#weight' => 9,
      ];
    }
    else {
      $form['available_in_wysiwyg_inline'] = [
        '#type' => 'hidden',
        '#default_value' => FALSE,
      ];
    }

    // The custom style is a extended custom style
    if ($this->entity->getParentId()) {
      $parent_entity = \Drupal::entityTypeManager()->getStorage('cohesion_custom_style')->load($this->entity->getParentId());
      if ($parent_entity && !$parent_entity->status()) {
        $form['available_in_wysiwyg']['#disabled'] = TRUE;
        $form['available_in_wysiwyg_inline']['#disabled'] = TRUE;
        $form['status']['#disabled'] = TRUE;
        $form['selectable']['#disabled'] = TRUE;
      }
    }

    // Weight.
    $form['weight'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Weight'),
      '#maxlength' => 3,
      '#default_value' => $this->entity->getWeight(),
      '#weight' => 10,
      '#access' => FALSE,
    ];

    return $form;
  }

  /**
   * Validate the Content template form.
   *
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // Make sure the sent custom style type exists as a CustomStyleType config
    // entity.
    $custom_style_type = $form_state->getValue('custom_style_type');
    $custom_style_types = CustomStyleType::loadMultiple();

    if (!array_key_exists($custom_style_type, $custom_style_types)) {
      $form_state->setErrorByName('custom_style_type', $this->t('The custom style type is invalid.'));
    }

    // Check if the machine name is empty.
    if (empty($form_state->getValue('class_name'))) {
      $form_state->setErrorByName('class_name', $this->t('The class name cannot be empty.'));
    }

    // Note, the machine name check is performed automatically in
    // $this->>checkUniqueMachineName()
  }

  /**
   * Save the Content template and set status/modified.
   *
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state, $redirect = NULL) {
    /** @var \Drupal\cohesion_custom_styles\Entity\CustomStyle $entity */
    $entity = $this->entity;

    // Add the prefix to the machine name (class_name) as it was removed for
    // the form.
    $entity->set('class_name', COHESION_CUSTOM_STYLES_CLASS_PREFIX . $entity->get('class_name'));

    // If the classname has changed, update any extended styles.
    if ($form_state->getValue('original_class_name') !== $this->entity->getClass()) {

      $storage = $this->entityTypeManager->getStorage('cohesion_custom_style');
      $ids = $storage->getQuery()
        ->accessCheck(TRUE)
        ->condition('parent', $form_state->getValue('original_class_name') ?? '')
        ->execute();

      // Loop over the children.
      foreach ($storage->loadMultiple($ids) as $child_entity) {
        // Set their "parent" key to this new classname.
        $child_entity->set('parent', $this->entity->getClass());
        $child_entity->save();
      }
    }

    // Set ID and custom flag if adding a custom template.
    $this->setEntityIdFromForm($entity, $form_state);

    // Set active accordion group.
    if (($url = $this->entity->toUrl('collection')) && ($url instanceof Url)) {
      $style_type_entity = $this->entityTypeManager->getStorage('custom_style_type')->load($this->entity->get('custom_style_type'));
      $url->setOption('query', ['active_group' => strtolower($style_type_entity->get('label'))]);
    }
    return parent::save($form, $form_state, $url);
  }

  /**
   * Check to see if the machine name is unique.
   *
   * @param $value
   *
   * @return bool
   */
  public function checkMachineName($value) {
    $query = \Drupal::entityQuery('cohesion_custom_style')->accessCheck(TRUE);
    $query->condition('class_name', COHESION_CUSTOM_STYLES_CLASS_PREFIX . $value);
    $entity_ids = $query->execute();

    return count($entity_ids) > 0;
  }

  /**
   * Required by machine name field validation.
   *
   * @param $value
   *
   * @return bool
   */
  public function exists($value) {
    return FALSE;
  }

  /**
   * Check to see if the machine name is unique.
   *
   * @param $value
   *
   * @return bool
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function checkUniqueMachineName($value) {

    $query = $this->entityTypeManager->getStorage('cohesion_custom_style')->getQuery();
    $query->condition('class_name', COHESION_CUSTOM_STYLES_CLASS_PREFIX . $value);
    $query->accessCheck(TRUE);
    $entity_ids = $query->execute();

    return count($entity_ids) > 0;
  }

}
