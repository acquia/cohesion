<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ElementLevel;

/**
 * Handles Site Studio element of "image" type.
 */
class ImageHandler extends ImageHandlerBase implements ElementLevelHandlerInterface {
  const ID = 'image';
  const MAP = '/maps/element/image.map.yml';
  const SCHEMA = '/maps/element/image.schema.json';

}
