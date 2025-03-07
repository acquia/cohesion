<?php

namespace Drupal\cohesion_elements\Form;

use Drupal\cohesion_elements\Entity\Component;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ComponentContentForm.
 *
 * Form handler for the node edit forms.
 *
 * @package Drupal\cohesion_elements\Form
 */
class ComponentContentForm extends ContentEntityForm {

  /**
   * The Current User object.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * ComponentContentForm constructor.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface|null $entity_type_bundle_info
   * @param \Drupal\Component\Datetime\TimeInterface|null $time
   * @param \Drupal\Core\Session\AccountInterface $current_user
   */
  public function __construct(EntityRepositoryInterface $entity_repository, EntityTypeBundleInfoInterface $entity_type_bundle_info, TimeInterface $time, AccountInterface $current_user) {
    parent::__construct($entity_repository, $entity_type_bundle_info, $time);
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('entity.repository'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    /** @var \Drupal\cohesion_elements\ComponentContentInterface $component_content */
    $component_content = $this->entity;

    if ($this->operation == 'edit') {
      $form['#title'] = $this->t('<em>Edit</em> @title', [
        '@title' => $component_content->label(),
      ]);
    }

    $component_id = NULL;
    if ($this->operation == 'add') {
      // If component content add form attach the component ID from the request
      // for angular.
      $request = \Drupal::request();
      $component_id = $request->attributes->get('cohesion_component');

      // Attach the component id to add if coming from the add page
      $form['#attached']['drupalSettings']['cohesion']['component_content_add'] = $component_id;

    }

    // Case for translation (operation add but not component id request
    if ($component_id == NULL && $component_content->getComponent() instanceof Component) {
      $component_id = $component_content->getComponent()->id();
    }

    // If the component_id is still null & we're editing get the current value.
    // This can be null still if a custom component.
    if ($component_id == NULL && $this->operation === 'edit') {
      $component_id = $this->entity->get('component')->getString();
    }

    $form['component_id'] = [
      '#type' => 'hidden',
      '#default_value' => $component_id,
    ];

    // Changed must be sent to the client, for later overwrite error checking.
    $form['changed'] = [
      '#type' => 'hidden',
      '#default_value' => $component_content->getChangedTime(),
    ];

    $form = parent::form($form, $form_state);
    $form['advanced']['#attributes']['class'][] = 'entity-meta';

    $form['meta'] = [
      '#type' => 'details',
      '#group' => 'advanced',
      '#weight' => -10,
      '#title' => $this->t('Status'),
      '#attributes' => ['class' => ['entity-meta__header']],
      '#tree' => TRUE,
      '#access' => $this->currentUser->hasPermission('administer component content'),
    ];
    $form['meta']['published'] = [
      '#type' => 'item',
      '#markup' => $component_content->isPublished() ? $this->t('Published') : $this->t('Not published'),
      '#access' => !$component_content->isNew(),
      '#wrapper_attributes' => ['class' => ['entity-meta__title']],
    ];
    $form['meta']['changed'] = [
      '#type' => 'item',
      '#title' => $this->t('Last saved'),
      '#markup' => !$component_content->isNew() ? \Drupal::service('date.formatter')->format($component_content->getChangedTime(), 'short') : $this->t('Not saved yet'),
      '#wrapper_attributes' => ['class' => ['entity-meta__last-saved']],
    ];
    $form['meta']['author'] = [
      '#type' => 'item',
      '#title' => $this->t('Author'),
      '#markup' => $component_content->getOwner() ? $component_content->getOwner()->getDisplayName() : \Drupal::config('user.settings')->get('anonymous'),
      '#wrapper_attributes' => ['class' => ['entity-meta__author']],
    ];

    $form['status']['#group'] = 'footer';

    // Node author information for administrators.
    $form['author'] = [
      '#type' => 'details',
      '#title' => t('Authoring information'),
      '#group' => 'advanced',
      '#attributes' => [
        'class' => ['node-form-author'],
      ],
      '#attached' => [
        'library' => ['node/drupal.node'],
      ],
      '#weight' => 90,
      '#optional' => TRUE,
    ];

    if (isset($form['uid'])) {
      $form['uid']['#group'] = 'author';
    }

    if (isset($form['created'])) {
      $form['created']['#group'] = 'author';
    }

    $form['#attached']['library'][] = 'node/form';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $element = parent::actions($form, $form_state);
    $component_content = $this->entity;

    $element['delete']['#access'] = $component_content->access('delete');
    $element['delete']['#weight'] = 100;

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $layout_canvas_value = $form_state->getValue('layout_canvas');
    $title_value = $form_state->getValue('title');
    if (isset($layout_canvas_value[0]) && isset($title_value[0])) {
      $json_values = Json::decode($layout_canvas_value[0]['json_values']);
      $json_values['canvas'][0]['model']['settings']['title'] = $title_value[0]['value'];
      $layout_canvas_value[0]['json_values'] = Json::encode($json_values);
      $form_state->setValue('layout_canvas', $layout_canvas_value);
    }
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->entity->set('component', $form_state->getValue('component_id'));
    $status = parent::save($form, $form_state);
    if ($this->entity->id() && $this->entity->access('view')) {
      $form_state->setRedirect(
        'entity.component_content.canonical',
        ['component_content' => $this->entity->id()]
      );
    }
    $context = [
      '@type' => $this->entity->getEntityType()->getLabel(),
      '%title' => $this->entity->label(),
    ];
    if ($this->entity->isNew() || $this->operation == 'add') {
      $this->logger('content')->notice('@type: added %title.', $context);
      \Drupal::messenger()->addMessage(t('@type %title has been created.', $context));
    }
    else {
      $this->logger('content')->notice('@type: updated %title.', $context);
      \Drupal::messenger()->addMessage($this->t('@type %title has been updated.', $context));
    }
    return $status;
  }

}
