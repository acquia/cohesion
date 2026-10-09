<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ElementLevel;

/**
 * Handles Site Studio element of "slider container" type.
 */
class SliderContainerHandler extends ElementHandlerBase implements ElementLevelHandlerInterface {
  const ID = 'slider-container';
  const MAP = '/maps/element/slider-container.map.yml';
  const SCHEMA = '/maps/element/slider-container.schema.json';

}
