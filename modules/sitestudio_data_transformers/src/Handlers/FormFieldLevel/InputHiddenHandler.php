<?php

namespace Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel;

/**
 * Handles Site Studio form fields of "hidden input" type.
 */
class InputHiddenHandler extends FieldHandlerBase implements FormFieldLevelHandlerInterface {

  const ID = 'form-input-hidden';
  const MAP = '/maps/field/input-hidden.map.yml';
  const SCHEMA = '/maps/field/input-hidden.schema.json';

}
