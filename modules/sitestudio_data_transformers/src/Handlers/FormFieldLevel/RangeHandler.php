<?php

namespace Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel;

/**
 * Handles Site Studio form fields of "range" type.
 */
class RangeHandler extends FieldHandlerBase implements FormFieldLevelHandlerInterface {

  const ID = 'form-range-slider';
  const MAP = '/maps/field/range.map.yml';
  const SCHEMA = '/maps/field/range.schema.json';

}
