<?php

namespace Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel;

/**
 * Handles Site Studio form fields of "input" type.
 */
class InputHiddenHandler extends FieldHandlerBase implements FormFieldLevelHandlerInterface {

  const ID = 'form-input-hidden';
  const MAP = '/maps/field_level/input-hidden.map.yml';
  const SCHEMA = '/maps/field_level/input-hidden.schema.json';

}
