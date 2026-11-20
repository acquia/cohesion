<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ElementLevel;

/**
 * Handles Site Studio element of "heading" type.
 */
class HeadingHandler extends ElementHandlerBase implements ElementLevelHandlerInterface {
  const ID = 'heading';
  const MAP = '/maps/element/heading.map.yml';
  const SCHEMA = '/maps/element/heading.schema.json';

}
