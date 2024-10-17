<?php

namespace Drupal\cohesion;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 *
 */
interface ApiPluginInterface extends PluginInspectionInterface {

  /**
   * Return the name of the reusable form plugin.
   *
   * @return string
   */
  public function getName();

  /**
   * Call the API, get the response and replace the tokenized content.
   *
   * @return mixed
   */
  public function callApi();

  /**
   * Prepare the data to be send to the API in order to generate the right
   * asset. Attach the JSON representation of the stylesheet to the data
   * object.
   *
   * @param bool $attach_css
   */
  public function prepareData($attach_css = TRUE);

  /**
   * @param \Drupal\cohesion\Entity\EntityJsonValuesInterface $entity
   */
  public function setEntity(EntityJsonValuesInterface $entity);

  /**
   * Get the forms to be processed by the API.
   *
   * @return array
   */
  public function getForms();

}
