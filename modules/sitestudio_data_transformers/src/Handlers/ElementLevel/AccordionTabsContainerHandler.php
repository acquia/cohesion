<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ElementLevel;

/**
 * Handles Site Studio element of "accordion tabs container" type.
 */
class AccordionTabsContainerHandler extends ElementHandlerBase implements ElementLevelHandlerInterface {
  const ID = 'accordion-tabs-container';
  const MAP = '/maps/element/accordion-tabs-container.map.yml';
  const SCHEMA = '/maps/element/accordion-tabs-container.schema.json';

}
