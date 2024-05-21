<?php

namespace Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel;

/**
 * Handles Site Studio form fields of "input" type.
 */
class InputHandler extends FieldHandlerBase implements FormFieldLevelHandlerInterface {

  const ID = 'form-input';
  const MAP = '/maps/field_level/input.map.yml';
  const SCHEMA = '/maps/field_level/input.schema.json';

}
