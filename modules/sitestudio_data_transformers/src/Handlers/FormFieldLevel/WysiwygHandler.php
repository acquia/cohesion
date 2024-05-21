<?php

namespace Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel;

/**
 * Handles Site Studio form fields of "wysiwyg" type.
 */
class WysiwygHandler extends FieldHandlerBase implements FormFieldLevelHandlerInterface {

  const ID = 'form-wysiwyg';
  const MAP = '/maps/field_level/wysiwyg.map.yml';
  const SCHEMA = '/maps/field_level/wysiwyg.schema.json';

}
