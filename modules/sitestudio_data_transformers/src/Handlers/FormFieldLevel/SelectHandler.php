<?php

namespace Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel;

use Drupal\cohesion\LayoutCanvas\Element;

/**
 * Handles Site Studio form fields of "input" type.
 */
class SelectHandler extends FieldHandlerBase implements FormFieldLevelHandlerInterface {

  /**
   * Site Studio Element type id.
   * @todo make these injectable from DIC or at least configurable/changeable by clients.
   */
  const ID = 'form-select';
  const MAP = '/maps/field_level/select.map.yml';
  const SCHEMA = '/maps/field_level/select.schema.json';

  /**
   * {@inheritdoc}
   */
  public function getSchema(Element $form_field = NULL): array {

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
