<?php

namespace Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel;

/**
 * Handles Site Studio form fields of "google map marker" type.
 */
class GoogleMapMarkerHandler extends FieldHandlerBase implements FormFieldLevelHandlerInterface {

  const ID = 'form-google-map-marker';
  const MAP = '/maps/field_level/google-map-marker.map.yml';
  const SCHEMA = '/maps/field_level/google-map-marker.schema.json';

}
