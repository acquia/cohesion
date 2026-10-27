<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ElementLevel;

/**
 * Handles Site Studio element of "youtube video embed" type.
 */
class YoutubeVideoEmbedHandler extends ElementHandlerBase implements ElementLevelHandlerInterface {
  const ID = 'youtube-video-embed';
  const MAP = '/maps/element/youtube-video-embed.map.yml';
  const SCHEMA = '/maps/element/youtube-video-embed.schema.json';

}
