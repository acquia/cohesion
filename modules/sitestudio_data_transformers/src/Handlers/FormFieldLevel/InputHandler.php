<?php

namespace Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel;

use Drupal\cohesion\LayoutCanvas\Element;

/**
 * Handles Site Studio form fields of "input" type.
 */
class InputHandler extends FieldHandlerBase implements FormFieldLevelHandlerInterface {

  /**
   * Site Studio Element type id.
   * @todo make these injectable from DIC or at least configurable/changeable by clients.
   */
  const ID = 'form-input';
  const MAP = '/maps/field_level/input.map.yml';
  const SCHEMA = '/maps/field_level/input.schema.json';

  /**
   * {@inheritdoc}
   */
  public function getDynamicSchema(Element $form_field = NULL): array {

    if (is_null($form_field)) {
      return json_decode($this->schema, TRUE);
    }

    $settings = $form_field->getModel()->getProperty('settings');
    if (isset($settings->schema) && !empty($settings->schema)) {
      $schema = json_decode($this->schema, TRUE);
      if (isset($settings->schema->maxLength)) {
        //@todo make this cleaner
        $schema['properties']['attributes']['properties']['value']['maxLength'] = $settings->schema->maxLength;
      }
    }

    return $schema;
  }

}
