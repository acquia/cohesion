<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ElementLevel;

/**
 * Handles Site Studio element of "wysiwyg" type.
 */
class WysiwygHandler extends ElementHandlerBase implements ElementLevelHandlerInterface {

  const ID = 'wysiwyg';
  const MAP = '/maps/element/wysiwyg.map.yml';
  const SCHEMA = '/maps/element/wysiwyg.schema.json';

}
