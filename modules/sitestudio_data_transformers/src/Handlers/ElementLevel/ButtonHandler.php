<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ElementLevel;

/**
 * Handles Site Studio element of "button" type.
 */
class ButtonHandler extends ElementHandlerBase implements ElementLevelHandlerInterface {
  const ID = 'button';
  const MAP = '/maps/element/button.map.yml';
  const SCHEMA = '/maps/element/button.schema.json';

}
