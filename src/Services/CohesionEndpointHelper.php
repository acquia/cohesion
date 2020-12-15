<?php

namespace Drupal\cohesion\Services;

use Drupal\Component\Serialization\Json;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class CohesionEndpointHelper.
 *
 * @package Drupal\cohesion\Helper
 */
class CohesionEndpointHelper {
  use StringTranslationTrait;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected $stringTranslation;

  /**
   * CohesionEndpointHelper constructor.
   */
  public function __construct() {
    $this->entityTypeManager = \Drupal::service('entity_type.manager');
    $this->stringTranslation = \Drupal::service('string_translation');
  }

  /**
   * @param array $values
   * @param array $content
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function saveElement($values = [], $content = []) {
    $types = [
      'helper' => 'cohesion_helper',
      'component' => 'cohesion_component',
    ];
    $content['json_values'] = Json::encode($content['json_values']);
    // Determine entity_type_id (default helper).
    $type = isset($content['type']) ? $content['type'] : NULL;
    if (in_array($type, array_keys($types))) {
      $entity_type_id = $types[$type];
    }
    else {
      // Unsupported entity type.
      $error = TRUE;
      $message = $this->t('Unsupported entity type');
    }

    // Create a machine name from the label.
    $storage = $this->entityTypeManager->getStorage($entity_type_id);

    $machine_name = preg_replace("/[^A-Za-z0-9\s]/", '', strtolower($values['label']));
    $machine_name = str_replace('-', '_', $machine_name);
    $machine_name = str_replace(' ', '_', $machine_name);

    $entity_class = $storage->getEntityType()->getOriginalClass();
    $prefix = $entity_class::ENTITY_MACHINE_NAME_PREFIX;
    $machine_name = $prefix . $machine_name;

    if (strlen($machine_name) > 32) {
      $machine_name = substr($machine_name, 0, 32);
    }

    if ($storage->load($machine_name)) {
      $error = TRUE;
      $message = $this->t('Failed to save @type with an automatically generated machine name of @machine_name. Please use a different title.', ['@type' => $type, '@machine_name' => $machine_name]);
    }
    else {
      list($error, $message) = $this->createElement($entity_type_id, $values, $machine_name);
    }

    return [$error, $message];
  }

  /**
   *
   * @param string $entity_type_id
   * @param array $payload
   *
   * @return array list of boolean error status and string message: array(FALSE, 'message')
   */
  public function createElement($entity_type_id, $payload, $machine_name) {
    // Set up the preview_image field.
    if (isset($payload['preview_image']->path) && is_numeric($payload['preview_image']->json)) {
      $payload['preview_image'] = [$payload['preview_image']->json];
    }
    try {
      // Create the entity object.
      $entity = $this->entityTypeManager->getStorage($entity_type_id)->create($payload);

      // Set entity id.
      $entity->set('id', $machine_name);

      // Set other entity values.
      $entity->setStatus(TRUE);

      // Save.
      $entity->save();
      $error = FALSE;
      $message = $this->t('Entity saved');
    }
    catch (\Exception $ex) {
      // Error creating entity.
      $error = TRUE;
      $message = $this->t('Cannot create entity with error: @error', ['@error' => $ex->getMessage()]);
    }
    return [$error, $message];
  }

}
