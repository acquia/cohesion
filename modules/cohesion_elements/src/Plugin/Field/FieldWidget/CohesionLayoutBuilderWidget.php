<?php

namespace Drupal\cohesion_elements\Plugin\Field\FieldWidget;

use Drupal\cohesion\Services\JsonXss;
use Drupal\cohesion_elements\Entity\CohesionLayout;
use Drupal\cohesion_elements\Entity\ComponentContent;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\token\TokenEntityMapperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'cohesion_layout_builder_widget' widget.
 *
 * @FieldWidget(
 *   id = "cohesion_layout_builder_widget",
 *   label = @Translation("Site Studio layout canvas"),
 *   field_types = {
 *     "entity_reference_revisions",
 *     "cohesion_entity_reference_revisions",
 *   },
 *   multiple_values = FALSE
 * )
 */
class CohesionLayoutBuilderWidget extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * Indicates whether the current widget instance is in translation.
   *
   * @var bool
   */
  private $isTranslating;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface*/
  protected $entityTypeManager;

  /**
   * @var \Drupal\token\TokenEntityMapperInterface*/
  protected $tokenEntityMapper;

  /**
   * @var \Drupal\cohesion\Services\JsonXss*/
  protected $jsonXss;

  /**
   * @var array*/
  protected $xss_paths;

  /**
   * @inheritdoc
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, EntityTypeManagerInterface $entity_type_manager, TokenEntityMapperInterface $token_entity_mapper, JsonXss $json_xss) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->entityTypeManager = $entity_type_manager;
    $this->tokenEntityMapper = $token_entity_mapper;
    $this->jsonXss = $json_xss;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('entity_type.manager'),
      $container->get('token.entity_mapper'),
      $container->get('cohesion.xss')
    );
  }

  /**
   * Initialise the Angular form to show the layout builder.
   *
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $values = $items->getValue();
    $target_type = $this->getFieldSetting('target_type');
    /** @var \Drupal\Core\Entity\RevisionableStorageInterface $entity_storage */
    $entity_storage = $this->entityTypeManager->getStorage($target_type);
    /** @var \Drupal\cohesion_elements\Entity\CohesionLayout $layout_entity */
    if (empty($values[$delta]['target_id'])) {
      $layout_entity = $entity_storage->create();
    }
    else {
      // If the form loads after node preview, there is already an entity
      // attached with potentially modified values. Use that if possible, so we
      // don't lose work in progress.
      if (isset($values[$delta]['entity']) && $values[$delta]['entity'] instanceof CohesionLayout) {
        $layout_entity = $values[$delta]['entity'];
      }
      // A hook_entity_prepare_form() implementation can result in
      // $items[$delta]->entity being different from what's saved in the
      // database, so use that if possible. For example, Content Moderation
      // creates an entity object that merges the draft revision of the
      // active translation with the published revisions of other languages.
      // @see \Drupal\Core\Entity\TranslatableRevisionableStorageInterface::createRevision()
      elseif (isset($items[$delta]->entity) && $items[$delta]->entity instanceof CohesionLayout) {
        $layout_entity = $items[$delta]->entity;
      }
      // Fall back to loading the entity revision from the database.
      else {
        $layout_entity = $entity_storage->loadRevision($values[$delta]['target_revision_id']);
      }
    }

    $storage = $form_state->getStorage();
    if (isset($storage['form_display'])) {
      $entity_type_target = $storage['form_display']->getTargetEntityTypeId();
      $bundle_target = $storage['form_display']->getTargetBundle();
      $form['#attached']['drupalSettings']['cohesion']['restrictedComponents'] = [
        'entity_type_access' => $entity_type_target,
        'bundle_access' => $bundle_target,
      ];
    }

    // Set list of field to blank by default. Template form that inherit from
    // this one will override the variable.
    $language_none = \Drupal::languageManager()
      ->getLanguage(LanguageInterface::LANGCODE_NOT_APPLICABLE);

    $form['#attached']['drupalSettings']['cohesion']['contextualKey'] = Url::fromRoute('cohesion.entity_fields', [
      'entity_type' => '__none__',
      'entity_bundle' => '__none__',
    ], ['language' => $language_none])->toString();

    // Add the shared attachments.
    _cohesion_shared_page_attachments($form);

    // Override the 'access elements' permission depending on the field
    // settings.
    if ($this->getFieldSetting('access_elements') !== "1") {
      $form['#attached']['drupalSettings']['cohesion']['permissions'] = array_values(array_diff($form['#attached']['drupalSettings']['cohesion']['permissions'], ['access elements']));
    }

    // Build the form element.
    $host = $items->getEntity();
    $form['#attached']['drupalSettings']['cohesion']['entityTypeId'] = $host->getEntityTypeId();
    $form['#attached']['drupalSettings']['cohesion']['entityId'] = $host->id();

    $element['target_id'] = ['#type' => 'cohesionfield'];
    $element['target_revision_id'] = [
      '#type' => 'value',
      '#value' => NULL,
    ];
    $element['#element_validate'] = [
      [
        $this,
        'validateElement',
      ],
    ];

    $this->initIsTranslating($form_state, $host);
    $langcode = $form_state->get('langcode');
    if (!$this->isTranslating) {
      // Set the langcode if we are not translating.
      $langcode_key = $layout_entity->getEntityType()->getKey('langcode');
      if ($layout_entity->get($langcode_key)->value != $langcode) {
        // If a translation in the given language already exists, switch to
        // that. If there is none yet, update the language.
        if ($layout_entity->hasTranslation($langcode)) {
          $layout_entity = $layout_entity->getTranslation($langcode);
        }
        else {
          $layout_entity->set($langcode_key, $langcode);
        }
      }
    }
    else {
      // Add translation if missing for the target language.
      if (!$layout_entity->hasTranslation($langcode)) {
        // Get the selected translation of the paragraph entity.
        $entity_langcode = $layout_entity->language()->getId();
        $source = $form_state->get(['content_translation', 'source']);
        $source_langcode = $source ? $source->getId() : $entity_langcode;
        // Make sure the source language version is used if available. It is a
        // valid scenario to have no paragraphs items in the source version of
        // the host and fetching the translation without this check could lead
        // to an exception.
        if ($layout_entity->hasTranslation($source_langcode)) {
          $layout_entity = $layout_entity->getTranslation($source_langcode);
        }
        // The paragraphs entity has no content translation source field if
        // no paragraph entity field is translatable, even if the host is.
        if ($layout_entity->hasField('content_translation_source')) {
          // Initialise the translation with source language values.
          $layout_entity->addTranslation($langcode, $layout_entity->toArray());
          $translation = $layout_entity->getTranslation($langcode);
          $manager = \Drupal::service('content_translation.manager');
          $manager->getTranslationMetadata($translation)
            ->setSource($layout_entity->language()->getId());
        }
      }
      // If any paragraphs type is translatable do not switch.
      if ($layout_entity->hasField('content_translation_source')) {
        // Switch the paragraph to the translation.
        $layout_entity = $layout_entity->getTranslation($langcode);
      }
    }

    $cohFormGroupId = 'node_layout';
    if ($host instanceof ComponentContent) {
      $cohFormGroupId = 'component_content';
    }
    $element['target_id']['#canvas_name'] = $items->getName() . '_' . $delta;

    $element['target_id'] += [
      '#json_values' => ((!is_null($layout_entity->json_values->value)) && (mb_strlen($layout_entity->json_values->value))) ? $layout_entity->json_values->value : '{}',
      '#styles' => ((!is_null($layout_entity->styles->value)) && (mb_strlen($layout_entity->styles->value))) ? $layout_entity->styles->value : '/* */',
      '#template' => $layout_entity->template->value,
      '#entity' => $layout_entity,
      '#cohFormGroup' => $cohFormGroupId,
      '#cohFormId' => $cohFormGroupId,
      '#title' => $items->getDataDefinition()->getLabel(),
      '#required' => $items->getDataDefinition()->isRequired(),
      '#token_browser' => $this->tokenEntityMapper->getTokenTypeForEntityType($host->getEntityTypeId(), ''),
      '#isContentEntity' => $layout_entity instanceof ContentEntityInterface,
    ];

    // Stash the Xss paths for this entity.
    if (!$this->jsonXss->userCanBypass()) {
      // Stash this so it can be compares inside validateForm.
      $this->xss_paths = $this->jsonXss->buildXssPaths($element['target_id']['#json_values']);

      // Let the app know which form elements to disable.
      $form['#attached']['drupalSettings']['cohesion']['xss_paths'] = $this->xss_paths;
    }

    return $element;
  }

  /**
   * Initializes the translation form state.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param \Drupal\Core\Entity\EntityInterface $host
   */
  protected function initIsTranslating(FormStateInterface $form_state, EntityInterface $host) {
    if ($this->isTranslating != NULL) {
      return;
    }
    $this->isTranslating = FALSE;
    if (!$host->isTranslatable()) {
      return;
    }
    if (!$host->getEntityType()->hasKey('default_langcode')) {
      return;
    }
    $default_langcode_key = $host->getEntityType()->getKey('default_langcode');
    if (!$host->hasField($default_langcode_key)) {
      return;
    }

    if (!empty($form_state->get('content_translation'))) {
      // Adding a language through the ContentTranslationController.
      $this->isTranslating = TRUE;
    }
    if ($host->hasTranslation($form_state->get('langcode')) && $host->getTranslation($form_state->get('langcode'))
      ->get($default_langcode_key)->value == 0) {
      // Editing a translation.
      $this->isTranslating = TRUE;
    }
  }

  /**
   * ??????
   *
   * @param array $element
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param array $complete_form
   */
  public function validateElement(array &$element, FormStateInterface $form_state, array &$complete_form) {
    /** @var \Drupal\cohesion_elements\Entity\CohesionLayout $entity */
    $entity = &$element['target_id']['#entity'];

    $value = [
      'entity' => $entity,
    ];

    $parents = $element['target_id']['#parents'];
    $parents[] = 'json_values';
    $json = $form_state->getValue($parents);
    $decoded_json = Json::decode($json);
    if ($this->fieldDefinition->isRequired() && (!isset($decoded_json['canvas']) || empty($decoded_json['canvas']))) {
      $form_state->setError($element, $this->t('@field_name is required', ['@field_name' => $this->fieldDefinition->getLabel()]));
    }

    // Xss validation.
    if (!$this->jsonXss->userCanBypass()) {
      foreach ($this->jsonXss->buildXssPaths($json) as $path => $new_value) {
        // Only test if the user changed the value or it's a new value. If it's
        // the same, no need to test.
        if (!isset($this->xss_paths[$path]) || $this->xss_paths[$path] !== $new_value) {
          $form_state->setError($element, $this->t('You do not have permission to add tags and attributes that fail XSS validation.'));
        }
      }
    }

    $value += [
      'json_values' => $json,
    ];

    if (!$entity->isNew()) {
      $value += [
        'target_id' => $entity->id(),
        'target_revision_id' => $entity->getRevisionId(),
      ];
    }

    $form_state->setValueForElement($element, $value);
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {

    foreach ($values as $delta => &$item) {
      if (isset($item['entity'])) {
        /** @var \Drupal\cohesion_elements\Entity\CohesionLayout $entity */
        $entity = $item['entity'];

        $langcode_key = $entity->getEntityType()->getKey('langcode');
        if ($entity->get($langcode_key)->value != $form_state->get('langcode')) {
          // If a translation in the given language already exists, switch to
          // that. If there is none yet, update the language.
          if ($entity->hasTranslation($form_state->get('langcode'))) {
            $entity = $entity->getTranslation($form_state->get('langcode'));
          }
          else {
            $entity->set($langcode_key, $form_state->get('langcode'));
          }
        }

        $entity->setJsonValue($item['json_values']);
        $entity->setNeedsSave(TRUE);

        $triggering_element = $form_state->getTriggeringElement();
        if ($triggering_element && isset($triggering_element['#parents'])) {

          $op = end($triggering_element['#parents']);

          if ($op == 'preview') {
            /** @var \Drupal\cohesion\Plugin\Api\TemplatesApi $send_to_api */
            $send_to_api = $entity->apiProcessorManager()->createInstance('templates_api');
            $send_to_api->isPreview(TRUE);
            $send_to_api->setEntity($entity);
            $send_to_api->setJsonValues($item['json_values']);
            $send_to_api->sendWithoutSave();

            $entity->processApiResponse($send_to_api->getData());
          }
        }

        $item['entity'] = $entity;
      }
    }
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function extractFormValues(FieldItemListInterface $items, array $form, FormStateInterface $form_state) {
    // Filter possible empty items.
    $items->filterEmptyItems();
    return parent::extractFormValues($items, $form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function form(FieldItemListInterface $items, array &$form, FormStateInterface $form_state, $get_delta = NULL) {
    $parents = $form['#parents'];

    // Identify the manage field settings default value form.
    if (in_array('default_value_input', $parents, TRUE)) {
      // Since the entity is not reusable neither cloneable, having a default
      // value is not supported.
      return [
        '#markup' => $this->t('No widget available for: %label.', [
          '%label' => $items->getFieldDefinition()
            ->getLabel(),
        ]),
      ];
    }

    $elements = parent::form($items, $form, $form_state, $get_delta);
    // Signal to content_translation that this field should be treated as
    // multilingual and not be hidden, see
    // \Drupal\content_translation\ContentTranslationHandler::entityFormSharedElements().
    $elements['#multilingual'] = TRUE;
    return $elements;
  }

}
