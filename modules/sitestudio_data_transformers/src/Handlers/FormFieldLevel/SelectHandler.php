<?php

namespace Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel;

/**
 * Handles Site Studio form fields of "select" type.
 */
class SelectHandler extends FieldHandlerBase implements FormFieldLevelHandlerInterface {

  const ID = 'form-select';
  const MAP = '/maps/field_level/select.map.yml';
  const SCHEMA = '/maps/field_level/select.schema.json';

}
