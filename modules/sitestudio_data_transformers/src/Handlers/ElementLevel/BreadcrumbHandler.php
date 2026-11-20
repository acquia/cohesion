<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ElementLevel;

/**
 * Handles Site Studio element of "breadcrumb" type.
 */
class BreadcrumbHandler extends ElementHandlerBase implements ElementLevelHandlerInterface {
  const ID = 'breadcrumb';
  const MAP = '/maps/element/breadcrumb.map.yml';
  const SCHEMA = '/maps/element/breadcrumb.schema.json';

}
