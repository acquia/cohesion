<?php

namespace Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel;

/**
 * Handles Site Studio form fields of "colorpicker" type.
 */
class ColorpickerHandler extends FieldHandlerBase implements FormFieldLevelHandlerInterface {

  const ID = 'form-colorpicker';
  const MAP = '/maps/field/colorpicker.map.yml';
  const SCHEMA = '/maps/field/colorpicker.schema.json';

}
