<?php

namespace Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel;

/**
 * Handles Site Studio form fields of "youtube" type.
 */
class YoutubeHandler extends FieldHandlerBase implements FormFieldLevelHandlerInterface {

  const ID = 'form-youtube-embed';
  const MAP = '/maps/field/youtube.map.yml';
  const SCHEMA = '/maps/field/youtube.schema.json';

}
