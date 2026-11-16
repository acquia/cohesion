<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\Plugin\EntityUpdate\_0027EntityUpdate;
use Drupal\cohesion_custom_styles\Entity\CustomStyle;

/**
 * Class MockUpdateEntity.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class _0027MockUpdateEntity extends EntityMockBase implements EntityJsonValuesInterface {
}

/**
 * @group Cohesion
 */
class _0027EntityUpdateUnitTest extends EntityUpdateUnitTestCase {

  /**
   * @var unit_0027MockUpdateEntity*/
  protected $unit;

  private $custom_style_mapper_fixture = '{
  "styles": {
    "title": "Layout",
    "selectorType": "topLevel",
    "formDefinition": [],
    "items": [
      {
        "title": "testprefix",
        "type": "container",
        "items": [],
        "form": null,
        "selectorType": "prefix",
        "model": "testprefix",
        "uuid": "2f8bdfe2-eeca-4e89-9b03-efc031ab97ef",
        "allowedTypes": [
          "child",
          "pseudo",
          "modifier"
        ],
        "formDefinition": []
      }
    ],
    "form": null
  }
}';

  private $custom_style_model_fixture = '{
  "preview": {
    "text": "<div class=\"coh-preview\">Default content for \'Generic\'.</div>\n",
    "textFormat": "cohesion"
  },
  "sBackgroundColour": "#ffffff",
  "styles": {
    "settings": {
      "element": "div",
      "class": "",
      "combinator": "",
      "pseudo": ""
    },
    "2f8bdfe2-eeca-4e89-9b03-efc031ab97ef": {
      "settings": {
        "element": "",
        "class": "testprefix",
        "combinator": "",
        "pseudo": ""
      },
      "styles": {}
    }
  }
}';

  private $element_style_fixture = '{
  "model": {
    "0033b5c5-6a75-48d3-afd1-86b41284195d": {
      "settings": {
        "title": "Paragraph",
        "customStyle": [
          {
            "customStyle": ""
          }
        ],
        "settings": {
          "customStyle": [
            {
              "customStyle": ""
            }
          ]
        }
      },
      "context-visibility": {
        "contextVisibility": {
          "condition": "ALL"
        }
      },
      "styles": {
        "settings": {
          "element": "p"
        },
        "779f33c7-664a-4062-a45a-8e75127200d2": {
          "settings": {
            "element": "",
            "class": "testprefix",
            "combinator": "",
            "pseudo": ""
          },
          "styles": {}
        }
      }
    }
  },
  "mapper": {
    "0033b5c5-6a75-48d3-afd1-86b41284195d": {
      "settings": {
        "formDefinition": [
          {
            "formKey": "paragraph-settings",
            "children": [
              {
                "formKey": "paragraph-paragraph",
                "breakpoints": [],
                "activeFields": [
                  {
                    "name": "content",
                    "active": true
                  }
                ]
              },
              {
                "formKey": "paragraph-style",
                "breakpoints": [],
                "activeFields": [
                  {
                    "name": "customStyle",
                    "active": true
                  },
                  {
                    "name": "customStyle",
                    "active": true
                  }
                ]
              }
            ]
          }
        ],
        "selectorType": "topLevel",
        "form": null,
        "items": []
      },
      "styles": {
        "formDefinition": [],
        "selectorType": "topLevel",
        "items": [
          {
            "title": "testprefix",
            "type": "container",
            "items": [],
            "form": null,
            "selectorType": "prefix",
            "model": "testprefix",
            "uuid": "779f33c7-664a-4062-a45a-8e75127200d2",
            "allowedTypes": [
              "child",
              "pseudo",
              "modifier"
            ],
            "formDefinition": []
          }
        ],
        "form": null
      }
    }
  }
}';

  /**
   * {@inheritdoc}
   */
  public function setUp():void {
    $this->unit = new _0027EntityUpdate([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0027EntityUpdate::runUpdate
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0027EntityUpdate::walkStyleMapper
   */
  public function testRunCustomStyleUpdate() {
    // Test the custom style.
    $custom_style_entity = new CustomStyle([
      'id' => 'parent',
      'json_values' => $this->custom_style_model_fixture,
      'json_mapper' => $this->custom_style_mapper_fixture,
      'class_name' => '.coh-parent-class',
      'parent' => FALSE,
    ], 'cohesion_custom_style');

    $decoded_mapper = json_decode($custom_style_entity->getJsonMapper(), TRUE);
    $decoded_model = json_decode($custom_style_entity->getJsonValues(), TRUE);

    $this->assertEquals('prefix', $decoded_mapper['styles']['items'][0]['selectorType']);
    $this->assertEquals('testprefix', $decoded_model['styles']['2f8bdfe2-eeca-4e89-9b03-efc031ab97ef']['settings']['class']);

    $this->unit->runUpdate($custom_style_entity);
    $decoded_model = json_decode($custom_style_entity->getJsonValues(), TRUE);
    $this->assertEquals('.testprefix', $decoded_model['styles']['2f8bdfe2-eeca-4e89-9b03-efc031ab97ef']['settings']['class']);

    $this->unit->runUpdate($custom_style_entity);
    $decoded_model = json_decode($custom_style_entity->getJsonValues(), TRUE);
    $this->assertEquals('.testprefix', $decoded_model['styles']['2f8bdfe2-eeca-4e89-9b03-efc031ab97ef']['settings']['class']);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0027EntityUpdate::runUpdate
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0027EntityUpdate::walkStyleMapper
   */
  public function testRunElementStyleUpdate() {
    // Test the element style.
    $layout = new _0027MockUpdateEntity($this->element_style_fixture, TRUE);

    $layout_array_before = $layout->getDecodedJsonValues();
    $this->assertEquals('prefix', $layout_array_before['mapper']['0033b5c5-6a75-48d3-afd1-86b41284195d']['styles']['items'][0]['selectorType']);
    $this->assertEquals('testprefix', $layout_array_before['model']['0033b5c5-6a75-48d3-afd1-86b41284195d']['styles']['779f33c7-664a-4062-a45a-8e75127200d2']['settings']['class']);

    $this->unit->runUpdate($layout);
    $layout_array_after = json_decode($layout->getJsonValues(), TRUE);
    $this->assertEquals('.testprefix', $layout_array_after['model']['0033b5c5-6a75-48d3-afd1-86b41284195d']['styles']['779f33c7-664a-4062-a45a-8e75127200d2']['settings']['class']);

    $this->unit->runUpdate($layout);
    $layout_array_after = json_decode($layout->getJsonValues(), TRUE);
    $this->assertEquals('.testprefix', $layout_array_after['model']['0033b5c5-6a75-48d3-afd1-86b41284195d']['styles']['779f33c7-664a-4062-a45a-8e75127200d2']['settings']['class']);
  }

}
