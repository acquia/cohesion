<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ElementLevel;

/**
 * Handles Site Studio element of "drupal block" type.
 */
class DrupalBlockHandler extends ElementHandlerBase implements ElementLevelHandlerInterface {
  const ID = 'drupal-block';
  const MAP = '/maps/element/drupal-block.map.yml';
  const SCHEMA = '/maps/element/drupal-block.schema.json';

}
