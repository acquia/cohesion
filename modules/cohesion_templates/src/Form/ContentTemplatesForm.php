<?php

namespace Drupal\cohesion_templates\Form;

use Drupal\cohesion\TemplateStorage\TemplateStorageBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Url;

/**
 * Content Templates Form.
 *
 * @package Drupal\cohesion_templates\Form
 */
class ContentTemplatesForm extends TemplateForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $operation = $this->getOperation();

    // Are we adding or editing an entity?
    $request = \Drupal::request();

    switch ($operation) {
      case 'add':

        // Default: Add.  Initialise the entity.
        $this->entity->setDefaultValues();
        $entity_type = $request->attributes->get('content_entity_type');
        $this->entity->set('entity_type', $entity_type);
        $bundle = $request->attributes->get('bundle');
        $this->entity->set('bundle', $bundle);
        $view_mode = 'full';
        $this->entity->set('view_mode', $view_mode);
        break;

      case 'duplicate':
        $this->entity->set('default', FALSE);
        $entity_type = $this->entity->get('entity_type');
        $bundle = $this->entity->get('bundle');
        $view_mode = $this->entity->get('view_mode');
        break;

      default:
        $entity_type = $this->entity->get('entity_type');
        $bundle = $this->entity->get('bundle');
        $view_mode = $this->entity->get('view_mode');
        break;
    }

    // Attach the tokens.
    $token_entity_mapper = \Drupal::service('token.entity_mapper');
    $token_type = $token_entity_mapper->getTokenTypeForEntityType($entity_type);
    $form['cohesion']['#token_browser'] = ($token_type) ? $token_type : '';

    // Set Drupal field endpoint.
    $language_none = \Drupal::languageManager()->getLanguage(LanguageInterface::LANGCODE_NOT_APPLICABLE);

    $form['#attached']['drupalSettings']['cohesion']['contextualKey'] = Url::fromRoute('cohesion.entity_fields', [
      'entity_type' => $entity_type,
      'entity_bundle' => $bundle,
    ], ['language' => $language_none])->toString();

    // Show content type (read-only)
    $bundle_info = \Drupal::service('entity_type.bundle.info');
    $bundles = $bundle_info->getBundleInfo($entity_type);
    if (array_key_exists($bundle, $bundles)) {
      $bundle_label = $bundles[$bundle]['label'];
    }
    else {
      $bundle_label = t('Global');
    }

    // Set titles for scratch entity creation and pseudo create/activation.
    if ($operation == 'add' || !$this->entity->get('modified')) {

      // Apply to the form page title.
      if ($bundle_label) {
        $form['#title'] = t('Create @bundle_label template', ['@bundle_label' => $bundle_label]);
      }
    }

    $form['details']['bundle'] = [
      '#type' => 'hidden',
      '#default_value' => $bundle,
      '#access' => TRUE,
      '#weight' => 1,
    ];

    // Show the content type (for display purposes only).
    $form['details']['bundle_label'] = [
      '#type' => 'textfield',
      '#title' => t('Type'),
      '#maxlength' => 255,
      // Use the content type label for this field rather than the machine name.
      '#default_value' => $bundle_label,
      '#disabled' => TRUE,
      '#required' => TRUE,
      '#access' => TRUE,
      '#weight' => 2,
    ];

    $form['details']['machine_name'] = [
      '#title' => $this->t('Machine name'),
      '#maxlength' => 32 - ($this->entity->isNew() ? strlen($this->entity->getEntityMachineNamePrefix()) : 0),
      '#access' => TRUE,
      '#weight' => 1,
      '#description' => $this->entity->isNew() ? $this->entity->getEntityMachineNamePrefix() : '',
      '#description_display' => 'before',
      '#default_value' => $this->entity->isNew() ? str_replace($this->entity->getEntityMachineNamePrefix(), '', $this->entity->id() ?? '') : $this->entity->id(),
      '#type' => 'ajax_machine_name',
      '#required' => FALSE,
      '#machine_name' => [
        'source' => ['details', 'label'],
        'label' => t('Machine name'),
        'replace_pattern' => '[^a-z0-9\_]+',
        'replace' => '_',
        'field_prefix' => $this->entity->isNew() ? $this->entity->getEntityMachineNamePrefix() : '',
        'exists' => [$this, 'checkUniqueMachineName'],
        'entity_type_id' => $this->entity->getEntityTypeId(),
        'entity_id' => $this->entity->id(),
      ],
      '#disabled' => !$this->entity->canEditMachineName(),
    ];

    // Show default & master template fields for full content templates.
    if ($view_mode == 'full') {
      $master_template_ids = \Drupal::service('entity_type.manager')->getStorage('cohesion_master_templates')->getQuery()
        ->accessCheck(TRUE)
        ->condition('status', TRUE)
        ->condition('selectable', TRUE)
        ->execute();
      $master_template_storage = $this->entityTypeManager->getStorage('cohesion_master_templates');
      $master_templates = $master_template_storage->loadMultiple($master_template_ids);

      $form['details']['master_template'] = [
        '#type' => 'select',
        '#title' => t('Master template'),
        '#options' => [
          '__none__' => t('Default'),
        ],
        '#required' => FALSE,
        '#access' => TRUE,
        '#weight' => 2,
      ];
      foreach ($master_templates as $key => $template) {
        // Show templates if they're enabled or being used.
        if ($template->status() || ($this->entity->get('master_template') == $key)) {
          $form['details']['master_template']['#options'][$key] = $template->get('label');
        }
      }
      $form['details']['master_template']['#default_value'] = $this->entity->get('master_template');

      // Show 'set default' option for entity-specific full content templates.
      if ($bundle != '__any__') {
        $form['set_default'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Set as default'),
          '#default_value' => ($this->entity->get('default')) ? TRUE : FALSE,
          '#weight' => 30,
        ];
      }
    }
    else {
      unset($form['selectable']);
    }

    return $form;
  }

  /**
   * Save the Content template and set status/modified.
   *
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state, $redirect = NULL) {

    $entity_type = $this->entity->get('entity_type');
    $base_hook = $this->entity->get('entity_type');
    $bundle = $this->entity->get('bundle');
    $view_mode = $this->entity->get('view_mode');
    $machine_name = $form_state->getValue('machine_name');

    // Set ID and custom flag if adding a custom template.
    if ($this->entity->isNew()) {
      $this->entity->set('custom', TRUE);
      $machine_name = $this->entity->getEntityMachineNamePrefix() . $machine_name;
    }

    $this->entity->set('id', $machine_name);

    \Drupal::moduleHandler()->alter('cohesion_templates_' . $entity_type . '_base_hook', $base_hook);

    // Node_type specific templates use custom prefix and suggestion.
    $filename = str_replace('_', '-', sprintf('%s' . TemplateStorageBase::TEMPLATE_PREFIX . '-%s', $base_hook, $this->entity->get('id')));
    $this->entity->set('twig_template', $filename);

    // Set as default template?
    // Only full content templates, of which there can be several, offer the
    // option to choose the default.  For all others, always set as default.
    if ($view_mode == 'full' && $bundle != '__any__' && !$form_state->getValue('set_default')) {
      $this->entity->setDefault(FALSE);
    }
    else {
      $this->entity->setDefault(TRUE);
    }

    $entity_type_id = $this->entity->getEntityTypeId();

    return parent::save(
      $form,
      $form_state,
      Url::fromRoute(
        "entity.{$entity_type_id}.collection",
        ['content_entity_type' => $entity_type]
      )
    );
  }

  /**
   *
   * @inheritdoc
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);

    // Remove delete action on non full content template.
    if ((isset($actions['delete']) && $this->entity->get('view_mode') !== 'full') || !$this->entity->isModified()) {
      unset($actions['delete']);
    }

    return $actions;
  }

}
