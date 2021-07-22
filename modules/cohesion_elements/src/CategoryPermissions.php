<?php

namespace Drupal\cohesion_elements;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class DynamicPermissions
 * Provides dynamic permissions for categories.
 *
 * @package Drupal\cohesion_elements
 */
class CategoryPermissions implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Creates a ProductMenuLink instance.
   *
   * @param $base_plugin_id
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity_type.manager'));
  }

  /**
   * Returns an array of category permissions.
   *
   * @return array
   * @see \Drupal\user\PermissionHandlerInterface::getPermissions()
   */
  public function getPermissions() {
    $permissions = [];

    $category_entity_types = [
      'cohesion_component_category' => 'components',
      'cohesion_helper_category' => 'helpers',
    ];

    foreach ($category_entity_types as $entity_type_id => $type_label) {
      // Get the storage for this category entity type.
      try {
        $storage = $this->entityTypeManager->getStorage($entity_type_id);
      }
      catch (\Throwable $e) {
        continue;
      }

      // Loop through the entities and and the categories.
      foreach ($storage->loadMultiple() as $entity) {
        $permissions += [
          'access ' . $entity->id() . ' ' . $entity_type_id . ' group' => [
            'title' => $this->t('Site Studio Components - @label @type_label category group', ['@label' => $entity->label(), '@type_label' => $type_label]),
            'description' => $this->t('Grant access to the Site Studio @label @type_label category group.', ['@label' => $entity->label(), '@type_label' => $type_label]),
          ],
        ];
      }
    }

    return $permissions;
  }

}
