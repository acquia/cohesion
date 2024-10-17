<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ElementLevel;

/**
 * Handles Site Studio element of "google map" type.
 */
class GoogleMapHandler extends ElementHandlerBase implements ElementLevelHandlerInterface {
  const ID = 'google-map';
  const MAP = '/maps/element/google-map.map.yml';
  const SCHEMA = '/maps/element/google-map.schema.json';

}
