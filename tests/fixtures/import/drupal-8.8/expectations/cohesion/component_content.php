<?php

/**
 * @file
 * Expectation for node with cohesion scenario.
 */

use Drupal\Tests\cohesion\Kernel\Stubs\CohesionCdfExpectations;

$data = [
  'uuid' => [
    'en' => [
      ['value' => 'ced8e818-f68a-4f7d-8408-6fbf61166874'],
    ],
  ],
  'langcode' => [
    'en' => [
      ['value' => 'en'],
    ],
  ],
  'revision_timestamp' => [
    'en' => [
      ['value' => '1584713128'],
    ],
  ],
  'revision_uid' => [
    'en' => [
      ['target_id' => '47dcfb30-3c6e-46ce-990d-2632020849fc'],
    ],
  ],
  'status' => [
    'en' => [
      ['value' => 1],
    ],
  ],
  'title' => [
    'en' => [
      ['value' => 'ContentHubTestComponentContent'],
    ],
  ],
  'uid' => [
    'en' => [
      ['target_id' => '47dcfb30-3c6e-46ce-990d-2632020849fc'],
    ],
  ],
  'created' => [
    'en' => [
      ['value' => '1584713128'],
    ],
  ],
  'changed' => [
    'en' => [
      ['value' => '1585134422'],
    ],
  ],
  'layout_canvas' => [
    'en' => [
      ['target_id' => 'c255b7e7-5fef-4e24-83cf-fe0a777c5cfb'],
    ],
  ],
  'component' => [
    'en' => [
      ['target_id' => '4361650b-c13e-42ce-9260-e5f9a8e31288'],
    ],
  ],
  'default_langcode' => [
    'en' => [
      ['value' => '1'],
    ],
  ],
  'revision_default' => [
    'en' => [
      ['value' => '1'],
    ],
  ],
  'revision_translation_affected' => [
    'en' => [
      ['value' => '1'],
    ],
  ],
];

$expectations['ced8e818-f68a-4f7d-8408-6fbf61166874'] = new CohesionCdfExpectations($data, ['id', 'vid']);

$data = [
  'uuid' => [
    'en' => [
      ['value' => 'c255b7e7-5fef-4e24-83cf-fe0a777c5cfb'],
    ],
  ],
  'langcode' => [
    'en' => [
      ['value' => 'en'],
    ],
  ],
  'json_values' => [
    'en' => [
      ['value' => '{"canvas":[{"uid":"cpt_contenthubtestcomponent","type":"component","title":"ContentHubTestComponent","enabled":true,"category":"category-3","componentId":"cpt_contenthubtestcomponent","componentType":"heading","uuid":"90626630-68d9-4c22-951d-8559a7eca2a7","parentUid":"root","isContainer":0,"model":{"settings":{"title":"ContentHubTestComponentContent"}},"children":[]}],"model":{"90626630-68d9-4c22-951d-8559a7eca2a7":{"settings":{"title":"ContentHubTestComponentContent"}}},"mapper":null}'],
    ],
  ],
  'parent_type' => [
    'en' => [
      ['value' => 'component_content'],
    ],
  ],
  'parent_field_name' => [
    'en' => [
      ['value' => 'layout_canvas'],
    ],
  ],
  'default_langcode' => [
    'en' => [
      ['value' => '1'],
    ],
  ],
  'revision_default' => [
    'en' => [
      ['value' => '1'],
    ],
  ],
  'revision_translation_affected' => [
    'en' => [
      ['value' => '1'],
    ],
  ],
];

$expectations['c255b7e7-5fef-4e24-83cf-fe0a777c5cfb'] = new CohesionCdfExpectations($data, ['id', 'revision', 'styles', 'template', 'last_entity_update', 'parent_id']);

return $expectations;
