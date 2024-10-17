<?php

namespace Drupal\cohesion_elements;

use Drupal\cohesion\ExceptionLoggerTrait;
use Drupal\cohesion_elements\Entity\ElementCategoryBase;
use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\BundlePermissionHandlerTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides dynamic permissions for categories.
 *
 * @package Drupal\cohesion_elements
 */
class CategoryPermissions implements ContainerInjectionInterface {

  use StringTranslationTrait;
  use BundlePermissionHandlerTrait;
  use ExceptionLoggerTrait;

  const CATEGORY_ENTITY_TYPES = [
    'cohesion_component_category' => 'components',
    'cohesion_helper_category' => 'helpers',
  ];

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $loggerChannel;

  /**
   * Creates Category Permissions instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity Type Manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   Logger Channel Factory.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannelFactoryInterface $loggerChannelFactory,
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->loggerChannel = $loggerChannelFactory->get('cohesion_elements.category_permissions');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('entity_type.manager'),
      $container->get('logger.factory')
    );
  }

  /**
   * Returns an array of category permissions.
   *
   * @return array
   *   Permissions array.
   * @see \Drupal\user\PermissionHandlerInterface::getPermissions()
   */
  public function getPermissions() {
    $permissions = [];

    foreach (self::CATEGORY_ENTITY_TYPES as $entity_type_id => $type_label) {
      try {
        $storage = $this->entityTypeManager->getStorage($entity_type_id);
      }
      catch (PluginException $exception) {
        $this->logException($exception, 'cohesion_elements');
        continue;
      }

      $permissions += $this->generatePermissions(
        $storage->loadMultiple(),
        [$this, 'getCategoryPermissions']
      );
    }

    return $permissions;
  }

  /**
   * Builds a category permission array for entity.
   *
   * @param \Drupal\cohesion_elements\Entity\ElementCategoryBase $entity
   *   Element Category entity.
   *
   * @return array
   *   Permission array.
   * @see \Drupal\user\PermissionHandlerInterface::getPermissions()
   */
  protected function getCategoryPermissions(ElementCategoryBase $entity) {
    return [
      'access ' . $entity->id() . ' ' . $entity->getEntityTypeId() . ' group' => [
        'title' => $this->t('Site Studio @type_label - @label group',
          [
            '@label' => $entity->label(),
            '@type_label' => $entity->getEntityType()->getPluralLabel(),
          ]
        ),
        'description' => $this->t('Grant access to Site Studio @type in @label within the sidebar browser on content entities.',
          [
            '@label' => $entity->label(),
            '@type' => self::CATEGORY_ENTITY_TYPES[$entity->getEntityTypeId()],
          ]
        ),
      ],
    ];
  }

}
