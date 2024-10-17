<?php

namespace Drupal\cohesion_elements\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Deprecated.
 *
 * Plugin implementation of the 'cohesion_layout' field type.
 *
 * @FieldType(
 *   id = "cohesion_layout",
 *   label = @Translation("Deprecated - Layout canvas"),
 *   description = @Translation("A layout builder for creating content in a
 *   visual, modular way."),
 *   default_widget = "cohesion_layout_builder_widget",
 *   no_ui = TRUE,
 *   default_formatter = "cohesion_layout_formatter",
 *   category = "Site Studio",
 *   module = "cohesion",
 * )
 */
class CohesionLayout extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  protected static $propertyDefinitions;

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'json_values' => [
          'type' => 'text',
          'size' => 'big',
          'not null' => FALSE,
        ],
        'styles' => [
          'type' => 'text',
          'size' => 'big',
          'not null' => FALSE,
        ],
        'template' => [
          'type' => 'text',
          'size' => 'big',
          'not null' => FALSE,
        ],
      ],
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {

    $properties['json_values'] = DataDefinition::create('string')
      ->setLabel(t('Values'));

    $properties['styles'] = DataDefinition::create('string')
      ->setLabel(t('Styles'));

    $properties['template'] = DataDefinition::create('string')
      ->setLabel(t('Template'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $values['json_values'] = '{}';
    $values['styles'] = '';
    $values['template'] = '/* */';
    return $values;
  }

}
