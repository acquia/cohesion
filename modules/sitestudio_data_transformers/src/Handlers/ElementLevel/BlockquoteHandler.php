<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ElementLevel;

/**
 * Handles Site Studio element of "blockquote" type.
 */
class BlockquoteHandler extends ElementHandlerBase implements ElementLevelHandlerInterface {
  const ID = 'blockquote';
  const MAP = '/maps/element/blockquote.map.yml';
  const SCHEMA = '/maps/element/blockquote.schema.json';

}
