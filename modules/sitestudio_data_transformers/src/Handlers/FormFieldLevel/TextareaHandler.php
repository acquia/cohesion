<?php

namespace Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel;

/**
 * Handles Site Studio form fields of "textarea" type.
 */
class TextareaHandler extends FieldHandlerBase implements FormFieldLevelHandlerInterface {

  const ID = 'form-textarea';
  const MAP = '/maps/field/textarea.map.yml';
  const SCHEMA = '/maps/field/textarea.schema.json';

}
