<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ElementLevel;

/**
 * Handles Site Studio element of "google map marker" type.
 */
class GoogleMapMarkerHandler extends ElementHandlerBase implements ElementLevelHandlerInterface {
  const ID = 'google-map-marker';
  const MAP = '/maps/element/google-map-marker.map.yml';
  const SCHEMA = '/maps/element/google-map-marker.schema.json';

}
