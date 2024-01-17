<?php

namespace Drupal\cohesion_elements;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class CategoryRelationshipsManager.
 *
 * Handles resetting components and helpers with no category to a new
 * "Uncategorized" category.
 *
 * @package Drupal\cohesion_elements
 */
class CategoryRelationshipsManager {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * CategoryRelationshipsManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * If the supplied category doesn't exist, scan for components / helpers
   * that used it and convert them to use "Uncategorized" instead.
   *
   * @param $category_id
   * @param $category_entity_type_id
   * @param $category_entity_class
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function processCategory($category_id, $category_entity_type_id, $category_entity_class) {
    $element_entity_type = $category_entity_class::TARGET_ENTITY_TYPE;
    $default_category_id = $category_entity_class::DEFAULT_CATEGORY_ID;

    // Setup.
    $category_storage = $this->entityTypeManager->getStorage($category_entity_type_id);
    $element_storage = $this->entityTypeManager->getStorage($element_entity_type);

    // Is the category missing?
    if (empty($category_storage->load($category_id)) && !empty($category_storage->loadMultiple()) || empty($category_storage->loadMultiple())) {

      // Is this category in use anywhere?
      $query = $element_storage->getQuery()
        ->accessCheck(TRUE)
        ->condition('category', $category_id, '=');

      if ($entity_ids = $query->execute()) {
        $this->createUncategorized($category_storage, $default_category_id);

        // Set all the elements to use this new category.
        foreach ($element_storage->loadMultiple($entity_ids) as $element_entity) {
          $element_entity->setCategory($default_category_id);
          $element_entity->save();
        }
      }
    }
  }

  /**
   * If uncategorized doesn't exist create it.
   *
   * @param $category_storage
   * @param $default_category_id
   */
  public function createUncategorized($category_storage, $default_category_id) {
    // Does the uncategorized category exist?
    // Create the uncategorized category.
    if (!$category_storage->load($default_category_id)) {
      $uncategorized = $category_storage->create([
        'id' => $default_category_id,
        'label' => t('Uncategorized'),
        'class' => 'category-1',
        'weight' => 999,
      ]);

      $uncategorized->save();
    }
  }

}
