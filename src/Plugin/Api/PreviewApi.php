<?php

namespace Drupal\cohesion\Plugin\Api;

use Drupal\cohesion\StylesApiPluginBase;
use Drupal\cohesion_base_styles\Entity\BaseStyles;
use Drupal\cohesion_custom_styles\Entity\CustomStyle;
use Drupal\Component\Serialization\Json;

/**
 * Send site studio preview template to its API.
 *
 * @package Drupal\cohesion
 *
 * @Api(
 *   id = "preview_api",
 *   name = @Translation("Preview send to API"),
 * )
 */
class PreviewApi extends StylesApiPluginBase {

  /**
   * The type of style to be previewed.
   *
   * @var string
   */
  protected $type;

  /**
   * @var string
   */
  protected $entity_type_id;

  /**
   * @var mixed
   */
  protected $style_model;

  /**
   * @var mixed
   */
  protected $style_mapper;

  /**
   *
   */
  public function getForms() {
    $form = [
      'parent' => [
        'title' => '',
        'type' => $this->type,
        'bundle' => 'preview_1',
        'values' => Json::encode($this->style_model),
        'mapper' => Json::encode($this->style_mapper),
      ],
    ];

    if ($this->entity_type_id != 'cohesion_base_styles') {
      $form['parent']['class_name'] = '.coh-preview';
    }

    return [
      $form,
    ];
  }

  /**
   * @param $entity_type_id
   * @param $style_model
   * @param $style_mapper
   */
  public function setupPreview($entity_type_id, $style_model, $style_mapper) {
    // Process the style model.
    $this->processBackgroundImageInheritance($style_model['styles']);
    $this->style_model = $style_model;
    $this->style_mapper = $style_mapper;
    $this->entity_type_id = $entity_type_id;

    // And save the values.
    if ($this->entity_type_id == 'cohesion_custom_style') {
      $this->type = CustomStyle::ASSET_GROUP_ID;
    }
    else {
      $this->type = BaseStyles::ASSET_GROUP_ID;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function send() {

    if (!(\Drupal::service('cohesion.utils')
      ->usedx8Status()) || $this->configInstaller->isSyncing()) {
      return FALSE;
    }

    // Prepare the data and DO NOT attach the stylesheet.json to the payload.
    $this->prepareData(FALSE);
    // Perform the send.
    $this->callApi();

    if ($this->response && floor($this->response['code'] / 200) == 1) {
      return TRUE;
    }
    else {
      return FALSE;
    }

  }

}
