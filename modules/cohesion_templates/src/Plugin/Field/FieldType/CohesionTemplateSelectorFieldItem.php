<?php

namespace Drupal\cohesion_templates\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Provides a field type of cohesion_template.
 *
 * @FieldType(
 *   id = "cohesion_template_selector",
 *   label = @Translation("Template selector"),
 *   description = @Translation("Template selector"),
 *   category = "Site Studio",
 *   module = "cohesion",
 *   default_formatter = "basic_string",
 *   default_widget = "cohesion_template_selector_widget",
 *   cardinality = 1
 * )
 */
class CohesionTemplateSelectorFieldItem extends FieldItemBase implements FieldItemInterface {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'selected_template' => [
          'type' => 'text',
          'size' => 'tiny',
          'not null' => FALSE,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('selected_template')->getValue();
    return $value === NULL || $value === '';
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['selected_template'] = DataDefinition::create('string')->setLabel(t('Template'));
    return $properties;
  }

}
