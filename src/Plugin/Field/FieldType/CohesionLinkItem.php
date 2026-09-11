<?php

namespace Drupal\cohesion\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\link\Plugin\Field\FieldType\LinkItem;

/**
 * This extends the LinkItem field, adding a 'path' property which is exposed as
 * a token. See cohesion_field_info_alter().
 *
 * @package Drupal\cohesion\Plugin\Field\FieldType
 */
class CohesionLinkItem extends LinkItem {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);

    // Add fake property 'path'.
    $properties['path'] = DataDefinition::create('string')->setLabel(t('Path'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function __get($name) {
    // Return fake property 'path' as a path string.
    if ($name == 'path') {
      return $this->getUrl()->toString();
    }

    return parent::__get($name);
  }

}
