<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ElementLevel;

/**
 * Handles Site Studio element of "drupal menu" type.
 */
class DrupalMenuHandler extends ElementHandlerBase implements ElementLevelHandlerInterface {
  const ID = 'drupal-menu';
  const MAP = '/maps/element/drupal-menu.map.yml';
  const SCHEMA = '/maps/element/drupal-menu.schema.json';

}
