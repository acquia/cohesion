<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\Plugin\EntityUpdate\_0030EntityUpdate;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\node\Entity\Node;

/**
 * Class MockUpdateEntity.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class _0030MockUpdateEntity extends EntityMockBase implements EntityJsonValuesInterface {
}

/**
 * Class _0030EntityUpdateMock.
 *
 * @package Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate
 */
class _0030EntityUpdateMock extends _0030EntityUpdate {
  const NODES = [
    '32' => '7493aabf-22e0-4a62-ad1d-08dd5a95f15d',
    '173' => '84a7017e-2416-11eb-adc1-0242ac120002',
    '5' => '8b56827e-2416-11eb-adc1-0242ac120002',
    '40' => '3ab6ad4-2416-11eb-adc1-0242ac120002',
    '170' => '990a5058-2416-11eb-adc1-0242ac120002',
    '18' => 'a0e3adce-2416-11eb-adc1-0242ac120002',
    '16' => 'ae4a6cb8-80f1-11eb-8dcd-0242ac130003'
  ];

  /**
   * Returns hardcoded entity UUID for ID.
   * @param $entity_type
   *   Unused entity type.
   * @param $entityId
   *   Entity Id.
   */
  public function getEntityUUID($entity_type, $entityId) {
    if (isset(self::NODES[$entityId])) {
      return self::NODES[$entityId];
    }

    return FALSE;
  }
}

/**
 * @group Cohesion
 */
class _0030EntityUpdateUnitTest extends EntityUpdateUnitTestCase {

  /**
   * Entity Update.
   *
   * @var unit_0030MockUpdateEntity
   */
  protected $unit;

  /**
   * Layout json_value.
   *
   * @var string
   */
  private $fixture_layout = '{
    "canvas": [
        {
            "uid": "cpt_entity_reference_browser",
            "type": "component",
            "title": "Entity reference",
            "enabled": true,
            "category": "category-10",
            "componentId": "cpt_entity_reference_browser",
            "componentType": "entity-reference",
            "uuid": "c9b23ff7-cf8a-4166-8da3-10107f595177",
            "parentUid": "root",
            "isContainer": 0,
            "children": []
        },
        {
            "uid": "cpt_entity_browser_browser",
            "type": "component",
            "title": "Entity browser - Browser",
            "enabled": true,
            "category": "category-10",
            "componentId": "cpt_entity_browser_browser",
            "componentType": "entity-browser",
            "uuid": "ea7d3080-f960-4687-a334-d5e38ec94bb6",
            "parentUid": "root",
            "isContainer": 0,
            "children": []
        },
        {
            "uid": "cpt_entity_browser_typehead",
            "type": "component",
            "title": "Entity browser - Typehead",
            "enabled": true,
            "category": "category-10",
            "componentId": "cpt_entity_browser_typehead",
            "componentType": "entity-browser",
            "uuid": "c7890063-e4a8-4022-a2c1-79238a80b820",
            "parentUid": "root",
            "isContainer": 0,
            "children": []
        },
        {
            "type": "item",
            "uid": "entity-reference",
            "title": "Entity reference",
            "status": {
                "collapsed": true
            },
            "uuid": "d3fd3fc4-4759-4a2c-af1e-5337088b7691",
            "parentUid": "root",
            "isContainer": false,
            "children": []
        },
        {
            "type": "item",
            "uid": "entity-browser",
            "title": "Entity browser",
            "status": {
                "collapsed": true
            },
            "uuid": "f6a9e89d-7d5a-4e78-9787-cb502feee985",
            "parentUid": "root",
            "isContainer": false,
            "children": []
        },
        {
            "type": "item",
            "uid": "entity-browser",
            "title": "Entity browser",
            "status": {
                "collapsed": true
            },
            "uuid": "6c9786ab-d8bc-4f3d-86ce-fad23deb9a1b",
            "parentUid": "root",
            "isContainer": false,
            "children": []
        }
    ],
    "mapper": {
        "6c9786ab-d8bc-4f3d-86ce-fad23deb9a1b": {
            "settings": {
                "formDefinition": [
                    {
                        "formKey": "entity-browser-settings",
                        "children": [
                            {
                                "formKey": "entity-browser",
                                "breakpoints": [],
                                "activeFields": [
                                    {
                                        "name": "entity",
                                        "active": true
                                    },
                                    {
                                        "name": "entityViewMode",
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
            }
        },
        "f6a9e89d-7d5a-4e78-9787-cb502feee985": {
            "settings": {
                "formDefinition": [
                    {
                        "formKey": "entity-browser-settings",
                        "children": [
                            {
                                "formKey": "entity-browser",
                                "breakpoints": [],
                                "activeFields": [
                                    {
                                        "name": "entity",
                                        "active": true
                                    },
                                    {
                                        "name": "entityViewMode",
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
            }
        },
        "d3fd3fc4-4759-4a2c-af1e-5337088b7691": {}
    },
    "model": {
        "c9b23ff7-cf8a-4166-8da3-10107f595177": {
            "settings": {
                "title": "Entity reference"
            },
            "918a3914-19bf-4523-8095-b9446087ec57": {
                "entity_type": "node",
                "view_mode": "full",
                "entity": "32"
            }
        },
        "ea7d3080-f960-4687-a334-d5e38ec94bb6": {
            "settings": {
                "title": "Entity browser - Browser"
            },
            "a113268c-dffe-4eb8-913b-0f27f94b3a97": {
                "entity": {
                    "entityId": "173",
                    "entityUUID": "d6b02d5b-6b2e-4dc9-bc7c-e83c6cd86bc6",
                    "entityType": "media"
                }
            }
        },
        "c7890063-e4a8-4022-a2c1-79238a80b820": {
            "settings": {
                "title": "Entity browser - Typehead"
            },
            "52e45650-d91d-4f9e-8702-f2c04fa8e1f1": {
                "entity": {
                    "entityType": "media",
                    "entityId": "5"
                }
            }
        },
        "d3fd3fc4-4759-4a2c-af1e-5337088b7691": {
            "settings": {
                "title": "Entity reference",
                "entityReference": {
                    "entity_type": "node",
                    "view_mode": "full",
                    "entity": "40"
                }
            },
            "context-visibility": {
                "contextVisibility": {
                    "condition": "ALL"
                }
            },
            "styles": {
                "settings": {
                    "element": "entity-reference"
                }
            }
        },
        "f6a9e89d-7d5a-4e78-9787-cb502feee985": {
            "settings": {
                "title": "Entity browser",
                "entity": {
                    "entityBrowserType": "media_browser",
                    "entityType": "media",
                    "entity": {
                        "entityId": "170",
                        "entityUUID": "58176bf5-56ff-4d5f-9f80-33be2d0f37aa",
                        "entityType": "media"
                    }
                }
            },
            "context-visibility": {
                "contextVisibility": {
                    "condition": "ALL"
                }
            },
            "styles": {
                "settings": {
                    "element": "entity-browser"
                }
            }
        },
        "6c9786ab-d8bc-4f3d-86ce-fad23deb9a1b": {
            "settings": {
                "title": "Entity browser",
                "entity": {
                    "entityBrowserType": "typeahead",
                    "entityType": "media",
                    "entity": {
                        "entityType": "media",
                        "entityId": "18"
                    }
                }
            },
            "context-visibility": {
                "contextVisibility": {
                    "condition": "ALL"
                }
            },
            "styles": {
                "settings": {
                    "element": "entity-browser"
                }
            }
        },
        "ebd19dba-0de1-43ae-bb34-15ebdedd0f23": {
            "settings": {
                "title": "Instagram - post container"
            },
            "4cba6656-3f20-4b8c-8d8f-29436280b245": {
                "entity_type": "node",
                "view_mode": "list",
                "entity": {
                    "entityType": "node",
                    "entityId": "16"
               }
            }
        }
    },
    "previewModel": {
        "c9b23ff7-cf8a-4166-8da3-10107f595177": {},
        "ea7d3080-f960-4687-a334-d5e38ec94bb6": {},
        "c7890063-e4a8-4022-a2c1-79238a80b820": {},
        "d3fd3fc4-4759-4a2c-af1e-5337088b7691": {},
        "f6a9e89d-7d5a-4e78-9787-cb502feee985": {},
        "6c9786ab-d8bc-4f3d-86ce-fad23deb9a1b": {}
    },
    "variableFields": {
        "c9b23ff7-cf8a-4166-8da3-10107f595177": [],
        "ea7d3080-f960-4687-a334-d5e38ec94bb6": [],
        "c7890063-e4a8-4022-a2c1-79238a80b820": [],
        "d3fd3fc4-4759-4a2c-af1e-5337088b7691": [],
        "f6a9e89d-7d5a-4e78-9787-cb502feee985": [],
        "6c9786ab-d8bc-4f3d-86ce-fad23deb9a1b": []
    },
    "meta": {
        "fieldHistory": []
    }
  }';

  /**
   * {@inheritdoc}
   */
  public function setUp():void {
    $this->unit = new _0030EntityUpdateMock([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0030EntityUpdate::runUpdate
   */
  public function testRunUpdate() {

    // Entity reference in layout canvas.
    $layout = new _0030MockUpdateEntity($this->fixture_layout, TRUE);


    $this->assertionsLayoutCanvasBefore($layout->getDecodedJsonValues());
    $this->unit->runUpdate($layout);
    $this->assertionsLayoutCanvasAfter($layout->getDecodedJsonValues());
    $this->unit->runUpdate($layout);
    $this->assertionsLayoutCanvasAfter($layout->getDecodedJsonValues());
  }

  /**
   * Tests layout array before entity update.
   *
   * @param array $layout_array_before
   *   Layout array.
   */
  private function assertionsLayoutCanvasBefore(array $layout_array_before) {
    $this->assertEquals('32', $layout_array_before['model']['c9b23ff7-cf8a-4166-8da3-10107f595177']['918a3914-19bf-4523-8095-b9446087ec57']['entity'], 'entity id');
    $this->assertEquals('173', $layout_array_before['model']['ea7d3080-f960-4687-a334-d5e38ec94bb6']['a113268c-dffe-4eb8-913b-0f27f94b3a97']['entity']['entityId'], 'entity id');
    $this->assertEquals('5', $layout_array_before['model']['c7890063-e4a8-4022-a2c1-79238a80b820']['52e45650-d91d-4f9e-8702-f2c04fa8e1f1']['entity']['entityId'], 'entity id');
    $this->assertEquals('40', $layout_array_before['model']['d3fd3fc4-4759-4a2c-af1e-5337088b7691']['settings']['entityReference']['entity'], 'entity id');
    $this->assertEquals('170', $layout_array_before['model']['f6a9e89d-7d5a-4e78-9787-cb502feee985']['settings']['entity']['entity']['entityId'], 'entity id');
    $this->assertEquals('18', $layout_array_before['model']['6c9786ab-d8bc-4f3d-86ce-fad23deb9a1b']['settings']['entity']['entity']['entityId'], 'entity id');
    $this->assertEquals('16', $layout_array_before['model']['ebd19dba-0de1-43ae-bb34-15ebdedd0f23']['4cba6656-3f20-4b8c-8d8f-29436280b245']['entity']['entityId'], 'entity id');
  }

  /**
   * Tests layout array before entity update.
   *
   * @param array $layout_array_after
   *   Layout array.
   */
  private function assertionsLayoutCanvasAfter(array $layout_array_after) {
    $this->assertEquals($this->unit::NODES['32'], $layout_array_after['model']['c9b23ff7-cf8a-4166-8da3-10107f595177']['918a3914-19bf-4523-8095-b9446087ec57']['entity'], 'entity id');
    $this->assertEquals($this->unit::NODES['173'], $layout_array_after['model']['ea7d3080-f960-4687-a334-d5e38ec94bb6']['a113268c-dffe-4eb8-913b-0f27f94b3a97']['entity']['entityId'], 'entity id');
    $this->assertEquals($this->unit::NODES['5'], $layout_array_after['model']['c7890063-e4a8-4022-a2c1-79238a80b820']['52e45650-d91d-4f9e-8702-f2c04fa8e1f1']['entity']['entityId'], 'entity id');
    $this->assertEquals($this->unit::NODES['40'], $layout_array_after['model']['d3fd3fc4-4759-4a2c-af1e-5337088b7691']['settings']['entityReference']['entity'], 'entity id');
    $this->assertEquals($this->unit::NODES['170'], $layout_array_after['model']['f6a9e89d-7d5a-4e78-9787-cb502feee985']['settings']['entity']['entity']['entityId'], 'entity id');
    $this->assertEquals($this->unit::NODES['18'], $layout_array_after['model']['6c9786ab-d8bc-4f3d-86ce-fad23deb9a1b']['settings']['entity']['entity']['entityId'], 'entity id');
    $this->assertEquals($this->unit::NODES['16'], $layout_array_after['model']['ebd19dba-0de1-43ae-bb34-15ebdedd0f23']['4cba6656-3f20-4b8c-8d8f-29436280b245']['entity']['entityId'], 'entity id');
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown():void {
    unset($this->unit);
  }

}
