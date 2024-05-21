<?php

namespace Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel;

/**
 * Handles Site Studio form fields of "toggle" type.
 */
class ToggleHandler extends FieldHandlerBase implements FormFieldLevelHandlerInterface {

  const ID = 'form-checkbox-toggle';
  const MAP = '/maps/field_level/toggle.map.yml';
  const SCHEMA = '/maps/field_level/toggle.schema.json';

}
