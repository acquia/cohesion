<?php

namespace Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel;

/**
 * Handles Site Studio form fields of "video" type.
 */
class VideoHandler extends FieldHandlerBase implements FormFieldLevelHandlerInterface {

  const ID = 'form-video-embed';
  const MAP = '/maps/field_level/video.map.yml';
  const SCHEMA = '/maps/field_level/video.schema.json';

}
