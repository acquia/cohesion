<?php

namespace Drupal\cohesion_elements;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\RouteObjectInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ComponentContentListBuilder.
 *
 * Provides a listing of Site Studio custom styles entities.
 *
 * @package Drupal\cohesion_elements
 */
class ComponentContentListBuilder extends EntityListBuilder {

  /**
   * Constructs a new ComponentContentListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   *   The date formatter service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Renderer service.
   */
  public function __construct(
    EntityTypeInterface $entity_type,
    EntityStorageInterface $storage,
    protected DateFormatterInterface $dateFormatter,
    protected RendererInterface $renderer,
  ) {
    parent::__construct($entity_type, $storage);
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new self(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('date.formatter'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getTitle() {
    $request = \Drupal::request();
    if ($route = $request->attributes->get(RouteObjectInterface::ROUTE_OBJECT)) {
      $route->setDefault('_title', 'Component content');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    // Enable language column and filter if multiple languages are added.
    $header = [
      'title' => $this->t('Title'),
      'author' => [
        'data' => $this->t('Author'),
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
      'status' => [
        'data' => $this->t('Status'),
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
      'changed' => [
        'data' => $this->t('Updated'),
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ],
    ];
    if (\Drupal::languageManager()->isMultilingual()) {
      $header['language_name'] = [
        'data' => $this->t('Language'),
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ];
    }
    $header['inuse'] = [
      'data' => $this->t('In use'),
      'class' => [RESPONSIVE_PRIORITY_LOW],
    ];
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\cohesion_elements\ComponentContentInterface $entity */
    $langcode = $entity->language()->getId();
    $row['title'] = $entity->label();
    $row['author']['data'] = [
      '#theme' => 'username',
      '#account' => $entity->getOwner(),
    ];

    $row['status'] = $entity->isPublished() ? $this->t('published') : $this->t('unpublished');
    if (\Drupal::service('module_handler')->moduleExists('content_moderation')) {
      /** @var \Drupal\content_moderation\ModerationInformation $moderation_information */
      $moderation_information = \Drupal::service('content_moderation.moderation_information');
      if ($moderation_information->isModeratedEntity($entity)) {

        if (!$entity->isLatestRevision()) {
          /** @var \Drupal\Core\Entity\RevisionableStorageInterface $storage */
          $storage = \Drupal::entityTypeManager()->getStorage($entity->getEntityTypeId());
          $latest_revision = $storage->loadRevision($storage->getLatestRevisionId($entity->id()));

          $row['status'] = $latest_revision->moderation_state->value;
        }
        else {
          $row['status'] = $entity->moderation_state->value;
        }

      }
    }

    $row['changed'] = $this->dateFormatter->format($entity->getChangedTime(), 'short');
    $language_manager = \Drupal::languageManager();
    if ($language_manager->isMultilingual()) {
      $row['language_name'] = $language_manager->getLanguageName($langcode);
    }
    $row['in_use'] = $this->getInUseMarkup($entity);
    $row['operations']['data'] = $this->buildOperations($entity);
    return $row + parent::buildRow($entity);
  }

  /**
   * Get the markup for in use.
   *
   * @param \Drupal\cohesion_elements\Entity\ComponentContent $entity
   *
   * @return mixed|null
   *
   * @throws \Drupal\Core\Entity\EntitymalformedException
   */
  private function getInUseMarkup($entity) {
    if ($entity->hasInUse()) {
      $markup = [
        '#type' => 'link',
        '#title' => t('In use'),
        '#url' => $entity->toUrl('in-use'),
        '#options' => [
          'attributes' => [
            'class' => ['use-ajax'],
            'data-dialog-type' => 'modal',
            'data-dialog-options' => Json::encode([
              'width' => 700,
            ]),
          ],
        ],
        '#attached' => ['library' => ['core/drupal.dialog.ajax']],
      ];
    }
    else {
      $markup = [
        '#markup' => t('Not in use'),
      ];
    }

    return $this->renderer->render($markup);
  }

}
