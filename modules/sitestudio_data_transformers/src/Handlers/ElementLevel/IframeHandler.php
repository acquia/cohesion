<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ElementLevel;

/**
 * Handles Site Studio element of "iframe" type.
 */
class IframeHandler extends ElementHandlerBase implements ElementLevelHandlerInterface {
  const ID = 'iframe';
  const MAP = '/maps/element/iframe.map.yml';
  const SCHEMA = '/maps/element/iframe.schema.json';

}
