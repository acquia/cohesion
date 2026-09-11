<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ElementLevel;

/**
 * Handles Site Studio element of "modal" type.
 */
class ModalHandler extends ElementHandlerBase implements ElementLevelHandlerInterface {
  const ID = 'modal';
  const MAP = '/maps/element/modal.map.yml';
  const SCHEMA = '/maps/element/modal.schema.json';

}
