<?php

namespace Drupal\cohesion_website_settings\Form;

use Drupal\cohesion\EntityGroupsPluginManager;
use Drupal\cohesion\Services\RebuildInuseBatch;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class WebsiteSettingsGroupFormBase.
 *
 * The base for form the color, icon library and font stack bulk edit form.
 *
 * @package Drupal\cohesion_website_settings\Form
 */
abstract class WebsiteSettingsGroupFormBase extends ConfigFormBase {

  const ENTITY_TYPE = NULL;

  const FORM_TITLE = NULL;

  const FORM_ID = NULL;

  const FORM_CLASS = NULL;

  const COH_FROM_ID = NULL;

  /**
   * The entity type definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface|null
   */
  protected $entity_type_definition;

  /**
   * Holds the storage manager for the entity.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * The instance of the entity groups plugin.
   *
   * @var \Drupal\cohesion\EntityGroupsPluginInterface
   */
  protected $entityGroupsPlugin;

  /**
   * The instance of the entity group plugin manager.
   *
   * @var \Drupal\cohesion\EntityGroupsPluginManager
   */
  protected $entityGroupsManager;

  /**
   * The instance of the rebuild in use batch service.
   *
   * @var \Drupal\cohesion\Services\RebuildInuseBatch
   */
  protected $rebuildInUseBatch;

  /**
   * @var int
   */
  protected $step = 1;

  /**
   * Holds data between form steps.
   *
   * @var array
   */
  protected $in_use_list;

  /**
   * Holds data between form steps.
   *
   * @var array
   */
  protected $changed_entities;

  /**
   * WebsiteSettingsGroupFormBase constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\cohesion\EntityGroupsPluginManager $entity_groups_manager
   * @param \Drupal\cohesion\Services\RebuildInuseBatch $rebuild_inuse_batch
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typed_configmanager
   *   The typed config manager.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, EntityGroupsPluginManager $entity_groups_manager, RebuildInuseBatch $rebuild_inuse_batch, TypedConfigManagerInterface $typed_configmanager) {
    parent::__construct($config_factory, $typed_configmanager);
    $this->storage = $entity_type_manager->getStorage(get_class($this)::ENTITY_TYPE);
    $this->entityGroupsManager = $entity_groups_manager;
    $this->rebuildInUseBatch = $rebuild_inuse_batch;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.entity_groups.processor'),
      $container->get('cohesion.rebuild_inuse_batch'),
      $container->get('config.typed')
    );
  }

  /**
   * Return an instance of the entity groups plugin. This is done dynamically
   * because the form can clear this value to avoid serialization problems when
   * switching between form steps.
   *
   * @return \Drupal\cohesion\EntityGroupsPluginInterface
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function getEntityGroupsPlugin() {
    if (!$this->entityGroupsPlugin) {
      // Create an instance of the entity groups plugin to use later on.
      $this->entityGroupsPlugin = $this->entityGroupsManager->createInstance(get_class($this)::PLUGIN_ID);
    }

    return $this->entityGroupsPlugin;
  }

  /**
   * Get the title based on the type of entity passed in.
   *
   * @return string
   */
  public function getTitle() {
    return t('Edit <i>@class_title</i>', ['@class_title' => get_class($this)::FORM_TITLE]);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'cohesion.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return get_class($this)::FORM_ID;
  }

  /**
   * Build the JSON string for the main json_values textarea.
   *
   * @return string
   */
  protected function buildJsonValues() {
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    if ($this->step == 1) {
      // Tell the app this is a website setting (mostly for styling).
      $form['#attributes']['class'][] = 'cohesion-website-settings-form';

      // The app.
      $form['cohesion'] = [
        // Drupal\cohesion\Element\CohesionField.
        '#type' => 'cohesionfield',
        '#json_values' => empty($form_state->getUserInput()) ? $this->getEntityGroupsPlugin()->getGroupJsonValues() : $form_state->getUserInput()['json_values'],
        '#json_mapper' => '{}',
        '#entity' => NULL,
        '#classes' => [
          'cohesion-website-settings-edit-form',
          get_class($this)::FORM_CLASS,
        ],
        '#cohFormGroup' => 'website_settings',
        '#cohFormId' => get_class($this)::COH_FROM_ID,
        '#isContentEntity' => FALSE,
      ];

      // Change save button text.
      $form['actions']['submit']['#value'] = t('Save');
    }
    else {
      // Set page title and warning (base_unit_settings only).
      $form['#title'] = $this->t('Are you sure you want to update the website settings?');

      $form['markup'] = [
        '#markup' => t('You are about to change core website settings. This will rebuild styles and templates and flush the render cache.'),
      ];

      // Change save button text.
      $form['actions']['submit']['#value'] = t('Rebuild');
      $form['actions']['submit']['#type_value'] = 'rebuild';

      // Add cancel button.
      $form['actions']['cancel'] = $form['actions']['submit'];
      $form['actions']['cancel']['#value'] = t('Cancel');
      $form['actions']['cancel']['#type_value'] = 'cancel';
      $form['actions']['cancel']['#button_type'] = 'secondary';
      $form['actions']['cancel']['#access'] = TRUE;
      $form['actions']['cancel']['#weight'] = 10;
    }

    // Add the shared attachments.
    _cohesion_shared_page_attachments($form);

    return $form;
  }

  /**
   * Submit handler.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  protected function stepOneSubmit(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    if ($this->step == 1) {
      $this->stepOneSubmit($form, $form_state);
    }
    elseif ($this->step > 1) {
      $triggering_element = $form_state->getTriggeringElement();

      // Cancel button.
      if ($triggering_element['#type_value'] == 'cancel') {
        // Just back to the list.
        $this->step = 1;
        return;
      }
      // Rebuild button.
      elseif ($triggering_element['#type_value'] == 'rebuild') {
        $this->rebuildInUseBatch->run($this->in_use_list, $this->changed_entities);
      }
    }
  }

}
