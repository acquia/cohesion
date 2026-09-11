<?php

namespace Drupal\cohesion_sync\Form;

use Drupal\cohesion\UsagePluginManager;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sync package form.
 *
 * @package Drupal\cohesion_sync\Form
 */
class PackageForm extends EntityForm {

  /**
   * @var \Drupal\cohesion\UsagePluginManager
   */
  protected $usagePluginManager;

  /**
   * PackageForm constructor.
   *
   * @param \Drupal\cohesion\UsagePluginManager $usage_plugin_manager
   */
  public function __construct(UsagePluginManager $usage_plugin_manager) {
    $this->usagePluginManager = $usage_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new self($container->get('plugin.manager.usage.processor'));
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    // Include the Angualar css (which controls the cohesion_accordion and other
    // form styling).
    $form['#attached']['library'][] = 'cohesion/cohesion-admin-scripts';
    $form['#attached']['library'][] = 'cohesion/cohesion-admin-styles';

    // Attach the css/* and js/* library.
    $form['#attached']['library'][] = 'cohesion_sync/sync-package-form';

    /**
     * Hook for JS Sync APP
     */
    $form['app'] = [
      '#markup' => '<div id="ssaApp"></div>',
    // Suppresses https://www.drupal.org/project/drupal/issues/3027240
      '#parents' => [],
    ];

    /**
     * Title and description metadata.
     */
    $form['details'] = [
      '#type' => 'details',
      '#title' => t('Details'),
      '#weight' => -99,
      '#attributes' => [
        'class' => ['package-details'],
      ],
      'label' => [
        '#type' => 'textfield',
        '#title' => $this->t('Title'),
        '#maxlength' => 255,
        '#default_value' => $this->entity->label(),
        '#required' => TRUE,
        '#access' => TRUE,
        '#weight' => 0,
      ],
      'machine_name' => [
        '#type' => 'ajax_machine_name',
        '#title' => $this->t('Machine name'),
        '#default_value' => !empty($this->entity->id()) ? str_replace($this->entity->getEntityMachineNamePrefix(), '', $this->entity->id()) : '',
        '#maxlength' => 32 - strlen($this->entity->getEntityMachineNamePrefix()),
        '#required' => FALSE,
        '#access' => TRUE,
        '#weight' => 0,
        '#attributes' => [
          'class' => ['class-name'],
        ],
        '#description_display' => 'before',
        '#machine_name' => [
          'source' => ['details', 'label'],
          'label' => t('Machine name'),
          'replace_pattern' => '[^a-z0-9\_]+',
          'replace' => '_',
          'field_prefix' => $this->entity->getEntityMachineNamePrefix(),
          'exists' => [$this, 'checkUniqueMachineName'],
          'entity_type_id' => $this->entity->getEntityTypeId(),
          'entity_id' => $this->entity->id(),
        ],
      ],
      'description' => [
        '#type' => 'textarea',
        '#title' => $this->t('Description'),
        '#default_value' => $this->entity->getDescription(),
        '#required' => FALSE,
        '#access' => TRUE,
        '#weight' => 0,
      ],
      '#open' => 'panel-open',
      '#summary_attributes' => ['data-ssa-help-link' => \Drupal::service('cohesion.support_url')->getSupportUrlPrefix() . 'package-edit-details'],
    ];

    $form['details']['#attached']['library'][] = 'cohesion/cohesion-accordion-element';

    /**
     * Excluded entities list.
     */

    $form['excluded'] = [
      '#type' => 'details',
      '#title' => t('Excluded entity types'),
      '#weight' => -99,
      '#attributes' => [
        'class' => ['package-excluded'],
      ],
      '#summary_attributes' => ['data-ssa-help-link' => \Drupal::service('cohesion.support_url')->getSupportUrlPrefix() . 'package-edit-excluded'],
    ];

    $form['excluded']['excluded_types'] = [
      '#markup' => '<div id="package-excluded-container" class="ssa-app package-loading-overlay"></div>',
    ];

    /**
     * LHS package requirements wrappers.
     */
    $form_requirements = [
      '#type' => 'details',
      '#title' => t('Package requirements'),
      '#weight' => -98,
      '#attributes' => [
        'class' => ['package-requirements'],
      ],
      '#open' => 'panel-open',
      '#summary_attributes' => ['data-ssa-help-link' => \Drupal::service('cohesion.support_url')->getSupportUrlPrefix() . 'package-edit-requirements'],
    ];

    $form_requirements['package_requirements_container'] = [
      '#markup' => '<div id="package-requirements-container" class="ssa-app package-loading-overlay"></div>',
    ];

    /**
     * RHS package contents form elements.
     */
    $form_contents = [
      '#type' => 'details',
      '#title' => t('Package contents'),
      '#weight' => -98,
      '#attributes' => [
        'class' => ['package-content'],
      ],
      '#open' => 'panel-open',
      '#summary_attributes' => ['data-ssa-help-link' => \Drupal::service('cohesion.support_url')->getSupportUrlPrefix() . 'package-edit-contents'],
    ];

    $form_contents['package_contents_container'] = [
      '#markup' => '<div id="package-contents-container" class="ssa-app package-loading-overlay"></div>',
    ];

    // LHS / RHS columns.
    $form['row'] = [
      '#prefix' => '<div class="flex-row">',
      '#suffix' => '</div>',
      'requirements' => $form_requirements,
      'content' => $form_contents,
      'package_settings' => [
        '#type' => 'hidden',
      ],
      'excluded_settings' => [
        '#type' => 'hidden',
      ],
    ];

    // Apply Angular styling to this form.
    $form['#attributes']['class'][] = 'coh-form';

    // Set default values for the app to load in.
    $form['#attached']['drupalSettings']['syncPackageForm']['excludedSettings'] = $this->entity->getExcludedEntityTypes();
    $form['#attached']['drupalSettings']['syncPackageForm']['packageSettings'] = $this->entity->getSettings();
    $form['#attached']['drupalSettings']['cohesion']['formGroup'] = 'cohesion_sync';
    $form['#attached']['drupalSettings']['cohesion']['formId'] = 'packages';

    return $form;
  }

  /**
   * Render the RHS package contents AJAX form.
   *
   * @param $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  public function contentsFormCallback($form, FormStateInterface $form_state) {
    return $form['row']['content']['wrapper'];
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);

    $actions['submit']['#value'] = $this->t('Save package');

    return $actions;
  }

  /**
   * Is the supplied machine name already in use?
   *
   * @param $value
   *
   * @return bool
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function checkUniqueMachineName($value) {
    if ($this->entityTypeManager->getStorage('cohesion_sync_package')->load($this->entity->getEntityMachineNamePrefix() . $value)) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   *
   * Called from submit() - see parent.
   */
  public function buildEntity(array $form, FormStateInterface $form_state) {
    /**
     * @var \Drupal\cohesion_sync\Entity\Package $entity
     */
    $entity = clone $this->entity;

    // Meta.
    $entity->set('id', $entity->getEntityMachineNamePrefix() . $form_state->getValue('machine_name'));
    $entity->set('label', $form_state->getValue('label'));
    $entity->set('description', $form_state->getValue('description'));

    // Excluded entity types.
    $entity->setExcludedEntityTypes(json_decode($form_state->getValue('excluded_settings'), TRUE));

    // Package contents.
    $entity->setSettings(json_decode($form_state->getValue('package_settings'), TRUE));

    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state, $redirect = NULL) {
    // Set entity id from supplied machine name.
    // Save it and get the status.
    $status = parent::save($form, $form_state);

    // Show status message.
    $message = $this->t('@verb the @type %label.', [
      '@verb' => ($status == SAVED_NEW) ? 'Created' : 'Saved',
      '@type' => $this->entity->getEntityType()->getSingularLabel(),
      '%label' => $this->entity->label(),
    ]);
    \Drupal::messenger()->addMessage($message);

    // Redirect to the entity collection page.
    $form_state->setRedirectUrl($this->entity->toUrl('collection'));
    return $status;
  }

}
