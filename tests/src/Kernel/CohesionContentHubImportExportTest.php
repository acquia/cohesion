<?php

namespace Drupal\Tests\cohesion\Kernel;


use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\cohesion_elements\Entity\CohesionLayout;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Tests\acquia_contenthub\Kernel\ImportExportTestBase;

/**
 * Class CohesionContentHubImportExportTest.
 *
 * @group Cohesion
 *
 * @requires module cohesion
 *
 * @package Drupal\Tests\cohesion\Kernel
 */
class CohesionContentHubImportExportTest extends ImportExportTestBase {

  const ENTITY_REFERENCE_TYPES = parent::ENTITY_REFERENCE_TYPES + ['cohesion_entity_reference_revisions'];

  /**
   * Returns fixture content.
   *
   * @inheritDoc
   */
  protected function getFixtureString(int $delta) {
    if (!empty($this->fixtures[$delta])) {
      $version_directory = $this->getDrupalVersion();
      $path_to_fixture = sprintf("%s/tests/fixtures/import/$version_directory/%s",
        drupal_get_path('module', 'cohesion'),
        $this->fixtures[$delta]['cdf']
      );
      return file_get_contents($path_to_fixture);
    }

    throw new \Exception(sprintf("Missing fixture for delta %d in class %s", $delta, __CLASS__));
  }

  /**
   * Returns fixture expectations.
   *
   * @inheritDoc
   */
  protected function getFixtureExpectations(int $delta) {
    if (!empty($this->fixtures[$delta])) {
      $version_directory = $this->getDrupalVersion();
      $path_to_fixture = sprintf("%s/tests/fixtures/import/$version_directory/%s",
        drupal_get_path('module', 'cohesion'),
        $this->fixtures[$delta]['expectations']
      );

      return include $path_to_fixture;
    }

    throw new \Exception(sprintf("Missing expectations for delta %d in class %s", $delta, __CLASS__));
  }

  /**
   * Handle custom field types to more accurately match expectations.
   *
   * @inheritDoc
   */
  protected function handleFieldValues(FieldItemListInterface $field) {
    $values = $field->getValue();
    if (in_array($field->getFieldDefinition()->getType(), static::ENTITY_REFERENCE_TYPES) && $values) {
      $values = [];
      foreach ($field as $item_delta => $item) {
        if ($item->getValue()['target_id']) {
          $values[$item_delta]['target_id'] = $item->entity->uuid();
        }
      }
    }elseif($field->getEntity() instanceof CohesionLayout && $field->getName() == 'json_values' && isset($values[0]['value'])) {
      $layout_canvas = new LayoutCanvas($values[0]['value']);
      $values[0]['value'] = json_encode($layout_canvas);
    }else {
      $values = parent::handleFieldValues($field);
    }


    return $values;
  }

  /**
   * Normalize fixture and expected object.
   *
   * @inheritDoc
   */
  protected function normalizeFixtureAndObject(array $fixture, array $object): array {
    $list = [
      'last_entity_update',
    ];

    // If the fixture had no value, we should not evaluate the object.
    foreach ($fixture as $key => $value) {
      if (!$value) {
        $list[] = $key;
      }
    }

    foreach ($list as $item) {
      if (isset($fixture[$item])) {
        unset($fixture[$item]);
      }

      if (isset($object[$item])) {
        unset($object[$item]);
      }
    }

    if(isset($fixture['json_values']['value']['en']['value']) && isset($object['json_values']['value']['en']['value'])) {
      $fixture['json_values']['value']['en']['value'] = json_encode(new LayoutCanvas($fixture['json_values']['value']['en']['value']));
      $object['json_values']['value']['en']['value'] = json_encode(new LayoutCanvas($object['json_values']['value']['en']['value']));
    }

    return parent::normalizeFixtureAndObject($fixture, $object);
  }

}
