<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ElementLevel;

/**
 * Handles Site Studio element of "youtube video background" type.
 */
class YoutubeVideoBackgroundHandler extends ImageHandlerBase implements ElementLevelHandlerInterface {
  const ID = 'youtube-video-background';
  const MAP = '/maps/element/youtube-video-background.map.yml';
  const SCHEMA = '/maps/element/youtube-video-background.schema.json';

}
