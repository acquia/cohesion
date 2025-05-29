<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ElementLevel;

/**
 * Handles Site Studio element of "inline element" type.
 */
class InlineElementHandler extends ElementHandlerBase implements ElementLevelHandlerInterface {
  const ID = 'inline-element';
  const MAP = '/maps/element/inline-element.map.yml';
  const SCHEMA = '/maps/element/inline-element.schema.json';

}
