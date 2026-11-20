<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ElementLevel;

/**
 * Handles Site Studio element of "custom element" type.
 */
class CustomElementHandler extends ElementHandlerBase implements ElementLevelHandlerInterface {
  const ID = 'custom-element';
  const MAP = '/maps/element/custom-element.map.yml';
  const SCHEMA = '/maps/element/custom-element.schema.json';

}
