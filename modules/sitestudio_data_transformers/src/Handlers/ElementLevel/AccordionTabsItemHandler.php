<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ElementLevel;

/**
 * Handles Site Studio element of "accordion tabs item" type.
 */
class AccordionTabsItemHandler extends ElementHandlerBase implements ElementLevelHandlerInterface {

  const ID = 'accordion-tabs-item';
  const MAP = '/maps/element/accordion-tabs-item.map.yml';
  const SCHEMA = '/maps/element/accordion-tabs-item.schema.json';

}
