<?php

namespace Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel;

/**
 * Handles Site Studio form fields of "link" type.
 */
class LinkHandler extends FieldHandlerBase implements FormFieldLevelHandlerInterface {

  const ID = 'form-link';
  const MAP = '/maps/field_level/link.map.yml';
  const SCHEMA = '/maps/field_level/link.schema.json';

}
