<?php

namespace Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel;

/**
 * Handles Site Studio form fields of "icon picker" type.
 */
class IconpickerHandler extends FieldHandlerBase implements FormFieldLevelHandlerInterface {

  const ID = 'form-iconpicker';
  const MAP = '/maps/field/iconpicker.map.yml';
  const SCHEMA = '/maps/field/iconpicker.schema.json';

}
