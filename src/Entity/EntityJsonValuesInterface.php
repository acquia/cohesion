<?php

namespace Drupal\cohesion\Entity;

/**
 * Defines the interface for EntityJsonValues entities.
 *
 * @package Drupal\cohesion
 */
interface EntityJsonValuesInterface {

  /**
   * Return json_values field data for angular form.
   *
   * @return string
   *   Return the JSON values data.
   */
  public function getJsonValues();

  /**
   * Return the decoded JSON values data.
   *
   * @param bool $as_object
   *   To get JSON as object.
   *
   * @return mixed
   */
  public function getDecodedJsonValues($as_object = FALSE);

  /**
   * Set the json_values field data for angular form.
   *
   * @param string $json_values
   *   The json_values field from the angular form.
   *
   * @return string
   *   Return the JSON values data that was set.
   */
  public function setJsonValue($json_values);

  /**
   * Process entity to be validated.
   *
   * @return array
   *   Return processed values or errors
   */
  public function jsonValuesErrors();

  /**
   * Return json_mapper field data for angular form.
   *
   * @return string
   *   Return the JSON mapper data.
   */
  public function getJsonMapper();

  /**
   * Set the json_mapper field data for angular form.
   *
   * @param string $json_mapper
   *   The json_mapper field from the angular form.
   *
   * @return string
   *   Return the JSON mapper data that was set.
   */
  public function setJsonMapper($json_mapper);

  /**
   * Process json_values to the API.
   *
   * @return \Drupal\cohesion\ApiPluginBase
   */
  public function process();

  /**
   * Determine if the config is a layout canvas the returns a template.
   *
   * @return bool
   */
  public function isLayoutCanvas();

  /**
   * Get the LayoutCanvas entity for this entity.
   *
   * @return \Drupal\cohesion\LayoutCanvas\LayoutCanvas|bool
   */
  public function getLayoutCanvasInstance();

  /**
   * Return the API plugin instance for this entity.
   *
   * @return \Drupal\cohesion\ApiPluginInterface
   */
  public function getApiPluginInstance();

  /**
   * Process values to be sent to the API.
   *
   * @return bool
   */
  public function preProcessJsonValues();

}
