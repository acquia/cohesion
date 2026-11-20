<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ElementLevel;

/**
 * Handles Site Studio element of "paragraph" type.
 */
class ParagraphHandler extends ElementHandlerBase implements ElementLevelHandlerInterface {
  const ID = 'paragraph';
  const MAP = '/maps/element/paragraph.map.yml';
  const SCHEMA = '/maps/element/paragraph.schema.json';

}
