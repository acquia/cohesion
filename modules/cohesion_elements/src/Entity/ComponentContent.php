<?php

namespace Drupal\cohesion_elements\Entity;

use Drupal\cohesion_elements\ComponentContentInterface;
use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\UserInterface;

/**
 * Defines the component content entity class.
 *
 * @ContentEntityType(
 *   id = "component_content",
 *   label = @Translation("Component content"),
 *   label_singular = @Translation("Component content"),
 *   handlers = {
 *     "access" = "Drupal\cohesion_elements\ComponentContentAccessControlHandler",
 *     "list_builder" = "Drupal\cohesion_elements\ComponentContentListBuilder",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "route_provider" = {
 *       "html" = "Drupal\cohesion_elements\Entity\ComponentContentRouteProvider",
 *     },
 *     "form" = {
 *       "default" = "Drupal\cohesion_elements\Form\ComponentContentForm",
 *       "add" = "Drupal\cohesion_elements\Form\ComponentContentForm",
 *       "edit" = "Drupal\cohesion_elements\Form\ComponentContentForm",
 *       "delete" = "Drupal\cohesion_elements\Form\ComponentContentDeleteForm",
 *     },
 *     "translation" = "Drupal\cohesion_elements\ComponentContentTranslationHandler",
 *     "moderation" = "Drupal\content_moderation\Entity\Handler\NodeModerationHandler"
 *   },
 *   admin_permission = "administer component content",
 *   base_table = "component_contents",
 *   data_table = "component_contents_field_data",
 *   revision_table = "component_content_revision",
 *   revision_data_table = "component_content_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "label" = "title",
 *     "langcode" = "langcode",
 *     "uuid" = "uuid",
 *     "status" = "status",
 *     "published" = "status",
 *     "uid" = "uid",
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_uid",
 *     "revision_created" = "revision_timestamp",
 *     "revision_log_message" = "revision_log"
 *   },
 *   links = {
 *     "canonical" = "/admin/cohesion/components/component_contents/{component_content}",
 *     "add-form" = "/admin/cohesion/components/component_contents/add/{cohesion_component}",
 *     "add-page" = "/admin/cohesion/components/component_contents/add",
 *     "edit-form" = "/admin/cohesion/components/component_contents/{component_content}",
 *     "delete-form" = "/admin/cohesion/components/component_contents/{component_content}/delete",
 *     "collection" = "/admin/cohesion/components/component_contents",
 *     "in-use" = "/admin/cohesion/components/component_contents/{component_content}/in_use",
 *   },
 * )
 */
class ComponentContent extends EditorialContentEntityBase implements ComponentContentInterface {

  const CATEGORY_ENTITY_TYPE_ID = 'cohesion_component_category';

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }

    // If no revision author has been set explicitly, make the component content
    // owner the revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preSaveRevision(EntityStorageInterface $storage, \stdClass $record) {
    parent::preSaveRevision($storage, $record);

    if (!$this->isNewRevision() && isset($this->original) && (!isset($record->revision_log) || $record->revision_log === '')) {
      // If we are updating an existing component content without adding a new
      // revision, we need to make sure $entity->revision_log is reset whenever
      // it is empty. Therefore, this code allows us to avoid clobbering an
      // existing log entry with an empty one.
      $record->revision_log = $this->original->revision_log->value;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->get('title')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTitle($title) {
    $this->set('title', $title);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getComponent() {
    return $this->get('component')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->getEntityKey('uid');
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getRevisionAuthor() {
    return $this->getRevisionUser();
  }

  /**
   * {@inheritdoc}
   */
  public function setRevisionAuthorId($uid) {
    $this->setRevisionUserId($uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    /** @var \Drupal\Core\Field\BaseFieldDefinition[] $fields */
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setRevisionable(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ]);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The username of the content author.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback('Drupal\cohesion_elements\Entity\ComponentContent::getCurrentUserId')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => '',
        ],
      ]);

    $fields['status']->setDisplayOptions('form', [
      'type' => 'boolean_checkbox',
      'settings' => [
        'display_label' => TRUE,
      ],
      'weight' => 120,
    ])->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setDescription(t('The time that the component content was created.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 10,
      ]);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the component content was last edited.'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    $fields['layout_canvas'] = BaseFieldDefinition::create('cohesion_entity_reference_revisions')
      ->setLabel(t('Layout canvas'))
      ->setCardinality(1)
      ->setSetting('target_type', 'cohesion_layout')
      ->setRevisionable(TRUE)
      ->setDescription(t("The Site Studio layout canvas associated to this content"))
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'entity_reference_revisions_entity_view',
      ])
      ->setDisplayOptions('form', [
        'type' => 'cohesion_layout_builder_widget',
        'weight' => 1,
      ]);

    $fields['component'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Component'))
      ->setCardinality(1)
      ->setDescription(t('The component config entity it is attached to'))
      ->setSetting('target_type', 'cohesion_component');

    return $fields;
  }

  /**
   * Default value callback for 'uid' base field definition.
   *
   * @return array
   *   An array of default values.
   * @see ::baseFieldDefinitions()
   */
  public static function getCurrentUserId() {
    return [\Drupal::currentUser()->id()];
  }

  /**
   * Does this have an in-use link?
   *
   * @return bool
   */
  public function hasInUse() {
    if ($this->getEntityType()->hasLinkTemplate('in-use')) {
      return \Drupal::service('cohesion_usage.update_manager')->hasInUse($this);
    }
    return FALSE;
  }

  /**
   * @return array
   */
  public function getInUseMessage() {
    return [
      'message' => [
        '#markup' => t('This component content has been tracked as in use in the places listed below. You should not delete it until you have removed its use.'),
      ],
    ];
  }

}
