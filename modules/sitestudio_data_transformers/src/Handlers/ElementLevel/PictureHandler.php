<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ElementLevel;

/**
 * Handles Site Studio element of "picture" type.
 */
class PictureHandler extends ElementHandlerBase implements ElementLevelHandlerInterface {
  const ID = 'picture';
  const MAP = '/maps/element/picture.map.yml';
  const SCHEMA = '/maps/element/picture.schema.json';

}
