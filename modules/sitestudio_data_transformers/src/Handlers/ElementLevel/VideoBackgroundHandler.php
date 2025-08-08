<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ElementLevel;

/**
 * Handles Site Studio element of "video background" type.
 */
class VideoBackgroundHandler extends ImageHandlerBase implements ElementLevelHandlerInterface {
  const ID = 'video-background';
  const MAP = '/maps/element/video-background.map.yml';
  const SCHEMA = '/maps/element/video-background.schema.json';

}
