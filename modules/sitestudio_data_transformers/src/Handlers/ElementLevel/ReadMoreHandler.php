<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ElementLevel;

/**
 * Handles Site Studio element of "read more" type.
 */
class ReadMoreHandler extends ElementHandlerBase implements ElementLevelHandlerInterface {
  const ID = 'read-more';
  const MAP = '/maps/element/read-more.map.yml';
  const SCHEMA = '/maps/element/read-more.schema.json';

}
