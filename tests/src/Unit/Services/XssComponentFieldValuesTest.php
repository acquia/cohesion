<?php

namespace Drupal\Tests\cohesion_style_guide\Unit\Services;

use Drupal\cohesion\LayoutCanvas\Element;
use Drupal\cohesion\Services\CohesionUtils;
use Drupal\Component\Utility\Html;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use \Drupal\Tests\UnitTestCase;

/**
 * Class XssComponentFieldValuesTest
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\cohesion_style_guide\Unit\Services
 */
class XssComponentFieldValuesTest extends UnitTestCase {

  protected $cohUtils;

  /**
   * Setup the test
   */
  public function setup(): void {

    $theme_handler = $this->createMock(ThemeHandlerInterface::class);
    $theme_manager = $this->createMock(ThemeManagerInterface::class);
    $entity_type_manager = $this->createMock(EntityTypeManagerInterface::class);
    $entity_type_manager->expects($this->any())
      ->method('getStorage')
      ->willReturn(FALSE);
    $language_manager = $this->createMock(LanguageManagerInterface::class);
    $logger_factory = $this->createMock(LoggerChannelFactoryInterface::class);
    $config_factory = $this->createMock(ConfigFactoryInterface::class);

    $this->cohUtils = new CohesionUtils($theme_handler, $theme_manager, $entity_type_manager, $language_manager, $logger_factory, $config_factory);
  }

  /**
   * Test CohesionUtils processFieldValues
   *
   * @dataProvider processFieldValuesDataProvider
   *
   * @covers \Drupal\cohesion\Services\CohesionUtils::processFieldValues
   */
  public function testProcessFieldValues($element_data, $model, $fieldValue, $expected) {
    $element = new Element(json_decode(json_encode($element_data)), NULL, json_decode(json_encode($model)));
    $test_result = $this->cohUtils->processFieldValues($fieldValue, $element->getModel());
    $this->assertEquals($expected, $test_result);
  }


  /**
   * Data provider for ::testProcessFieldValues.
   * @return array
   */
  public function processFieldValuesDataProvider() {
    $data = [];

    $element_data = [
      'uuid' => '1',
    ];
    $fieldValue = '<script>alter("attackme")</script>';
    $escaped_field_value = Html::escape($fieldValue);

    // Test input, link to page
    $model = [
      '1' => [
        'settings' => [
          'schema' => [
            'type' => 'string'
          ]
        ]
      ]
    ];
    $data[] = [$element_data, $model, $fieldValue, $escaped_field_value];

    // Test textarea
    $model = [
      '1' => [
        'settings' => [
          'type' => 'cohTextarea',
        ],
      ],
    ];
    $data[] = [$element_data, $model, $fieldValue, $escaped_field_value];

    // Test no type - string
    $model = [
      '1' => [
        'settings' => [
          'type' => 'notype',
        ],
      ],
    ];
    $data[] = [$element_data, $model, $fieldValue, $escaped_field_value];

    // Test no type - array
    $arrayFieldValue = [
      'test' => $fieldValue,
      'nested' => [
        'test' => $fieldValue,
      ]
    ];
    $arrayExpected = [
      'test' => $escaped_field_value,
      'nested' => [
        'test' => $escaped_field_value,
      ]
    ];
    $data[] = [$element_data, $model, $arrayFieldValue, $arrayExpected];

    // Test no type - object
    $objectFieldValue = [
      'test' => $fieldValue,
      'nested' => [
        'test' => $fieldValue,
      ]
    ];
    $objectExpected = [
      'test' => $escaped_field_value,
      'nested' => [
        'test' => $escaped_field_value,
      ]
    ];
    $data[] = [$element_data, $model, json_decode(json_encode($objectFieldValue)), json_decode(json_encode($objectExpected))];

    // Test no type - json
    $jsonFieldValue = [
      'test' => $fieldValue,
      'nested' => [
        'test' => $fieldValue,
      ]
    ];
    $jsonExpected = [
      'test' => $escaped_field_value,
      'nested' => [
        'test' => $escaped_field_value,
      ]
    ];;
    $data[] = [$element_data, $model, json_encode($jsonFieldValue), json_encode($jsonExpected)];

    // checkboxToggle string
    $model = [
      '1' => [
        'settings' => [
          'type' => 'checkboxToggle',
          'toggleType' => 'string',
          'trueValue' => 'result'
        ],
      ],
    ];

    $data[] = [$element_data, $model, $fieldValue, 'result'];

    // checkboxToggle number
    $model = [
      '1' => [
        'settings' => [
          'type' => 'checkboxToggle',
          'toggleType' => 'string',
          'trueValue' => 1
        ],
      ],
    ];

    $data[] = [$element_data, $model, $fieldValue, 1];

    // checkboxToggle no value
    $model = [
      '1' => [
        'settings' => [
          'type' => 'checkboxToggle',
          'toggleType' => 'string',
        ],
      ],
    ];

    $data[] = [$element_data, $model, $fieldValue, ''];

    // Select
    $model = [
      '1' => [
        'settings' => [
          'type' => 'cohSelect',
          'options' => [
            [
              'value' => 'testvalue'
            ]
          ],
        ],
      ],
    ];

    $data[] = [$element_data, $model, $fieldValue, ''];

    // Select
    $model = [
      '1' => [
        'settings' => [
          'type' => 'cohWysiwyg',
        ],
      ],
    ];

    $data[] = [$element_data, $model, $fieldValue, $fieldValue];

    return $data;
  }

}
