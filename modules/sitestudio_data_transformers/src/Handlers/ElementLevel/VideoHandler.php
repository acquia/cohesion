<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ElementLevel;

/**
 * Handles Site Studio element of "video" type.
 */
class VideoHandler extends ImageHandlerBase implements ElementLevelHandlerInterface {
  const ID = 'video';
  const MAP = '/maps/element/video.map.yml';
  const SCHEMA = '/maps/element/video.schema.json';

}
