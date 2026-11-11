<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Plugin\EntityUpdate\_0025EntityUpdate;

/**
 * Class MockUpdateEntity.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class _0025MockEntity extends EntityMockBase {

  protected $jsonMapper;

  public function __construct($json_values, $isLayoutCanvas = FALSE, $json_mapper = NULL) {
    parent::__construct($json_values, $isLayoutCanvas);
    if (!is_null($json_mapper)) {
      $this->jsonMapper = $json_mapper;
    }
  }

  public function getDecodedJsonMapper($as_array = FALSE) {
    if ($as_array) {
      return json_decode($this->jsonMapper, TRUE);
    }
    else {
      return json_decode($this->jsonMapper);
    }
  }

  public function setJsonMapper($json_mapper) {
    $this->jsonMapper = $json_mapper;
  }

}

class _0025MockEntityUpdate extends _0025EntityUpdate {

  private $uuid_iterator = -1;

  private $uuids = [
    '350c43f3-883a-46c1-a0c2-b9840c4850a5',
    '76c5b0c2-3b6b-426a-aacd-401d70e2a08c',
    'e1a684be-5a94-4a2e-b48b-313f935b0514',
    '576e69b3-7501-45c9-aa8b-914f6967fa0c',
    'a39a774b-4187-4014-b878-e3f0c8de216b',
    '6d05c31c-7a0a-4464-9dd6-abecbef9f9a1',
    'd794598a-448a-4e9b-9ebc-9ddb45cecf5c',
    '6a6c6855-817a-417b-aaab-b42e3a909974',
    'e9a2b9dd-b370-46c6-923a-8b4b0965dccc',
    '2440d1ef-73fe-4c9c-9aac-90e814b6f94a',
    'b0ed739a-eb2f-4f18-b730-90bd68cea31e',
    '11a57964-ff75-4371-a373-aaa1f49d753a',
    'a6197f67-2459-4a5a-a6cb-d9e13c321da2',
    '94e8ab7b-ff50-46c3-926a-74f46a0835c6',
    'cb34a0a1-9d6c-4a9e-8756-6b0b64d81314',
    '69c6e594-810b-42c2-94ed-e42857a3f969',
    '43cb5162-fec1-4434-9ba7-031ff959f0aa',
    '90e3eafb-ec39-487c-9f5e-0983200ffdc5',
    'a361455a-4168-4ca0-84c4-14999193d150',
    'b95b04ce-72ef-4231-87a6-fe19d69d7712',
    'b61e4026-b2ff-46f0-9cbd-2a42926f9d35',
    '8f815034-6e17-413d-85cc-d58c1b892378',
    '0af82dd2-ee5c-4669-bed3-62e31ae49156',
    '597bc58d-3b3d-40f6-a0c2-32e747703911',
    '4bab51ff-55b8-4862-bffa-c391f4bda9b9',
    '9d3acbd2-664f-423c-b81b-136578830c4a',
    '1e6aea38-e1d6-490f-be67-eafad14a18cd',
    'c77baf3f-54c1-48a3-8e4b-b28ab94edd15',
    '34f48fa4-cd3d-447b-ab0c-d6987a4e7f2a',
    '9b45ffe3-4c57-4456-b82e-5c6fa09817a9',
  ];

  public function generateUUID() {
    $this->uuid_iterator++;
    return $this->uuids[$this->uuid_iterator];
  }

}

/**
 * @group Cohesion
 */
class _0025EntityUpdateUnitTest extends EntityUpdateUnitTestCase {

  /**
   * @var unit\Drupal\cohesion\Plugin\EntityUpdate\_0025EntityUpdate*/
  protected $unit;

  private $fixture_component = '{ "model": { "093be72e-486c-43b0-affe-d66b1e617634": { "settings": { "width": "fluid" } }, "7c43da5f-3243-4721-9d8a-a17d5a4d56c6": { "settings": { "title": "Row for columns", "styles": { "xl": { "bleed": "retain", "targetContainer": "inner", "overflow": "visible", "jsSettings": { "matchHeightRow": { "targetElement": "none" } } } }, "customStyle": [ { "customStyle": "" } ], "targetContainer": "inner" }, "context-visibility": { "contextVisibility": { "condition": "ALL" }, "contextAttributes": [ { "context": "context:amp_theme_active" } ] }, "styles": { "settings": { "element": "row-for-columns" } }, "markup": { "classes": "class" }, "animateonview": { "group": "BouncingExits", "animation": "bounceOut" }, "seo": { "itemTypeValue": "ss", "itemPropValue": "dd" }, "analytics": { "eventAttributes": [ { "trigger": "click", "eventCategory": "ss", "eventAction": "dd" } ], "dataLayerAttributes": [] }, "hideNoData": { "hideEnable": true, "hideData": "qw" } }, "5d7f4bdf-6f67-4a6f-bf06-f8d099ba3b65": { "settings": { "styles": { "xl": { "col": -2, "pull": -1, "push": -1, "offset": -1 } } } }, "75fc200c-5741-49dc-9913-34db49a9acde": { "settings": { "styles": { "xl": { "col": -2, "pull": -1, "push": -1, "offset": -1 } } } }, "c7a6e84d-4f7e-4dae-a9e6-849e569033e8": { "settings": { "element": "h1" } }, "fa90b640-36f8-4842-a69e-7b43321b1b35": { "settings": { "title": "Paragraph", "customStyle": [ { "customStyle": "" } ] }, "context-visibility": { "contextVisibility": { "condition": "ALL" } }, "styles": { "settings": { "element": "p" }, "styles": { "xl": { "font-weight": { "value": "300" } }, "pseudos": [ { "pseudos": [ null, null, { "styles": { "xl": { "clearfix": { "value": false } } } } ] } ] }, "pseudos": [ { "settings": { "element": "", "class": "", "combinator": "", "pseudo": ":active" }, "styles": { "xl": { "min-height": { "value": "11" } } }, "children": [ { "settings": { "element": "ee", "class": "", "combinator": "", "pseudo": "" }, "styles": { "xl": { "font-size": { "value": "[Field 2]" } } }, "children": [], "pseudos": [], "modifiers": [], "prefix": [] } ], "pseudos": [ { "settings": { "element": "", "class": "", "combinator": "", "pseudo": ":before" }, "styles": { "xl": { "margin": { "margin-top": { "value": "33" } } } }, "children": [], "pseudos": [], "modifiers": [], "prefix": [] }, { "settings": { "element": "", "class": "", "combinator": "", "pseudo": ":last-child" }, "styles": { "xl": { "margin": { "margin-top": { "value": "44" } } } }, "children": [], "pseudos": [], "modifiers": [], "prefix": [] }, { "settings": { "element": "", "class": "", "combinator": "", "pseudo": ":as" }, "styles": { "xl": { "clearfix": { "value": false }, "min-height": { "value": "44" }, "max-height": { "value": "[Field 1]" } } }, "children": [], "pseudos": [], "modifiers": [], "prefix": [] } ], "modifiers": [], "prefix": [] } ], "children": [ { "settings": { "element": "sd", "class": "", "combinator": "", "pseudo": "" }, "styles": { "xl": { "min-width": { "value": "22" } } }, "children": [], "pseudos": [], "modifiers": [], "prefix": [] } ] } }, "0228b0a0-d3c4-46fc-9ce4-32cb4b64674d": { "settings": { "title": "Input", "schema": { "type": "string" } } }, "0a7e17d8-6803-42f2-ba2f-bb4c78ad265e": { "settings": { "title": "Input", "schema": { "type": "string" } } } }, "mapper": { "093be72e-486c-43b0-affe-d66b1e617634": {}, "7c43da5f-3243-4721-9d8a-a17d5a4d56c6": { "settings": { "topLevel": { "formDefinition": [ { "formKey": "row-for-columns-settings", "children": [ { "formKey": "row-for-columns-bleed", "breakpoints": [ { "name": "xl" } ], "activeFields": [ { "name": "bleed", "active": true }, { "name": "overflow", "active": true } ] }, { "formKey": "row-for-columns-markup-style-target", "breakpoints": [], "activeFields": [ { "name": "targetContainer", "active": true } ] }, { "formKey": "row-for-columns-style", "breakpoints": [], "activeFields": [ { "name": "customStyle", "active": true }, { "name": "customStyle", "active": true } ] } ] }, { "formKey": "row-for-columns-js-settings", "children": [ { "formKey": "row-for-columns-match-height", "breakpoints": [ { "name": "xl" } ], "activeFields": [ { "name": "targetElement", "active": true }, { "name": "class", "active": true }, { "name": "targetLevel", "active": true } ] } ] } ], "selectorType": "topLevel", "form": null }, "dropzone": [], "selectorType": "topLevel" }, "markup": { "topLevel": { "formDefinition": [ { "formKey": "tab-markup-classes-and-ids", "children": [ { "formKey": "tab-markup-add-classes", "breakpoints": [], "activeFields": [ { "name": "classes", "active": true } ] } ] } ], "title": "Markup", "selectorType": "topLevel", "form": null }, "dropzone": [], "title": "Markup", "selectorType": "topLevel" }, "animateonview": { "topLevel": { "formDefinition": [ { "formKey": "tab-animateonview-animation", "children": [ { "formKey": "tab-animateonview-animation-attributes", "breakpoints": [], "activeFields": [ { "name": "group", "active": true }, { "name": "animation", "active": true }, { "name": "animation", "active": true }, { "name": "animation", "active": true }, { "name": "animation", "active": true }, { "name": "animation", "active": true }, { "name": "animation", "active": true }, { "name": "animation", "active": true }, { "name": "animation", "active": true }, { "name": "animation", "active": true }, { "name": "animation", "active": true }, { "name": "animation", "active": true }, { "name": "animation", "active": true }, { "name": "animation", "active": true }, { "name": "animation", "active": true }, { "name": "duration", "active": true }, { "name": "delay", "active": true }, { "name": "offset", "active": true }, { "name": "iteration", "active": true } ] } ] } ], "title": "Animate on view", "selectorType": "topLevel", "form": null }, "dropzone": [], "title": "Animate on view", "selectorType": "topLevel" }, "seo": { "topLevel": { "formDefinition": [ { "formKey": "tab-seo-schema", "children": [ { "formKey": "tab-seo-add-schema", "breakpoints": [], "activeFields": [ { "name": "itemTypeValue", "active": true }, { "name": "itemPropValue", "active": true } ] } ] } ], "title": "SEO", "selectorType": "topLevel", "form": null }, "dropzone": [], "title": "SEO", "selectorType": "topLevel" }, "analytics": { "topLevel": { "formDefinition": [ { "formKey": "tab-analytics-events", "children": [ { "formKey": "tab-analytics-event-attributes", "breakpoints": [], "activeFields": [ { "name": "eventAttributes", "active": true } ] } ] }, { "formKey": "tab-analytics-data-layer", "children": [ { "formKey": "tab-analytics-data-layer-attributes", "breakpoints": [], "activeFields": [ { "name": "dataLayerAttributes", "active": true } ] } ] } ], "title": "Analytics", "selectorType": "topLevel", "form": null }, "dropzone": [], "title": "Analytics", "selectorType": "topLevel" }, "hideNoData": { "topLevel": { "formDefinition": [ { "formKey": "tab-hide-data-settings", "children": [ { "formKey": "tab-hide-data-hide", "breakpoints": [], "activeFields": [ { "name": "hideEnable", "active": true }, { "name": "hideData", "active": true } ] } ] } ], "title": "Hide if no data", "selectorType": "topLevel", "form": null }, "dropzone": [], "title": "Hide if no data", "selectorType": "topLevel" }, "context-visibility": { "topLevel": { "formDefinition": [ { "formKey": "tab-context-visibility-context", "children": [ { "formKey": "tab-context-visibility-add-contexts", "breakpoints": [], "activeFields": [ { "name": "contextAttributes", "active": true } ] }, { "formKey": "tab-context-visibility-pass-condition", "breakpoints": [], "activeFields": [ { "name": "condition", "active": true } ] } ] } ], "title": "Context visibility", "selectorType": "topLevel", "form": null }, "dropzone": [], "title": "Context visibility", "selectorType": "topLevel" } }, "5d7f4bdf-6f67-4a6f-bf06-f8d099ba3b65": {}, "75fc200c-5741-49dc-9913-34db49a9acde": {}, "fa90b640-36f8-4842-a69e-7b43321b1b35": { "settings": { "topLevel": { "formDefinition": [ { "formKey": "paragraph-settings", "children": [ { "formKey": "paragraph-paragraph", "breakpoints": [], "activeFields": [ { "name": "content", "active": true } ] }, { "formKey": "paragraph-style", "breakpoints": [], "activeFields": [ { "name": "customStyle", "active": true }, { "name": "customStyle", "active": true } ] } ] } ], "selectorType": "topLevel", "form": null }, "dropzone": [], "selectorType": "topLevel" }, "styles": { "topLevel": { "formDefinition": [ { "formKey": "font", "children": [ { "formKey": "font-and-color", "breakpoints": [ { "name": "xl" } ], "activeFields": [ { "name": "font-family", "active": true }, { "name": "font-weight", "active": true }, { "name": "color", "active": true } ] } ] } ], "selectorType": "topLevel", "form": null }, "dropzone": [ { "title": ":active", "type": "container", "items": [ { "title": ":before", "type": "container", "items": [], "form": null, "selectorType": "pseudo", "oldModelKey": [ "pseudos", 0, "pseudos", 0 ], "prevKey": [ "pseudos", 0, "pseudos", 0 ], "allowedTypes": [ "child", "pseudo" ], "formDefinition": [ { "formKey": "layout", "children": [ { "formKey": "margin", "breakpoints": [ { "name": "xl" } ], "activeFields": [ { "name": "margin-equal", "active": false }, { "name": "margin-top", "active": true }, { "name": "margin-bottom", "active": true }, { "name": "margin-left", "active": true }, { "name": "margin-right", "active": true } ] } ] } ] }, { "title": ":last-child", "type": "container", "items": [], "form": null, "selectorType": "pseudo", "oldModelKey": [ "pseudos", 0, "pseudos", 1 ], "prevKey": [ "pseudos", 0, "pseudos", 1 ], "allowedTypes": [ "child", "pseudo" ], "formDefinition": [ { "formKey": "layout", "children": [ { "formKey": "margin", "breakpoints": [ { "name": "xl" } ], "activeFields": [ { "name": "margin-equal", "active": false }, { "name": "margin-top", "active": true }, { "name": "margin-bottom", "active": true }, { "name": "margin-left", "active": true }, { "name": "margin-right", "active": true } ] } ] } ] }, { "title": ":as", "type": "container", "items": [], "form": null, "selectorType": "pseudo", "subType": "custom-pseudo", "model": ":as", "oldModelKey": [ "pseudos", 0, "pseudos", 2 ], "prevKey": [ "pseudos", 0, "pseudos", 2 ], "allowedTypes": [ "child", "pseudo" ], "formDefinition": [ { "formKey": "layout", "children": [ { "formKey": "float", "breakpoints": [ { "name": "xl" } ], "activeFields": [ { "name": "float", "active": true }, { "name": "clear", "active": true }, { "name": "clearfix", "active": true } ] }, { "formKey": "height", "breakpoints": [ { "name": "xl" } ], "activeFields": [ { "name": "min-height", "active": true }, { "name": "max-height", "active": true }, { "name": "height", "active": true } ] } ] } ] }, { "title": "ee", "type": "container", "items": [], "form": null, "selectorType": "child", "model": "ee", "oldModelKey": [ "pseudos", 0, "children", 0 ], "prevKey": [ "pseudos", 0, "children", 0 ], "allowedTypes": [ "child", "pseudo", "modifier" ], "formDefinition": [ { "formKey": "font", "children": [ { "formKey": "font-sizing-and-alignment", "breakpoints": [ { "name": "xl" } ], "activeFields": [ { "name": "font-size", "active": true }, { "name": "line-height", "active": true }, { "name": "text-align", "active": true }, { "name": "letter-spacing", "active": true }, { "name": "word-spacing", "active": true }, { "name": "text-indent", "active": true } ] } ] } ] } ], "form": null, "selectorType": "pseudo", "prevKey": [ "pseudos", 0 ], "allowedTypes": [ "child", "pseudo" ], "formDefinition": [ { "formKey": "layout", "children": [ { "formKey": "height", "breakpoints": [ { "name": "xl" } ], "activeFields": [ { "name": "min-height", "active": true }, { "name": "max-height", "active": true }, { "name": "height", "active": true } ] } ] } ] }, { "title": "sd", "type": "container", "items": [], "form": null, "selectorType": "child", "model": "sd", "prevKey": [ "children", 0 ], "allowedTypes": [ "child", "pseudo", "modifier" ], "formDefinition": [ { "formKey": "layout", "children": [ { "formKey": "width", "breakpoints": [ { "name": "xl" } ], "activeFields": [ { "name": "min-width", "active": true }, { "name": "max-width", "active": true }, { "name": "width", "active": true } ] } ] } ] } ] } }, "c7a6e84d-4f7e-4dae-a9e6-849e569033e8": {} }, "previewModel": { "7c43da5f-3243-4721-9d8a-a17d5a4d56c6": {}, "fa90b640-36f8-4842-a69e-7b43321b1b35": { "styles": { "pseudos": [ { "pseudos": [ null, null, { "styles": { "xl": { "max-height": { "value": "99" } } } } ], "children": [ { "styles": { "xl": { "font-size": { "value": "45" } } } } ] } ] } } }, "variableFields": { "7c43da5f-3243-4721-9d8a-a17d5a4d56c6": [], "fa90b640-36f8-4842-a69e-7b43321b1b35": [ "styles.pseudos.0.pseudos.2.styles.xl.max-height.value", "styles.pseudos.0.children.0.styles.xl.font-size.value" ] }, "meta": {}, "canvas": [ { "type": "container", "uid": "container", "title": "Container", "status": { "collapsed": false }, "children": [ { "type": "container", "uid": "row-for-columns", "title": "Row for columns", "status": { "collapsed": false }, "children": [ { "type": "container", "uid": "column", "title": "Column", "status": { "collapsed": false }, "children": [ { "type": "container", "uid": "paragraph", "title": "Paragraph", "status": { "collapsed": true }, "children": [], "uuid": "fa90b640-36f8-4842-a69e-7b43321b1b35", "parentUid": "column", "isContainer": true } ], "breakpointClasses": "coh-layout-col-xl coh-layout-column-width", "uuid": "5d7f4bdf-6f67-4a6f-bf06-f8d099ba3b65", "parentUid": "row-for-columns", "isContainer": true }, { "type": "container", "uid": "column", "title": "Column", "status": { "collapsed": false }, "children": [ { "type": "container", "uid": "heading", "title": "Heading", "status": { "collapsed": true }, "children": [], "uuid": "c7a6e84d-4f7e-4dae-a9e6-849e569033e8", "parentUid": "column", "isContainer": true } ], "breakpointClasses": "coh-layout-col-xl coh-layout-column-width", "uuid": "75fc200c-5741-49dc-9913-34db49a9acde", "parentUid": "row-for-columns", "isContainer": true } ], "uuid": "7c43da5f-3243-4721-9d8a-a17d5a4d56c6", "parentUid": "container", "isContainer": true } ], "uuid": "093be72e-486c-43b0-affe-d66b1e617634", "parentUid": "root", "isContainer": true } ], "componentForm": [ { "type": "form-field", "uid": "form-input", "title": "Input", "status": { "collapsed": false }, "uuid": "0228b0a0-d3c4-46fc-9ce4-32cb4b64674d", "parentUid": "root", "isContainer": false }, { "type": "form-field", "uid": "form-input", "title": "Input", "status": { "collapsed": false }, "uuid": "0a7e17d8-6803-42f2-ba2f-bb4c78ad265e", "parentUid": "root", "isContainer": false } ] }';

  private $fixture_style_json_values = '{ "preview": { "text": "<div class=\"coh-preview\">Default content for \'Generic\'.</div>\n", "textFormat": "cohesion" }, "sBackgroundColour": "#ffffff", "styles": { "settings": { "element": "div", "class": "", "combinator": "", "pseudo": "" }, "styles": { "xl": { "padding": { "padding-top": { "value": "22" }, "padding-bottom": { "value": "[token:token]" } } } }, "pseudos": [ { "settings": { "element": "", "class": "", "combinator": "", "pseudo": ":active" }, "styles": { "xl": { "font-weight": { "value": "900" } } }, "children": [ { "settings": { "element": "sdsd", "class": "", "combinator": "", "pseudo": "" }, "styles": { "xl": { "font-size": { "value": "22" } } }, "children": [], "pseudos": [], "modifiers": [], "prefix": [] } ], "pseudos": [ { "settings": { "element": "", "class": "", "combinator": "", "pseudo": ":focus" }, "styles": { "xl": { "font-family": { "value": "$coh-font-roboto" }, "color": { "value": { "value": { "hex": "#28a9e0", "rgba": "rgba(40, 169, 224, 1)" }, "name": "Color 1", "uid": "color-1", "class": ".coh-color-color-1", "variable": "$coh-color-color-1", "wysiwyg": true, "inuse": true } } } }, "children": [], "pseudos": [], "modifiers": [], "prefix": [] }, { "settings": { "element": "", "class": "", "combinator": "", "pseudo": ":first-child" }, "styles": { "xl": { "margin": { "margin-top": { "value": "344" } } } }, "children": [], "pseudos": [ { "settings": { "element": "", "class": "", "combinator": "", "pseudo": ":ss" }, "styles": { "xl": { "background-color": { "value": { "value": { "hex": "#000000", "rgba": "rgba(0, 0, 0, 1)" }, "name": "Black", "uid": "black", "class": ".coh-color-black", "variable": "$coh-color-black", "wysiwyg": true, "inuse": true } } } }, "children": [], "pseudos": [], "modifiers": [], "prefix": [] } ], "modifiers": [], "prefix": [] } ], "modifiers": [], "prefix": [] } ], "modifiers": [ { "settings": { "element": "", "class": ".cc", "combinator": "", "pseudo": "" }, "styles": { "xl": { "min-height": { "value": "21" } } }, "children": [], "pseudos": [], "modifiers": [], "prefix": [] } ], "children": [ { "settings": { "element": "dd", "class": "", "combinator": "", "pseudo": "" }, "styles": { "xl": { "padding": { "padding-bottom": { "value": "66" }, "padding-left": { "value": "77" } } } }, "children": [], "pseudos": [], "modifiers": [], "prefix": [] } ] } }';
  private $fixture_style_json_mapper = '{ "styles": { "topLevel": { "title": "Layout", "selectorType": "topLevel", "formDefinition": [ { "formKey": "layout", "children": [ { "formKey": "margin", "breakpoints": [ { "name": "xl" } ], "activeFields": [ { "name": "margin", "active": true }, { "name": "margin-top", "active": true }, { "name": "margin-bottom", "active": true }, { "name": "margin-left", "active": true }, { "name": "margin-right", "active": true } ], "form": { "type": "cohSection", "title": "Margin", "formKey": "margin", "helpKey": "css-layout-margin", "weight": 4, "breakpoints": true, "items": [ { "type": "cohTextBox", "key": "margin.margin-equal.value", "title": "Margin equal", "isStyle": true, "defaultActive": false, "schema": { "type": "string", "cohValidate": [ "cssLengthWithAuto", "scssVariable" ] } }, { "type": "cohTextBox", "key": "margin.margin-top.value", "title": "Margin top", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssLengthWithAuto", "scssVariable" ] } }, { "type": "cohTextBox", "key": "margin.margin-bottom.value", "title": "Margin bottom", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssLengthWithAuto", "scssVariable" ] } }, { "type": "cohTextBox", "key": "margin.margin-left.value", "title": "Margin left", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssLengthWithAuto", "scssVariable" ] } }, { "type": "cohTextBox", "key": "margin.margin-right.value", "title": "Margin right", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssLengthWithAuto", "scssVariable" ] } } ] } }, { "formKey": "padding", "breakpoints": [ { "name": "xl" } ], "activeFields": [ { "name": "padding", "active": true }, { "name": "padding-top", "active": true }, { "name": "padding-bottom", "active": true }, { "name": "padding-left", "active": true }, { "name": "padding-right", "active": true } ], "form": { "type": "cohSection", "title": "Padding", "formKey": "padding", "helpKey": "css-layout-padding", "weight": 3, "breakpoints": true, "items": [ { "type": "cohTextBox", "key": "padding.padding-equal.value", "title": "Padding equal", "isStyle": true, "defaultActive": false, "schema": { "type": "string", "cohValidate": [ "cssPositiveLength", "scssVariable" ] } }, { "type": "cohTextBox", "key": "padding.padding-top.value", "title": "Padding top", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssPositiveLength", "scssVariable" ] } }, { "type": "cohTextBox", "key": "padding.padding-bottom.value", "title": "Padding bottom", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssPositiveLength", "scssVariable" ] } }, { "type": "cohTextBox", "key": "padding.padding-left.value", "title": "Padding left", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssPositiveLength", "scssVariable" ] } }, { "type": "cohTextBox", "key": "padding.padding-right.value", "title": "Padding right", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssPositiveLength", "scssVariable" ] } } ] } } ], "form": { "type": "cohAccordion", "htmlClass": "coh-accordion-panel-body--bleed coh-accordion-panel-body--dark", "title": "Layout", "weight": 3, "formKey": "layout", "isOpen": true, "options": [ "height", "width", "padding", "margin", "float", "position", "display", "opacity", "vertical-alignment", "multi-column-layout", "flex-container", "flex-item", "box-shadow" ], "items": [ { "type": "cohSection", "title": "Margin", "formKey": "margin", "helpKey": "css-layout-margin", "weight": 4, "breakpoints": true, "items": [ { "type": "fieldsetArray", "items": [ { "key": [ "xl" ], "title": "xl", "type": "cohBreakpointFieldset", "items": [ { "type": "cohTextBox", "key": [ "styles", "styles", "xl", "margin", "margin-equal", "value" ], "title": "Margin equal", "isStyle": true, "defaultActive": false, "schema": { "type": "string", "cohValidate": [ "cssLengthWithAuto", "scssVariable" ] }, "originalKeyValue": [ "styles", "xl", "margin", "margin-equal", "value" ], "isActive": false }, { "type": "cohTextBox", "key": [ "styles", "styles", "xl", "margin", "margin-top", "value" ], "title": "Margin top", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssLengthWithAuto", "scssVariable" ] }, "isActive": true, "originalKeyValue": [ "styles", "xl", "margin", "margin-top", "value" ], "ngModelOptions": {}, "cohIdKey": "styles_5sd73itiud", "isVariableMode": false, "$validators": {} }, { "type": "cohTextBox", "key": [ "styles", "styles", "xl", "margin", "margin-bottom", "value" ], "title": "Margin bottom", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssLengthWithAuto", "scssVariable" ] }, "isActive": true, "originalKeyValue": [ "styles", "xl", "margin", "margin-bottom", "value" ], "ngModelOptions": {}, "cohIdKey": "styles_kroe9pv9x3k", "isVariableMode": false, "$validators": {} }, { "type": "cohTextBox", "key": [ "styles", "styles", "xl", "margin", "margin-left", "value" ], "title": "Margin left", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssLengthWithAuto", "scssVariable" ] }, "isActive": true, "originalKeyValue": [ "styles", "xl", "margin", "margin-left", "value" ], "ngModelOptions": {}, "cohIdKey": "styles_k4obyysmei", "isVariableMode": false, "$validators": {} }, { "type": "cohTextBox", "key": [ "styles", "styles", "xl", "margin", "margin-right", "value" ], "title": "Margin right", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssLengthWithAuto", "scssVariable" ] }, "isActive": true, "originalKeyValue": [ "styles", "xl", "margin", "margin-right", "value" ], "ngModelOptions": {}, "cohIdKey": "styles_2a3p5emb9q", "isVariableMode": false, "$validators": {} } ], "ngModelOptions": {} } ] } ], "mapperKey": "styles", "currentBreakpoints": [ { "iconName": "television", "name": "xl" } ], "ngModelOptions": {} }, { "type": "cohSection", "title": "Padding", "formKey": "padding", "helpKey": "css-layout-padding", "weight": 3, "breakpoints": true, "items": [ { "type": "fieldsetArray", "items": [ { "key": [ "xl" ], "title": "xl", "type": "cohBreakpointFieldset", "items": [ { "type": "cohTextBox", "key": [ "styles", "styles", "xl", "padding", "padding-equal", "value" ], "title": "Padding equal", "isStyle": true, "defaultActive": false, "schema": { "type": "string", "cohValidate": [ "cssPositiveLength", "scssVariable" ] }, "originalKeyValue": [ "styles", "xl", "padding", "padding-equal", "value" ], "isActive": false }, { "type": "cohTextBox", "key": [ "styles", "styles", "xl", "padding", "padding-top", "value" ], "title": "Padding top", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssPositiveLength", "scssVariable" ] }, "isActive": true, "originalKeyValue": [ "styles", "xl", "padding", "padding-top", "value" ], "ngModelOptions": {}, "cohIdKey": "styles_h79tgwzh1r", "isVariableMode": false, "$validators": {}, "validationMessage": { "regxValidator": "Invalid variable" } }, { "type": "cohTextBox", "key": [ "styles", "styles", "xl", "padding", "padding-bottom", "value" ], "title": "Padding bottom", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssPositiveLength", "scssVariable" ] }, "isActive": true, "originalKeyValue": [ "styles", "xl", "padding", "padding-bottom", "value" ], "ngModelOptions": {}, "cohIdKey": "styles_3y5pixru6o", "isVariableMode": false, "required": false, "$validators": {}, "validationMessage": { "regxValidator": "Invalid variable" } }, { "type": "cohTextBox", "key": [ "styles", "styles", "xl", "padding", "padding-left", "value" ], "title": "Padding left", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssPositiveLength", "scssVariable" ] }, "isActive": true, "originalKeyValue": [ "styles", "xl", "padding", "padding-left", "value" ], "ngModelOptions": {}, "cohIdKey": "styles_g6b3nggqb4", "isVariableMode": false, "$validators": {} }, { "type": "cohTextBox", "key": [ "styles", "styles", "xl", "padding", "padding-right", "value" ], "title": "Padding right", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssPositiveLength", "scssVariable" ] }, "isActive": true, "originalKeyValue": [ "styles", "xl", "padding", "padding-right", "value" ], "ngModelOptions": {}, "cohIdKey": "styles_zee114i6ah", "isVariableMode": false, "$validators": {} } ], "ngModelOptions": {} } ] } ], "mapperKey": "styles", "currentBreakpoints": [ { "iconName": "television", "name": "xl" } ], "ngModelOptions": {} } ], "ngModelOptions": {} } } ], "form": { "type": "cohFormBuilder", "element": "div", "currentLevel": "div", "defaultKey": "custom-styles-layout", "items": [ { "type": "cohAccordion", "htmlClass": "coh-accordion-panel-body--bleed coh-accordion-panel-body--dark", "title": "Layout", "weight": 3, "formKey": "layout", "isOpen": true, "options": [ "height", "width", "padding", "margin", "float", "position", "display", "opacity", "vertical-alignment", "multi-column-layout", "flex-container", "flex-item", "box-shadow" ], "items": [ { "type": "cohSection", "title": "Margin", "formKey": "margin", "helpKey": "css-layout-margin", "weight": 4, "breakpoints": true, "items": [ { "type": "fieldsetArray", "items": [ { "key": [ "xl" ], "title": "xl", "type": "cohBreakpointFieldset", "items": [ { "type": "cohTextBox", "key": [ "styles", "styles", "xl", "margin", "margin-equal", "value" ], "title": "Margin equal", "isStyle": true, "defaultActive": false, "schema": { "type": "string", "cohValidate": [ "cssLengthWithAuto", "scssVariable" ] }, "originalKeyValue": [ "styles", "xl", "margin", "margin-equal", "value" ], "isActive": false }, { "type": "cohTextBox", "key": [ "styles", "styles", "xl", "margin", "margin-top", "value" ], "title": "Margin top", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssLengthWithAuto", "scssVariable" ] }, "isActive": true, "originalKeyValue": [ "styles", "xl", "margin", "margin-top", "value" ], "ngModelOptions": {}, "cohIdKey": "styles_5sd73itiud", "isVariableMode": false, "$validators": {} }, { "type": "cohTextBox", "key": [ "styles", "styles", "xl", "margin", "margin-bottom", "value" ], "title": "Margin bottom", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssLengthWithAuto", "scssVariable" ] }, "isActive": true, "originalKeyValue": [ "styles", "xl", "margin", "margin-bottom", "value" ], "ngModelOptions": {}, "cohIdKey": "styles_kroe9pv9x3k", "isVariableMode": false, "$validators": {} }, { "type": "cohTextBox", "key": [ "styles", "styles", "xl", "margin", "margin-left", "value" ], "title": "Margin left", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssLengthWithAuto", "scssVariable" ] }, "isActive": true, "originalKeyValue": [ "styles", "xl", "margin", "margin-left", "value" ], "ngModelOptions": {}, "cohIdKey": "styles_k4obyysmei", "isVariableMode": false, "$validators": {} }, { "type": "cohTextBox", "key": [ "styles", "styles", "xl", "margin", "margin-right", "value" ], "title": "Margin right", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssLengthWithAuto", "scssVariable" ] }, "isActive": true, "originalKeyValue": [ "styles", "xl", "margin", "margin-right", "value" ], "ngModelOptions": {}, "cohIdKey": "styles_2a3p5emb9q", "isVariableMode": false, "$validators": {} } ], "ngModelOptions": {} } ] } ], "mapperKey": "styles", "currentBreakpoints": [ { "iconName": "television", "name": "xl" } ], "ngModelOptions": {} }, { "type": "cohSection", "title": "Padding", "formKey": "padding", "helpKey": "css-layout-padding", "weight": 3, "breakpoints": true, "items": [ { "type": "fieldsetArray", "items": [ { "key": [ "xl" ], "title": "xl", "type": "cohBreakpointFieldset", "items": [ { "type": "cohTextBox", "key": [ "styles", "styles", "xl", "padding", "padding-equal", "value" ], "title": "Padding equal", "isStyle": true, "defaultActive": false, "schema": { "type": "string", "cohValidate": [ "cssPositiveLength", "scssVariable" ] }, "originalKeyValue": [ "styles", "xl", "padding", "padding-equal", "value" ], "isActive": false }, { "type": "cohTextBox", "key": [ "styles", "styles", "xl", "padding", "padding-top", "value" ], "title": "Padding top", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssPositiveLength", "scssVariable" ] }, "isActive": true, "originalKeyValue": [ "styles", "xl", "padding", "padding-top", "value" ], "ngModelOptions": {}, "cohIdKey": "styles_h79tgwzh1r", "isVariableMode": false, "$validators": {}, "validationMessage": { "regxValidator": "Invalid variable" } }, { "type": "cohTextBox", "key": [ "styles", "styles", "xl", "padding", "padding-bottom", "value" ], "title": "Padding bottom", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssPositiveLength", "scssVariable" ] }, "isActive": true, "originalKeyValue": [ "styles", "xl", "padding", "padding-bottom", "value" ], "ngModelOptions": {}, "cohIdKey": "styles_3y5pixru6o", "isVariableMode": false, "required": false, "$validators": {}, "validationMessage": { "regxValidator": "Invalid variable" } }, { "type": "cohTextBox", "key": [ "styles", "styles", "xl", "padding", "padding-left", "value" ], "title": "Padding left", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssPositiveLength", "scssVariable" ] }, "isActive": true, "originalKeyValue": [ "styles", "xl", "padding", "padding-left", "value" ], "ngModelOptions": {}, "cohIdKey": "styles_g6b3nggqb4", "isVariableMode": false, "$validators": {} }, { "type": "cohTextBox", "key": [ "styles", "styles", "xl", "padding", "padding-right", "value" ], "title": "Padding right", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssPositiveLength", "scssVariable" ] }, "isActive": true, "originalKeyValue": [ "styles", "xl", "padding", "padding-right", "value" ], "ngModelOptions": {}, "cohIdKey": "styles_zee114i6ah", "isVariableMode": false, "$validators": {} } ], "ngModelOptions": {} } ] } ], "mapperKey": "styles", "currentBreakpoints": [ { "iconName": "television", "name": "xl" } ], "ngModelOptions": {} } ], "ngModelOptions": {} } ], "ngModelOptions": {}, "isOpen": true, "mapperKey": "styles", "currentLevelTitle": "Default" } }, "dropzone": [ { "title": ":active", "type": "container", "items": [ { "title": ":focus", "type": "container", "items": [], "form": { "type": "cohFormBuilder", "element": "div", "currentLevel": { "prevKey": [ "pseudos", 0, "pseudos", 0 ] }, "defaultKey": "custom-styles-layout", "items": [ { "type": "cohAccordion", "htmlClass": "coh-accordion-panel-body--bleed coh-accordion-panel-body--dark", "title": "Font", "weight": 1, "formKey": "font", "isOpen": true, "options": [ "font-and-color", "font-sizing-and-alignment", "font-style-and-wrapping", "text-shadow" ], "items": [ { "type": "cohSection", "title": "Font and color", "formKey": "font-and-color", "helpKey": "css-font-and-color", "weight": 1, "breakpoints": true, "items": [ { "type": "fieldset", "items": [ { "key": [ "xl" ], "title": "xl", "type": "cohBreakpointFieldset", "items": [ { "type": "cohSelect", "key": [ "styles", "pseudos", 0, "pseudos", 0, "styles", "xl", "font-family", "value" ], "title": "Font family", "isStyle": true, "defaultActive": true, "valueProperty": "stack.variable", "labelProperty": "stack.name", "endpoint": "/cohesionapi/main/font_libraries", "schema": { "type": "string" }, "originalKeyValue": [ "font-family", "value" ], "breakpointName": "xl", "isActive": true, "ngModelOptions": {}, "loading": false, "cohIdKey": "styles_n9crf9epv8", "options": [ { "label": " ", "nullOption": true }, { "stack": { "inuse": true, "systemfont": false, "name": "Playfair Display", "uid": "playfair-display", "class": ".coh-font-playfair-display", "variable": "$coh-font-playfair-display", "fontStack": "\'Playfair Display\', serif", "smoothing": { "firefox": true, "webkit": true } }, "value": "$coh-font-playfair-display", "label": "Playfair Display" }, { "stack": { "inuse": true, "systemfont": false, "fontStack": "\'Roboto\', sans-serif", "name": "Roboto", "uid": "roboto", "class": ".coh-font-roboto", "variable": "$coh-font-roboto", "smoothing": { "firefox": true, "webkit": true } }, "value": "$coh-font-roboto", "label": "Roboto" } ], "isVariableMode": false }, { "type": "cohTextBox", "key": [ "styles", "pseudos", 0, "pseudos", 0, "styles", "xl", "font-weight", "value" ], "title": "Font weight", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssFontWeight", "scssVariable" ] }, "originalKeyValue": [ "font-weight", "value" ], "breakpointName": "xl", "isActive": true, "ngModelOptions": {}, "cohIdKey": "styles_1l5fmimb3k", "isVariableMode": false, "$validators": {} }, { "type": "cohColourPickerOpener", "key": [ "styles", "pseudos", 0, "pseudos", 0, "styles", "xl", "color", "value" ], "title": "Font color", "isStyle": true, "defaultActive": true, "schema": { "type": "object" }, "originalKeyValue": [ "color", "value" ], "breakpointName": "xl", "isActive": true, "ngModelOptions": {}, "cohIdKey": "styles_3bwn9l3lnzj", "isVariableMode": false, "colourPickerOptions": { "flat": true }, "options": { "preferredFormat": "hex" } } ], "ngModelOptions": {} } ] } ], "mapperKey": "styles", "currentBreakpoints": [ { "name": "xl", "iconName": "television" } ], "ngModelOptions": {} } ], "ngModelOptions": {} } ], "ngModelOptions": {}, "isOpen": true, "mapperKey": "styles", "currentLevelTitle": ":focus" }, "selectorType": "pseudo", "oldModelKey": [ "pseudos", 0, "pseudos", 0 ], "prevKey": [ "pseudos", 0, "pseudos", 0 ], "allowedTypes": [ "child", "pseudo" ] }, { "title": ":first-child", "type": "container", "items": [ { "title": ":ss", "type": "container", "items": [], "form": { "type": "cohFormBuilder", "element": "div", "currentLevel": { "prevKey": [ "pseudos", 0, "pseudos", 1, "pseudos", 0 ] }, "defaultKey": "custom-styles-layout", "items": [ { "type": "cohAccordion", "htmlClass": "coh-accordion-panel-body--bleed coh-accordion-panel-body--dark", "title": "Background", "formKey": "background", "weight": 2, "isOpen": true, "options": [ "background-image", "background-image-and-gradient" ], "items": [ { "type": "cohSection", "title": "Background color", "formKey": "background-color", "helpKey": "css-background-color", "weight": 1, "breakpoints": true, "items": [ { "type": "fieldset", "items": [ { "key": [ "xl" ], "title": "xl", "type": "cohBreakpointFieldset", "items": [ { "type": "cohColourPickerOpener", "key": [ "styles", "pseudos", "0", "pseudos", "1", "pseudos", "0", "styles", "xl", "background-color", "value" ], "title": "Color", "isStyle": true, "defaultActive": true, "schema": { "type": "object" }, "originalKeyValue": [ "background-color", "value" ], "breakpointName": "xl", "isActive": true, "ngModelOptions": {}, "cohIdKey": "styles_mfqphiduyw", "isVariableMode": false, "colourPickerOptions": { "flat": true }, "options": { "preferredFormat": "hex" } } ], "ngModelOptions": {} } ] } ], "mapperKey": "styles", "currentBreakpoints": [ { "name": "xl", "iconName": "television" } ], "ngModelOptions": {} } ], "ngModelOptions": {} } ], "ngModelOptions": {}, "isOpen": true, "mapperKey": "styles", "currentLevelTitle": ":ss" }, "selectorType": "pseudo", "subType": "custom-pseudo", "model": ":ss", "oldModelKey": [ "pseudos", 0, "pseudos", 1, "pseudos", 0 ], "prevKey": [ "pseudos", 0, "pseudos", 1, "pseudos", 0 ], "allowedTypes": [ "child", "pseudo" ] } ], "form": { "type": "cohFormBuilder", "element": "div", "currentLevel": { "prevKey": [ "pseudos", 0, "pseudos", 1 ] }, "defaultKey": "custom-styles-layout", "items": [ { "type": "cohAccordion", "htmlClass": "coh-accordion-panel-body--bleed coh-accordion-panel-body--dark", "title": "Layout", "weight": 3, "formKey": "layout", "isOpen": true, "options": [ "height", "width", "padding", "margin", "float", "position", "display", "opacity", "vertical-alignment", "multi-column-layout", "flex-container", "flex-item", "box-shadow" ], "items": [ { "type": "cohSection", "title": "Margin", "formKey": "margin", "helpKey": "css-layout-margin", "weight": 4, "breakpoints": true, "items": [ { "type": "fieldset", "items": [ { "key": [ "xl" ], "title": "xl", "type": "cohBreakpointFieldset", "items": [ { "type": "cohTextBox", "key": [ "styles", "pseudos", 0, "pseudos", 1, "styles", "xl", "margin", "margin-equal", "value" ], "title": "Margin equal", "isStyle": true, "defaultActive": false, "schema": { "type": "string", "cohValidate": [ "cssLengthWithAuto", "scssVariable" ] }, "originalKeyValue": [ "margin", "margin-equal", "value" ], "breakpointName": "xl", "isActive": false }, { "type": "cohTextBox", "key": [ "styles", "pseudos", 0, "pseudos", 1, "styles", "xl", "margin", "margin-top", "value" ], "title": "Margin top", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssLengthWithAuto", "scssVariable" ] }, "originalKeyValue": [ "margin", "margin-top", "value" ], "breakpointName": "xl", "isActive": true, "ngModelOptions": {}, "cohIdKey": "styles_9lkm29oeneh", "isVariableMode": false, "$validators": {}, "validationMessage": { "regxValidator": "Invalid variable" } }, { "type": "cohTextBox", "key": [ "styles", "pseudos", 0, "pseudos", 1, "styles", "xl", "margin", "margin-bottom", "value" ], "title": "Margin bottom", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssLengthWithAuto", "scssVariable" ] }, "originalKeyValue": [ "margin", "margin-bottom", "value" ], "breakpointName": "xl", "isActive": true, "ngModelOptions": {}, "cohIdKey": "styles_p5jyyaeywf", "isVariableMode": false, "$validators": {} }, { "type": "cohTextBox", "key": [ "styles", "pseudos", 0, "pseudos", 1, "styles", "xl", "margin", "margin-left", "value" ], "title": "Margin left", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssLengthWithAuto", "scssVariable" ] }, "originalKeyValue": [ "margin", "margin-left", "value" ], "breakpointName": "xl", "isActive": true, "ngModelOptions": {}, "cohIdKey": "styles_rtuo6p1op5k", "isVariableMode": false, "$validators": {} }, { "type": "cohTextBox", "key": [ "styles", "pseudos", 0, "pseudos", 1, "styles", "xl", "margin", "margin-right", "value" ], "title": "Margin right", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssLengthWithAuto", "scssVariable" ] }, "originalKeyValue": [ "margin", "margin-right", "value" ], "breakpointName": "xl", "isActive": true, "ngModelOptions": {}, "cohIdKey": "styles_zzxlf0mz4f", "isVariableMode": false, "$validators": {} } ], "ngModelOptions": {} } ] } ], "mapperKey": "styles", "currentBreakpoints": [ { "name": "xl", "iconName": "television" } ], "ngModelOptions": {} } ], "ngModelOptions": {} } ], "ngModelOptions": {}, "isOpen": true, "mapperKey": "styles", "currentLevelTitle": ":first-child" }, "selectorType": "pseudo", "oldModelKey": [ "pseudos", 0, "pseudos", 1 ], "prevKey": [ "pseudos", 0, "pseudos", 1 ], "allowedTypes": [ "child", "pseudo" ] }, { "title": "sdsd", "type": "container", "items": [], "form": { "type": "cohFormBuilder", "element": "div", "currentLevel": { "prevKey": [ "pseudos", 0, "children", 0 ] }, "defaultKey": "custom-styles-layout", "items": [ { "type": "cohAccordion", "htmlClass": "coh-accordion-panel-body--bleed coh-accordion-panel-body--dark", "title": "Font", "weight": 1, "formKey": "font", "isOpen": true, "options": [ "font-and-color", "font-sizing-and-alignment", "font-style-and-wrapping", "text-shadow" ], "items": [ { "type": "cohSection", "title": "Font sizing and alignment", "formKey": "font-sizing-and-alignment", "helpKey": "css-font-sizing-and-alignment", "weight": 2, "breakpoints": true, "items": [ { "type": "fieldset", "items": [ { "key": [ "xl" ], "title": "xl", "type": "cohBreakpointFieldset", "items": [ { "type": "cohTextBox", "key": [ "styles", "pseudos", 0, "children", 0, "styles", "xl", "font-size", "value" ], "title": "Font size", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssPositiveLengthWithFontSize", "scssVariable" ] }, "originalKeyValue": [ "font-size", "value" ], "breakpointName": "xl", "isActive": true, "ngModelOptions": {}, "cohIdKey": "styles_azxlf4axlj", "isVariableMode": false, "$validators": {}, "validationMessage": { "regxValidator": "Invalid variable" } }, { "type": "cohTextBox", "key": [ "styles", "pseudos", 0, "children", 0, "styles", "xl", "line-height", "value" ], "title": "Line height", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssPositiveLengthWithNormal", "scssVariable" ] }, "originalKeyValue": [ "line-height", "value" ], "breakpointName": "xl", "isActive": true, "ngModelOptions": {}, "cohIdKey": "styles_4t3k59zuym", "isVariableMode": false, "$validators": {} }, { "type": "cohSelect", "key": [ "styles", "pseudos", 0, "children", 0, "styles", "xl", "text-align", "value" ], "title": "Text align", "options": [ { "label": " ", "nullOption": true }, { "label": "Left", "value": "left" }, { "label": "Right", "value": "right" }, { "label": "Center", "value": "center" } ], "isStyle": true, "defaultActive": true, "schema": { "type": "string" }, "originalKeyValue": [ "text-align", "value" ], "breakpointName": "xl", "isActive": true, "ngModelOptions": {}, "cohIdKey": "styles_7rzebxljrk", "isVariableMode": false }, { "type": "cohTextBox", "key": [ "styles", "pseudos", 0, "children", 0, "styles", "xl", "letter-spacing", "value" ], "title": "Letter spacing", "isStyle": true, "schema": { "type": "string", "cohValidate": [ "cssLengthNoPercentWithNormal", "scssVariable" ] }, "originalKeyValue": [ "letter-spacing", "value" ], "breakpointName": "xl", "isActive": true, "ngModelOptions": {}, "cohIdKey": "styles_go77nui71l", "isVariableMode": false, "$validators": {} }, { "type": "cohTextBox", "key": [ "styles", "pseudos", 0, "children", 0, "styles", "xl", "word-spacing", "value" ], "title": "Word spacing", "isStyle": true, "schema": { "type": "string", "cohValidate": [ "cssLengthNoPercentWithNormal", "scssVariable" ] }, "originalKeyValue": [ "word-spacing", "value" ], "breakpointName": "xl", "isActive": true, "ngModelOptions": {}, "cohIdKey": "styles_v0tqv0eq41", "isVariableMode": false, "$validators": {} }, { "type": "cohTextBox", "key": [ "styles", "pseudos", 0, "children", 0, "styles", "xl", "text-indent", "value" ], "title": "Text indent", "isStyle": true, "schema": { "type": "string", "cohValidate": [ "cssLength", "scssVariable" ] }, "originalKeyValue": [ "text-indent", "value" ], "breakpointName": "xl", "isActive": true, "ngModelOptions": {}, "cohIdKey": "styles_0917wapfkz", "isVariableMode": false, "$validators": {} } ], "ngModelOptions": {} } ] } ], "mapperKey": "styles", "currentBreakpoints": [ { "name": "xl", "iconName": "television" } ], "ngModelOptions": {} } ], "ngModelOptions": {} } ], "ngModelOptions": {}, "isOpen": true, "mapperKey": "styles", "currentLevelTitle": "sdsd" }, "selectorType": "child", "model": "sdsd", "oldModelKey": [ "pseudos", 0, "children", 0 ], "prevKey": [ "pseudos", 0, "children", 0 ], "allowedTypes": [ "child", "pseudo", "modifier" ] } ], "form": { "type": "cohFormBuilder", "element": "div", "currentLevel": { "prevKey": [ "pseudos", 0 ] }, "defaultKey": "custom-styles-layout", "items": [ { "type": "cohAccordion", "htmlClass": "coh-accordion-panel-body--bleed coh-accordion-panel-body--dark", "title": "Font", "weight": 1, "formKey": "font", "isOpen": true, "options": [ "font-and-color", "font-sizing-and-alignment", "font-style-and-wrapping", "text-shadow" ], "items": [ { "type": "cohSection", "title": "Font and color", "formKey": "font-and-color", "helpKey": "css-font-and-color", "weight": 1, "breakpoints": true, "items": [ { "type": "fieldset", "items": [ { "key": [ "xl" ], "title": "xl", "type": "cohBreakpointFieldset", "items": [ { "type": "cohSelect", "key": [ "styles", "pseudos", 0, "styles", "xl", "font-family", "value" ], "title": "Font family", "isStyle": true, "defaultActive": true, "valueProperty": "stack.variable", "labelProperty": "stack.name", "endpoint": "/cohesionapi/main/font_libraries", "schema": { "type": "string" }, "originalKeyValue": [ "font-family", "value" ], "breakpointName": "xl", "isActive": true, "ngModelOptions": {}, "loading": false, "cohIdKey": "styles_718vz42gcz", "isVariableMode": false, "options": [ { "label": " ", "nullOption": true }, { "stack": { "inuse": true, "systemfont": false, "name": "Playfair Display", "uid": "playfair-display", "class": ".coh-font-playfair-display", "variable": "$coh-font-playfair-display", "fontStack": "\'Playfair Display\', serif", "smoothing": { "firefox": true, "webkit": true } }, "value": "$coh-font-playfair-display", "label": "Playfair Display" }, { "stack": { "inuse": true, "systemfont": false, "fontStack": "\'Roboto\', sans-serif", "name": "Roboto", "uid": "roboto", "class": ".coh-font-roboto", "variable": "$coh-font-roboto", "smoothing": { "firefox": true, "webkit": true } }, "value": "$coh-font-roboto", "label": "Roboto" } ] }, { "type": "cohTextBox", "key": [ "styles", "pseudos", 0, "styles", "xl", "font-weight", "value" ], "title": "Font weight", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssFontWeight", "scssVariable" ] }, "originalKeyValue": [ "font-weight", "value" ], "breakpointName": "xl", "isActive": true, "ngModelOptions": {}, "cohIdKey": "styles_n0imap4p96", "isVariableMode": false, "$validators": {}, "validationMessage": { "regxValidator": "Invalid variable" } }, { "type": "cohColourPickerOpener", "key": [ "styles", "pseudos", 0, "styles", "xl", "color", "value" ], "title": "Font color", "isStyle": true, "defaultActive": true, "schema": { "type": "object" }, "originalKeyValue": [ "color", "value" ], "breakpointName": "xl", "isActive": true, "ngModelOptions": {}, "cohIdKey": "styles_fdvf7a8ugo", "isVariableMode": false, "colourPickerOptions": { "flat": true }, "options": { "preferredFormat": "hex" } } ], "ngModelOptions": {} } ] } ], "mapperKey": "styles", "currentBreakpoints": [ { "name": "xl", "iconName": "television" } ], "ngModelOptions": {} } ], "ngModelOptions": {} } ], "ngModelOptions": {}, "isOpen": true, "mapperKey": "styles", "currentLevelTitle": ":active" }, "selectorType": "pseudo", "prevKey": [ "pseudos", 0 ], "allowedTypes": [ "child", "pseudo" ] }, { "title": ".cc", "type": "container", "items": [], "form": { "type": "cohFormBuilder", "element": "div", "currentLevel": { "prevKey": [ "modifiers", 0 ] }, "defaultKey": "custom-styles-layout", "items": [ { "type": "cohAccordion", "htmlClass": "coh-accordion-panel-body--bleed coh-accordion-panel-body--dark", "title": "Layout", "weight": 3, "formKey": "layout", "isOpen": true, "options": [ "height", "width", "padding", "margin", "float", "position", "display", "opacity", "vertical-alignment", "multi-column-layout", "flex-container", "flex-item", "box-shadow" ], "items": [ { "type": "cohSection", "title": "Height", "formKey": "height", "helpKey": "css-layout-height", "weight": 1, "breakpoints": true, "items": [ { "type": "fieldset", "items": [ { "key": [ "xl" ], "title": "xl", "type": "cohBreakpointFieldset", "items": [ { "type": "cohTextBox", "key": [ "styles", "modifiers", 0, "styles", "xl", "min-height", "value" ], "title": "Min height", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssPositiveLengthWithAuto", "scssVariable" ] }, "originalKeyValue": [ "min-height", "value" ], "breakpointName": "xl", "isActive": true, "ngModelOptions": {}, "cohIdKey": "styles_gjrwr059gh", "isVariableMode": false, "$validators": {}, "validationMessage": { "regxValidator": "Invalid variable" } }, { "type": "cohTextBox", "key": [ "styles", "modifiers", 0, "styles", "xl", "max-height", "value" ], "title": "Max height", "isStyle": true, "schema": { "type": "string", "cohValidate": [ "cssPositiveLengthWithNone", "scssVariable" ] }, "originalKeyValue": [ "max-height", "value" ], "breakpointName": "xl", "isActive": true, "ngModelOptions": {}, "cohIdKey": "styles_4irdtdkwov", "isVariableMode": false, "$validators": {} }, { "type": "cohTextBox", "key": [ "styles", "modifiers", 0, "styles", "xl", "height", "value" ], "title": "Fixed height", "isStyle": true, "schema": { "type": "string", "cohValidate": [ "cssPositiveLengthWithAuto", "scssVariable" ] }, "originalKeyValue": [ "height", "value" ], "breakpointName": "xl", "isActive": true, "ngModelOptions": {}, "cohIdKey": "styles_yzg85b7afug", "isVariableMode": false, "$validators": {} } ], "ngModelOptions": {} } ] } ], "mapperKey": "styles", "currentBreakpoints": [ { "name": "xl", "iconName": "television" } ], "ngModelOptions": {} } ], "ngModelOptions": {} } ], "ngModelOptions": {}, "isOpen": true, "mapperKey": "styles", "currentLevelTitle": ".cc" }, "selectorType": "modifier", "model": ".cc", "prevKey": [ "modifiers", 0 ], "allowedTypes": [ "child", "pseudo", "modifier" ] }, { "title": "dd", "type": "container", "items": [], "form": { "type": "cohFormBuilder", "element": "div", "currentLevel": { "prevKey": [ "children", 0 ] }, "defaultKey": "custom-styles-layout", "items": [ { "type": "cohAccordion", "htmlClass": "coh-accordion-panel-body--bleed coh-accordion-panel-body--dark", "title": "Layout", "weight": 3, "formKey": "layout", "isOpen": true, "options": [ "height", "width", "padding", "margin", "float", "position", "display", "opacity", "vertical-alignment", "multi-column-layout", "flex-container", "flex-item", "box-shadow" ], "items": [ { "type": "cohSection", "title": "Padding", "formKey": "padding", "helpKey": "css-layout-padding", "weight": 3, "breakpoints": true, "items": [ { "type": "fieldset", "items": [ { "key": [ "xl" ], "title": "xl", "type": "cohBreakpointFieldset", "items": [ { "type": "cohTextBox", "key": [ "styles", "children", 0, "styles", "xl", "padding", "padding-equal", "value" ], "title": "Padding equal", "isStyle": true, "defaultActive": false, "schema": { "type": "string", "cohValidate": [ "cssPositiveLength", "scssVariable" ] }, "originalKeyValue": [ "padding", "padding-equal", "value" ], "breakpointName": "xl", "isActive": false }, { "type": "cohTextBox", "key": [ "styles", "children", 0, "styles", "xl", "padding", "padding-top", "value" ], "title": "Padding top", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssPositiveLength", "scssVariable" ] }, "originalKeyValue": [ "padding", "padding-top", "value" ], "breakpointName": "xl", "isActive": true, "ngModelOptions": {}, "cohIdKey": "styles_1xlwvg0kkp", "isVariableMode": false, "$validators": {} }, { "type": "cohTextBox", "key": [ "styles", "children", 0, "styles", "xl", "padding", "padding-bottom", "value" ], "title": "Padding bottom", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssPositiveLength", "scssVariable" ] }, "originalKeyValue": [ "padding", "padding-bottom", "value" ], "breakpointName": "xl", "isActive": true, "ngModelOptions": {}, "cohIdKey": "styles_fnmvi1c3av", "isVariableMode": false, "$validators": {}, "validationMessage": { "regxValidator": "Invalid variable" } }, { "type": "cohTextBox", "key": [ "styles", "children", 0, "styles", "xl", "padding", "padding-left", "value" ], "title": "Padding left", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssPositiveLength", "scssVariable" ] }, "originalKeyValue": [ "padding", "padding-left", "value" ], "breakpointName": "xl", "isActive": true, "ngModelOptions": {}, "cohIdKey": "styles_r9g7iwgneak", "isVariableMode": false, "$validators": {}, "validationMessage": { "regxValidator": "Invalid variable" } }, { "type": "cohTextBox", "key": [ "styles", "children", 0, "styles", "xl", "padding", "padding-right", "value" ], "title": "Padding right", "isStyle": true, "defaultActive": true, "schema": { "type": "string", "cohValidate": [ "cssPositiveLength", "scssVariable" ] }, "originalKeyValue": [ "padding", "padding-right", "value" ], "breakpointName": "xl", "isActive": true, "ngModelOptions": {}, "cohIdKey": "styles_8czncb04zn", "isVariableMode": false, "$validators": {} } ], "ngModelOptions": {} } ] } ], "mapperKey": "styles", "currentBreakpoints": [ { "name": "xl", "iconName": "television" } ], "ngModelOptions": {} } ], "ngModelOptions": {} } ], "ngModelOptions": {}, "isOpen": true, "mapperKey": "styles", "currentLevelTitle": "dd" }, "selectorType": "child", "model": "dd", "prevKey": [ "children", 0 ], "allowedTypes": [ "child", "pseudo", "modifier" ] } ], "selectorType": "topLevel" } }';

  /**
   * {@inheritdoc}
   */
  public function setUp():void {
    $this->unit = new _0025MockEntityUpdate([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0018EntityUpdate::runUpdate
   */
  public function testRunUpdate() {

    $component = new _0025MockEntity($this->fixture_component, TRUE);
    $before_json = $component->getDecodedJsonValues();
    $this->assertionsLayoutCanvasBefore($before_json);
    $this->unit->runUpdate($component);
    $this->assertionsLayoutCanvasAfter($component->getDecodedJsonValues(), $before_json);
    $this->unit->runUpdate($component);
    $this->assertionsLayoutCanvasAfter($component->getDecodedJsonValues(), $before_json);

    $style = new _0025MockEntity($this->fixture_style_json_values, FALSE, $this->fixture_style_json_mapper);
    $this->assertionsStyleBefore($style->getDecodedJsonValues(), $style->getDecodedJsonMapper(TRUE));
    $this->unit->runUpdate($style);
    $this->assertionsStyleAfter($style->getDecodedJsonValues(), $style->getDecodedJsonMapper(TRUE));
    $this->unit->runUpdate($style);
    $this->assertionsStyleAfter($style->getDecodedJsonValues(), $style->getDecodedJsonMapper(TRUE));

  }

  private function assertionsLayoutCanvasBefore($json) {
    $this->assertArrayHasKey('pseudos', $json['model']['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles'], 'Pseudos in styles has key');
    $this->assertEquals('11', $json['model']['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['pseudos'][0]['styles']['xl']['min-height']['value']);

    $this->assertArrayHasKey('children', $json['model']['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['pseudos'][0], 'children in pseudos has key');
    $this->assertArrayHasKey('children', $json['model']['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['pseudos'][0]['children'][0], 'children in children in pseudos has key');
    $this->assertArrayHasKey('pseudos', $json['model']['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['pseudos'][0]['children'][0], 'pseudos in children in pseudos has key');
    $this->assertArrayHasKey('modifiers', $json['model']['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['pseudos'][0]['children'][0], 'modifiers in children in pseudos has key');
    $this->assertArrayHasKey('prefix', $json['model']['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['pseudos'][0]['children'][0], 'prefix in children in pseudos has key');
    $this->assertEquals('[Field 2]', $json['model']['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['pseudos'][0]['children'][0]['styles']['xl']['font-size']['value']);

    $this->assertArrayHasKey('pseudos', $json['model']['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['pseudos'][0], 'Pseudos in pseudos has key');
    $this->assertArrayHasKey('modifiers', $json['model']['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['pseudos'][0], 'modifiers in pseudos has key');
    $this->assertArrayHasKey('prefix', $json['model']['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['pseudos'][0], 'prefix in pseudos has key');

    $this->assertArrayHasKey('children', $json['model']['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles'], 'Children in styles has key');

    $this->assertArrayHasKey('pseudos', $json['model']['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['styles'], 'Pseudos in styles/styles has key');

    // Mapper.
    $this->assertArrayHasKey('topLevel', $json['mapper']['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['settings']);
    $this->assertArrayHasKey('topLevel', $json['mapper']['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['markup']);
    $this->assertArrayHasKey('topLevel', $json['mapper']['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['animateonview']);
    $this->assertArrayHasKey('topLevel', $json['mapper']['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['seo']);
    $this->assertArrayHasKey('topLevel', $json['mapper']['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['analytics']);
    $this->assertArrayHasKey('topLevel', $json['mapper']['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['hideNoData']);

    $this->assertArrayHasKey('topLevel', $json['mapper']['fa90b640-36f8-4842-a69e-7b43321b1b35']['settings']);
    $this->assertArrayHasKey('dropzone', $json['mapper']['fa90b640-36f8-4842-a69e-7b43321b1b35']['settings']);
    $this->assertArrayNotHasKey('formDefinition', $json['mapper']['fa90b640-36f8-4842-a69e-7b43321b1b35']['settings']);
    $this->assertArrayNotHasKey('form', $json['mapper']['fa90b640-36f8-4842-a69e-7b43321b1b35']['settings']);

    // variableFields.
    $this->assertEquals('styles.pseudos.0.pseudos.2.styles.xl.max-height.value', $json['variableFields']['fa90b640-36f8-4842-a69e-7b43321b1b35'][0]);
    $this->assertEquals('styles.pseudos.0.children.0.styles.xl.font-size.value', $json['variableFields']['fa90b640-36f8-4842-a69e-7b43321b1b35'][1]);

  }

  private function assertionsLayoutCanvasAfter($after_json, $before_json) {

    // Make sure the canvas has not been changed.
    $this->assertEquals($after_json['canvas'], $before_json['canvas']);

    // Make sure the componentForm has not been changed.
    $this->assertEquals($after_json['componentForm'], $before_json['componentForm']);

    // Assert Model.
    $before_model = $before_json['model'];
    $after_model = $after_json['model'];
    // Only the paragraph element has styles so most element model should remain the same.
    $this->assertEquals($after_model['093be72e-486c-43b0-affe-d66b1e617634'], $before_model['093be72e-486c-43b0-affe-d66b1e617634']);
    $this->assertEquals($after_model['7c43da5f-3243-4721-9d8a-a17d5a4d56c6'], $before_model['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']);
    $this->assertEquals($after_model['5d7f4bdf-6f67-4a6f-bf06-f8d099ba3b65'], $before_model['5d7f4bdf-6f67-4a6f-bf06-f8d099ba3b65']);
    $this->assertEquals($after_model['75fc200c-5741-49dc-9913-34db49a9acde'], $before_model['75fc200c-5741-49dc-9913-34db49a9acde']);
    $this->assertEquals($after_model['c7a6e84d-4f7e-4dae-a9e6-849e569033e8'], $before_model['c7a6e84d-4f7e-4dae-a9e6-849e569033e8']);
    $this->assertEquals($after_model['0228b0a0-d3c4-46fc-9ce4-32cb4b64674d'], $before_model['0228b0a0-d3c4-46fc-9ce4-32cb4b64674d']);
    $this->assertEquals($after_model['0a7e17d8-6803-42f2-ba2f-bb4c78ad265e'], $before_model['0a7e17d8-6803-42f2-ba2f-bb4c78ad265e']);
    // Paragraph element.
    $this->assertEquals($after_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['settings'], $before_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['settings']);
    $this->assertEquals($after_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['context-visibility'], $before_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['context-visibility']);

    $this->assertArrayNotHasKey('pseudos', $after_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles'], 'Pseudos in styles');
    $this->assertArrayNotHasKey('children', $after_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles'], 'Children in styles');

    // @todo to Jesse ????
    //   $this->assertArrayNotHasKey('pseudos', $after_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['styles'], 'Pseudos in styles/styles');
    $this->assertArrayHasKey('350c43f3-883a-46c1-a0c2-b9840c4850a5', $after_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']);
    $this->assertEquals('22', $after_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['350c43f3-883a-46c1-a0c2-b9840c4850a5']['styles']['xl']['min-width']['value']);
    $this->assertArrayNotHasKey('children', $after_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['350c43f3-883a-46c1-a0c2-b9840c4850a5']);
    $this->assertArrayNotHasKey('pseudos', $after_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['350c43f3-883a-46c1-a0c2-b9840c4850a5']);
    $this->assertArrayNotHasKey('modifiers', $after_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['350c43f3-883a-46c1-a0c2-b9840c4850a5']);
    $this->assertArrayNotHasKey('prefix', $after_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['350c43f3-883a-46c1-a0c2-b9840c4850a5']);

    $this->assertArrayHasKey('76c5b0c2-3b6b-426a-aacd-401d70e2a08c', $after_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']);
    $this->assertEquals('[Field 2]', $after_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['76c5b0c2-3b6b-426a-aacd-401d70e2a08c']['styles']['xl']['font-size']['value']);
    $this->assertArrayNotHasKey('children', $after_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['76c5b0c2-3b6b-426a-aacd-401d70e2a08c']);
    $this->assertArrayNotHasKey('pseudos', $after_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['76c5b0c2-3b6b-426a-aacd-401d70e2a08c']);
    $this->assertArrayNotHasKey('modifiers', $after_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['76c5b0c2-3b6b-426a-aacd-401d70e2a08c']);
    $this->assertArrayNotHasKey('prefix', $after_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['76c5b0c2-3b6b-426a-aacd-401d70e2a08c']);

    $this->assertArrayHasKey('e1a684be-5a94-4a2e-b48b-313f935b0514', $after_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']);
    $this->assertEquals('33', $after_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['e1a684be-5a94-4a2e-b48b-313f935b0514']['styles']['xl']['margin']['margin-top']['value']);
    $this->assertArrayNotHasKey('children', $after_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['e1a684be-5a94-4a2e-b48b-313f935b0514']);
    $this->assertArrayNotHasKey('pseudos', $after_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['e1a684be-5a94-4a2e-b48b-313f935b0514']);
    $this->assertArrayNotHasKey('modifiers', $after_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['e1a684be-5a94-4a2e-b48b-313f935b0514']);
    $this->assertArrayNotHasKey('prefix', $after_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['e1a684be-5a94-4a2e-b48b-313f935b0514']);

    $this->assertArrayHasKey('576e69b3-7501-45c9-aa8b-914f6967fa0c', $after_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']);
    $this->assertEquals('44', $after_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['576e69b3-7501-45c9-aa8b-914f6967fa0c']['styles']['xl']['margin']['margin-top']['value']);

    $this->assertArrayHasKey('a39a774b-4187-4014-b878-e3f0c8de216b', $after_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']);
    $this->assertEquals(FALSE, $after_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['a39a774b-4187-4014-b878-e3f0c8de216b']['styles']['xl']['clearfix']['value']);
    $this->assertEquals(44, $after_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['a39a774b-4187-4014-b878-e3f0c8de216b']['styles']['xl']['min-height']['value']);
    $this->assertEquals('[Field 1]', $after_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['a39a774b-4187-4014-b878-e3f0c8de216b']['styles']['xl']['max-height']['value']);

    $this->assertArrayHasKey('6d05c31c-7a0a-4464-9dd6-abecbef9f9a1', $after_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']);
    $this->assertEquals('11', $after_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['6d05c31c-7a0a-4464-9dd6-abecbef9f9a1']['styles']['xl']['min-height']['value']);

    // Mapper.
    $after_mapper = $after_json['mapper'];
    $this->assertArrayNotHasKey('topLevel', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['settings']);
    $this->assertArrayNotHasKey('dropzone', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['settings']);
    $this->assertArrayHasKey('formDefinition', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['settings']);
    $this->assertArrayHasKey('selectorType', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['settings']);
    $this->assertArrayHasKey('form', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['settings']);

    $this->assertArrayNotHasKey('topLevel', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['markup']);
    $this->assertArrayNotHasKey('dropzone', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['markup']);
    $this->assertArrayHasKey('formDefinition', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['markup']);
    $this->assertArrayHasKey('title', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['markup']);
    $this->assertArrayHasKey('selectorType', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['markup']);
    $this->assertArrayHasKey('form', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['markup']);

    $this->assertArrayNotHasKey('topLevel', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['animateonview']);
    $this->assertArrayNotHasKey('dropzone', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['animateonview']);
    $this->assertArrayHasKey('formDefinition', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['animateonview']);
    $this->assertArrayHasKey('title', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['animateonview']);
    $this->assertArrayHasKey('selectorType', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['animateonview']);
    $this->assertArrayHasKey('form', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['animateonview']);

    $this->assertArrayNotHasKey('topLevel', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['seo']);
    $this->assertArrayNotHasKey('dropzone', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['seo']);
    $this->assertArrayHasKey('formDefinition', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['seo']);
    $this->assertArrayHasKey('title', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['seo']);
    $this->assertArrayHasKey('selectorType', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['seo']);
    $this->assertArrayHasKey('form', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['seo']);

    $this->assertArrayNotHasKey('topLevel', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['analytics']);
    $this->assertArrayNotHasKey('dropzone', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['analytics']);
    $this->assertArrayHasKey('formDefinition', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['analytics']);
    $this->assertArrayHasKey('title', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['analytics']);
    $this->assertArrayHasKey('selectorType', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['analytics']);
    $this->assertArrayHasKey('form', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['analytics']);

    $this->assertArrayNotHasKey('topLevel', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['hideNoData']);
    $this->assertArrayNotHasKey('dropzone', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['hideNoData']);
    $this->assertArrayHasKey('formDefinition', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['hideNoData']);
    $this->assertArrayHasKey('title', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['hideNoData']);
    $this->assertArrayHasKey('selectorType', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['hideNoData']);
    $this->assertArrayHasKey('form', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['hideNoData']);

    $this->assertArrayNotHasKey('topLevel', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['context-visibility']);
    $this->assertArrayNotHasKey('dropzone', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['context-visibility']);
    $this->assertArrayHasKey('formDefinition', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['context-visibility']);
    $this->assertArrayHasKey('title', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['context-visibility']);
    $this->assertArrayHasKey('selectorType', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['context-visibility']);
    $this->assertArrayHasKey('form', $after_mapper['7c43da5f-3243-4721-9d8a-a17d5a4d56c6']['context-visibility']);

    // Mapper Paragraph.
    $this->assertArrayNotHasKey('topLevel', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['settings']);
    $this->assertArrayNotHasKey('dropzone', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['settings']);
    $this->assertArrayHasKey('formDefinition', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['settings']);
    $this->assertArrayHasKey('selectorType', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['settings']);
    $this->assertArrayHasKey('form', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['settings']);

    $this->assertArrayNotHasKey('topLevel', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']);
    $this->assertArrayNotHasKey('dropzone', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']);
    $this->assertArrayHasKey('formDefinition', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']);
    $this->assertArrayHasKey('selectorType', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']);
    $this->assertArrayHasKey('form', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']);

    $this->assertArrayHasKey('items', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']);

    $this->assertEquals('6d05c31c-7a0a-4464-9dd6-abecbef9f9a1', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['items'][0]['uuid']);
    $this->assertArrayNotHasKey('form', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['items'][0]);
    $this->assertArrayNotHasKey('prevKey', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['items'][0]);
    $this->assertArrayNotHasKey('allowedTypes', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['items'][0]);
    $this->assertArrayNotHasKey('oldModelKey', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['items'][0]);

    $this->assertEquals('350c43f3-883a-46c1-a0c2-b9840c4850a5', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['items'][1]['uuid']);
    $this->assertArrayNotHasKey('form', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['items'][1]);
    $this->assertArrayNotHasKey('prevKey', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['items'][1]);
    $this->assertArrayNotHasKey('allowedTypes', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['items'][1]);
    $this->assertArrayNotHasKey('oldModelKey', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['items'][1]);

    $this->assertEquals('e1a684be-5a94-4a2e-b48b-313f935b0514', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['items'][0]['items'][0]['uuid']);
    $this->assertArrayNotHasKey('form', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['items'][0]['items'][0]);
    $this->assertArrayNotHasKey('prevKey', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['items'][0]['items'][0]);
    $this->assertArrayNotHasKey('allowedTypes', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['items'][0]['items'][0]);
    $this->assertArrayNotHasKey('oldModelKey', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['items'][0]['items'][0]);

    $this->assertEquals('576e69b3-7501-45c9-aa8b-914f6967fa0c', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['items'][0]['items'][1]['uuid']);
    $this->assertArrayNotHasKey('form', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['items'][0]['items'][1]);
    $this->assertArrayNotHasKey('prevKey', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['items'][0]['items'][1]);
    $this->assertArrayNotHasKey('allowedTypes', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['items'][0]['items'][1]);
    $this->assertArrayNotHasKey('oldModelKey', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['items'][0]['items'][1]);

    $this->assertEquals('a39a774b-4187-4014-b878-e3f0c8de216b', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['items'][0]['items'][2]['uuid']);
    $this->assertArrayNotHasKey('form', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['items'][0]['items'][2]);
    $this->assertArrayNotHasKey('prevKey', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['items'][0]['items'][2]);
    $this->assertArrayNotHasKey('allowedTypes', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['items'][0]['items'][2]);
    $this->assertArrayNotHasKey('oldModelKey', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['items'][0]['items'][2]);

    $this->assertEquals('76c5b0c2-3b6b-426a-aacd-401d70e2a08c', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['items'][0]['items'][3]['uuid']);
    $this->assertArrayNotHasKey('form', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['items'][0]['items'][3]);
    $this->assertArrayNotHasKey('prevKey', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['items'][0]['items'][3]);
    $this->assertArrayNotHasKey('allowedTypes', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['items'][0]['items'][3]);
    $this->assertArrayNotHasKey('oldModelKey', $after_mapper['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['items'][0]['items'][3]);

    // previewModel.
    $after_preview_model = $after_json['previewModel'];
    $this->assertEquals('45', $after_preview_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['76c5b0c2-3b6b-426a-aacd-401d70e2a08c']['styles']['xl']['font-size']['value']);
    $this->assertEquals(NULL, $after_preview_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['e1a684be-5a94-4a2e-b48b-313f935b0514']);
    $this->assertEquals(NULL, $after_preview_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['576e69b3-7501-45c9-aa8b-914f6967fa0c']);
    $this->assertEquals(99, $after_preview_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['a39a774b-4187-4014-b878-e3f0c8de216b']['styles']['xl']['max-height']['value']);
    $this->assertArrayHasKey('6d05c31c-7a0a-4464-9dd6-abecbef9f9a1', $after_preview_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']);
    $this->assertEquals(TRUE, is_array($after_preview_model['fa90b640-36f8-4842-a69e-7b43321b1b35']['styles']['6d05c31c-7a0a-4464-9dd6-abecbef9f9a1']));

    // variableFields.
    $this->assertEquals('styles.a39a774b-4187-4014-b878-e3f0c8de216b.styles.xl.max-height.value', $after_json['variableFields']['fa90b640-36f8-4842-a69e-7b43321b1b35'][0]);
    $this->assertEquals('styles.76c5b0c2-3b6b-426a-aacd-401d70e2a08c.styles.xl.font-size.value', $after_json['variableFields']['fa90b640-36f8-4842-a69e-7b43321b1b35'][1]);

  }

  private function assertionsStyleBefore($json_values, $json_mapper) {
    // Model.
    $this->assertArrayHasKey('settings', $json_values['styles']);
    $this->assertArrayHasKey('styles', $json_values['styles']);
    $this->assertArrayHasKey('pseudos', $json_values['styles']);
    $this->assertArrayHasKey('modifiers', $json_values['styles']);
    $this->assertArrayHasKey('children', $json_values['styles']);

    $this->assertArrayHasKey('children', $json_values['styles']['pseudos'][0]);
    $this->assertArrayHasKey('pseudos', $json_values['styles']['pseudos'][0]);
    $this->assertArrayHasKey('modifiers', $json_values['styles']['pseudos'][0]);
    $this->assertArrayHasKey('prefix', $json_values['styles']['pseudos'][0]);

    $this->assertArrayHasKey('children', $json_values['styles']['pseudos'][0]['children'][0]);
    $this->assertArrayHasKey('pseudos', $json_values['styles']['pseudos'][0]['children'][0]);
    $this->assertArrayHasKey('modifiers', $json_values['styles']['pseudos'][0]['children'][0]);
    $this->assertArrayHasKey('prefix', $json_values['styles']['pseudos'][0]['children'][0]);

    $this->assertArrayHasKey('children', $json_values['styles']['pseudos'][0]['pseudos'][0]);
    $this->assertArrayHasKey('pseudos', $json_values['styles']['pseudos'][0]['pseudos'][0]);
    $this->assertArrayHasKey('modifiers', $json_values['styles']['pseudos'][0]['pseudos'][0]);
    $this->assertArrayHasKey('prefix', $json_values['styles']['pseudos'][0]['pseudos'][0]);

    $this->assertArrayHasKey('children', $json_values['styles']['pseudos'][0]['pseudos'][1]);
    $this->assertArrayHasKey('pseudos', $json_values['styles']['pseudos'][0]['pseudos'][1]);
    $this->assertArrayHasKey('modifiers', $json_values['styles']['pseudos'][0]['pseudos'][1]);
    $this->assertArrayHasKey('prefix', $json_values['styles']['pseudos'][0]['pseudos'][1]);

    $this->assertArrayHasKey('children', $json_values['styles']['modifiers'][0]);
    $this->assertArrayHasKey('pseudos', $json_values['styles']['modifiers'][0]);
    $this->assertArrayHasKey('modifiers', $json_values['styles']['modifiers'][0]);
    $this->assertArrayHasKey('prefix', $json_values['styles']['modifiers'][0]);

    $this->assertArrayHasKey('children', $json_values['styles']['children'][0]);
    $this->assertArrayHasKey('pseudos', $json_values['styles']['children'][0]);
    $this->assertArrayHasKey('modifiers', $json_values['styles']['children'][0]);
    $this->assertArrayHasKey('prefix', $json_values['styles']['children'][0]);

    // Mapper.
    $this->assertArrayHasKey('topLevel', $json_mapper['styles']);
    $this->assertArrayHasKey('dropzone', $json_mapper['styles']);

    $this->assertEquals(':active', $json_mapper['styles']['dropzone'][0]['title']);
    $this->assertArrayHasKey('items', $json_mapper['styles']['dropzone'][0]);
    $this->assertArrayHasKey('form', $json_mapper['styles']['dropzone'][0]);
    $this->assertArrayHasKey('prevKey', $json_mapper['styles']['dropzone'][0]);
    $this->assertArrayHasKey('allowedTypes', $json_mapper['styles']['dropzone'][0]);
    $this->assertArrayNotHasKey('uuid', $json_mapper['styles']['dropzone'][0]);

    $this->assertEquals('.cc', $json_mapper['styles']['dropzone'][1]['title']);
    $this->assertArrayHasKey('items', $json_mapper['styles']['dropzone'][1]);
    $this->assertArrayHasKey('form', $json_mapper['styles']['dropzone'][1]);
    $this->assertArrayHasKey('prevKey', $json_mapper['styles']['dropzone'][1]);
    $this->assertArrayHasKey('allowedTypes', $json_mapper['styles']['dropzone'][1]);
    $this->assertArrayNotHasKey('uuid', $json_mapper['styles']['dropzone'][1]);

    $this->assertEquals('dd', $json_mapper['styles']['dropzone'][2]['title']);
    $this->assertArrayHasKey('items', $json_mapper['styles']['dropzone'][2]);
    $this->assertArrayHasKey('form', $json_mapper['styles']['dropzone'][2]);
    $this->assertArrayHasKey('prevKey', $json_mapper['styles']['dropzone'][2]);
    $this->assertArrayHasKey('allowedTypes', $json_mapper['styles']['dropzone'][2]);
    $this->assertArrayNotHasKey('uuid', $json_mapper['styles']['dropzone'][2]);

    $this->assertEquals(':focus', $json_mapper['styles']['dropzone'][0]['items'][0]['title']);
    $this->assertArrayHasKey('items', $json_mapper['styles']['dropzone'][0]['items'][0]);
    $this->assertArrayHasKey('form', $json_mapper['styles']['dropzone'][0]['items'][0]);
    $this->assertArrayHasKey('prevKey', $json_mapper['styles']['dropzone'][0]['items'][0]);
    $this->assertArrayHasKey('allowedTypes', $json_mapper['styles']['dropzone'][0]['items'][0]);
    $this->assertArrayHasKey('oldModelKey', $json_mapper['styles']['dropzone'][0]['items'][0]);
    $this->assertArrayNotHasKey('uuid', $json_mapper['styles']['dropzone'][0]['items'][0]);

    $this->assertEquals(':first-child', $json_mapper['styles']['dropzone'][0]['items'][1]['title']);
    $this->assertArrayHasKey('items', $json_mapper['styles']['dropzone'][0]['items'][1]);
    $this->assertArrayHasKey('form', $json_mapper['styles']['dropzone'][0]['items'][1]);
    $this->assertArrayHasKey('prevKey', $json_mapper['styles']['dropzone'][0]['items'][1]);
    $this->assertArrayHasKey('allowedTypes', $json_mapper['styles']['dropzone'][0]['items'][1]);
    $this->assertArrayNotHasKey('uuid', $json_mapper['styles']['dropzone'][0]['items'][1]);

    $this->assertEquals('sdsd', $json_mapper['styles']['dropzone'][0]['items'][2]['title']);
    $this->assertArrayHasKey('items', $json_mapper['styles']['dropzone'][0]['items'][2]);
    $this->assertArrayHasKey('form', $json_mapper['styles']['dropzone'][0]['items'][2]);
    $this->assertArrayHasKey('prevKey', $json_mapper['styles']['dropzone'][0]['items'][2]);
    $this->assertArrayHasKey('allowedTypes', $json_mapper['styles']['dropzone'][0]['items'][2]);
    $this->assertArrayNotHasKey('uuid', $json_mapper['styles']['dropzone'][0]['items'][2]);

    $this->assertEquals(':ss', $json_mapper['styles']['dropzone'][0]['items'][1]['items'][0]['title']);
    $this->assertArrayHasKey('items', $json_mapper['styles']['dropzone'][0]['items'][1]['items'][0]);
    $this->assertArrayHasKey('form', $json_mapper['styles']['dropzone'][0]['items'][1]['items'][0]);
    $this->assertArrayHasKey('prevKey', $json_mapper['styles']['dropzone'][0]['items'][1]['items'][0]);
    $this->assertArrayHasKey('allowedTypes', $json_mapper['styles']['dropzone'][0]['items'][1]['items'][0]);
    $this->assertArrayNotHasKey('uuid', $json_mapper['styles']['dropzone'][0]['items'][1]['items'][0]);
  }

  private function assertionsStyleAfter($json_values, $json_mapper) {
    // Model.
    $this->assertArrayHasKey('settings', $json_values['styles']);
    $this->assertArrayHasKey('styles', $json_values['styles']);
    $this->assertArrayNotHasKey('pseudos', $json_values['styles']);
    $this->assertArrayNotHasKey('modifiers', $json_values['styles']);
    $this->assertArrayNotHasKey('children', $json_values['styles']);

    $this->assertArrayHasKey('d794598a-448a-4e9b-9ebc-9ddb45cecf5c', $json_values['styles']);
    $this->assertEquals('dd', $json_values['styles']['d794598a-448a-4e9b-9ebc-9ddb45cecf5c']['settings']['element']);
    $this->assertEquals('66', $json_values['styles']['d794598a-448a-4e9b-9ebc-9ddb45cecf5c']['styles']['xl']['padding']['padding-bottom']['value']);
    $this->assertEquals('77', $json_values['styles']['d794598a-448a-4e9b-9ebc-9ddb45cecf5c']['styles']['xl']['padding']['padding-left']['value']);
    $this->assertArrayNotHasKey('pseudos', $json_values['styles']['d794598a-448a-4e9b-9ebc-9ddb45cecf5c']);
    $this->assertArrayNotHasKey('modifiers', $json_values['styles']['d794598a-448a-4e9b-9ebc-9ddb45cecf5c']);
    $this->assertArrayNotHasKey('children', $json_values['styles']['d794598a-448a-4e9b-9ebc-9ddb45cecf5c']);
    $this->assertArrayNotHasKey('prefix', $json_values['styles']['d794598a-448a-4e9b-9ebc-9ddb45cecf5c']);

    $this->assertArrayHasKey('6a6c6855-817a-417b-aaab-b42e3a909974', $json_values['styles']);
    $this->assertEquals('sdsd', $json_values['styles']['6a6c6855-817a-417b-aaab-b42e3a909974']['settings']['element']);
    $this->assertEquals('22', $json_values['styles']['6a6c6855-817a-417b-aaab-b42e3a909974']['styles']['xl']['font-size']['value']);
    $this->assertArrayNotHasKey('pseudos', $json_values['styles']['6a6c6855-817a-417b-aaab-b42e3a909974']);
    $this->assertArrayNotHasKey('modifiers', $json_values['styles']['6a6c6855-817a-417b-aaab-b42e3a909974']);
    $this->assertArrayNotHasKey('children', $json_values['styles']['6a6c6855-817a-417b-aaab-b42e3a909974']);
    $this->assertArrayNotHasKey('prefix', $json_values['styles']['6a6c6855-817a-417b-aaab-b42e3a909974']);

    $this->assertArrayHasKey('e9a2b9dd-b370-46c6-923a-8b4b0965dccc', $json_values['styles']);
    $this->assertEquals(':focus', $json_values['styles']['e9a2b9dd-b370-46c6-923a-8b4b0965dccc']['settings']['pseudo']);
    $this->assertEquals('$coh-font-roboto', $json_values['styles']['e9a2b9dd-b370-46c6-923a-8b4b0965dccc']['styles']['xl']['font-family']['value']);
    $this->assertArrayNotHasKey('pseudos', $json_values['styles']['e9a2b9dd-b370-46c6-923a-8b4b0965dccc']);
    $this->assertArrayNotHasKey('modifiers', $json_values['styles']['e9a2b9dd-b370-46c6-923a-8b4b0965dccc']);
    $this->assertArrayNotHasKey('children', $json_values['styles']['e9a2b9dd-b370-46c6-923a-8b4b0965dccc']);
    $this->assertArrayNotHasKey('prefix', $json_values['styles']['e9a2b9dd-b370-46c6-923a-8b4b0965dccc']);

    $this->assertArrayHasKey('2440d1ef-73fe-4c9c-9aac-90e814b6f94a', $json_values['styles']);
    $this->assertEquals(':ss', $json_values['styles']['2440d1ef-73fe-4c9c-9aac-90e814b6f94a']['settings']['pseudo']);
    $this->assertEquals('$coh-color-black', $json_values['styles']['2440d1ef-73fe-4c9c-9aac-90e814b6f94a']['styles']['xl']['background-color']['value']['variable']);
    $this->assertArrayNotHasKey('pseudos', $json_values['styles']['2440d1ef-73fe-4c9c-9aac-90e814b6f94a']);
    $this->assertArrayNotHasKey('modifiers', $json_values['styles']['2440d1ef-73fe-4c9c-9aac-90e814b6f94a']);
    $this->assertArrayNotHasKey('children', $json_values['styles']['2440d1ef-73fe-4c9c-9aac-90e814b6f94a']);
    $this->assertArrayNotHasKey('prefix', $json_values['styles']['2440d1ef-73fe-4c9c-9aac-90e814b6f94a']);

    $this->assertArrayHasKey('b0ed739a-eb2f-4f18-b730-90bd68cea31e', $json_values['styles']);
    $this->assertEquals(':first-child', $json_values['styles']['b0ed739a-eb2f-4f18-b730-90bd68cea31e']['settings']['pseudo']);
    $this->assertEquals('344', $json_values['styles']['b0ed739a-eb2f-4f18-b730-90bd68cea31e']['styles']['xl']['margin']['margin-top']['value']);
    $this->assertArrayNotHasKey('pseudos', $json_values['styles']['b0ed739a-eb2f-4f18-b730-90bd68cea31e']);
    $this->assertArrayNotHasKey('modifiers', $json_values['styles']['b0ed739a-eb2f-4f18-b730-90bd68cea31e']);
    $this->assertArrayNotHasKey('children', $json_values['styles']['b0ed739a-eb2f-4f18-b730-90bd68cea31e']);
    $this->assertArrayNotHasKey('prefix', $json_values['styles']['b0ed739a-eb2f-4f18-b730-90bd68cea31e']);

    $this->assertArrayHasKey('11a57964-ff75-4371-a373-aaa1f49d753a', $json_values['styles']);
    $this->assertEquals(':active', $json_values['styles']['11a57964-ff75-4371-a373-aaa1f49d753a']['settings']['pseudo']);
    $this->assertEquals('900', $json_values['styles']['11a57964-ff75-4371-a373-aaa1f49d753a']['styles']['xl']['font-weight']['value']);
    $this->assertArrayNotHasKey('pseudos', $json_values['styles']['11a57964-ff75-4371-a373-aaa1f49d753a']);
    $this->assertArrayNotHasKey('modifiers', $json_values['styles']['11a57964-ff75-4371-a373-aaa1f49d753a']);
    $this->assertArrayNotHasKey('children', $json_values['styles']['11a57964-ff75-4371-a373-aaa1f49d753a']);
    $this->assertArrayNotHasKey('prefix', $json_values['styles']['11a57964-ff75-4371-a373-aaa1f49d753a']);

    $this->assertArrayHasKey('a6197f67-2459-4a5a-a6cb-d9e13c321da2', $json_values['styles']);
    $this->assertEquals('.cc', $json_values['styles']['a6197f67-2459-4a5a-a6cb-d9e13c321da2']['settings']['class']);
    $this->assertEquals('21', $json_values['styles']['a6197f67-2459-4a5a-a6cb-d9e13c321da2']['styles']['xl']['min-height']['value']);
    $this->assertArrayNotHasKey('pseudos', $json_values['styles']['a6197f67-2459-4a5a-a6cb-d9e13c321da2']);
    $this->assertArrayNotHasKey('modifiers', $json_values['styles']['a6197f67-2459-4a5a-a6cb-d9e13c321da2']);
    $this->assertArrayNotHasKey('children', $json_values['styles']['a6197f67-2459-4a5a-a6cb-d9e13c321da2']);
    $this->assertArrayNotHasKey('prefix', $json_values['styles']['a6197f67-2459-4a5a-a6cb-d9e13c321da2']);

    // Mapper.
    $this->assertArrayNotHasKey('topLevel', $json_mapper['styles']);
    $this->assertArrayNotHasKey('dropzone', $json_mapper['styles']);
    $this->assertArrayHasKey('items', $json_mapper['styles']);
    $this->assertArrayHasKey('title', $json_mapper['styles']);
    $this->assertArrayHasKey('selectorType', $json_mapper['styles']);
    $this->assertArrayHasKey('formDefinition', $json_mapper['styles']);
    $this->assertArrayHasKey('form', $json_mapper['styles']);

    $this->assertEquals(':active', $json_mapper['styles']['items'][0]['title']);
    $this->assertArrayHasKey('items', $json_mapper['styles']['items'][0]);
    $this->assertArrayNotHasKey('form', $json_mapper['styles']['items'][0]);
    $this->assertArrayNotHasKey('prevKey', $json_mapper['styles']['items'][0]);
    $this->assertArrayNotHasKey('allowedTypes', $json_mapper['styles']['items'][0]);
    $this->assertArrayHasKey('uuid', $json_mapper['styles']['items'][0]);
    $this->assertEquals('11a57964-ff75-4371-a373-aaa1f49d753a', $json_mapper['styles']['items'][0]['uuid']);

    $this->assertEquals('.cc', $json_mapper['styles']['items'][1]['title']);
    $this->assertArrayHasKey('items', $json_mapper['styles']['items'][1]);
    $this->assertArrayNotHasKey('form', $json_mapper['styles']['items'][1]);
    $this->assertArrayNotHasKey('prevKey', $json_mapper['styles']['items'][1]);
    $this->assertArrayNotHasKey('allowedTypes', $json_mapper['styles']['items'][1]);
    $this->assertArrayHasKey('uuid', $json_mapper['styles']['items'][1]);
    $this->assertEquals('a6197f67-2459-4a5a-a6cb-d9e13c321da2', $json_mapper['styles']['items'][1]['uuid']);

    $this->assertEquals('dd', $json_mapper['styles']['items'][2]['title']);
    $this->assertArrayHasKey('items', $json_mapper['styles']['items'][2]);
    $this->assertArrayNotHasKey('form', $json_mapper['styles']['items'][2]);
    $this->assertArrayNotHasKey('prevKey', $json_mapper['styles']['items'][2]);
    $this->assertArrayNotHasKey('allowedTypes', $json_mapper['styles']['items'][2]);
    $this->assertArrayHasKey('uuid', $json_mapper['styles']['items'][2]);
    $this->assertEquals('d794598a-448a-4e9b-9ebc-9ddb45cecf5c', $json_mapper['styles']['items'][2]['uuid']);

    $this->assertEquals(':focus', $json_mapper['styles']['items'][0]['items'][0]['title']);
    $this->assertArrayHasKey('items', $json_mapper['styles']['items'][0]['items'][0]);
    $this->assertArrayNotHasKey('form', $json_mapper['styles']['items'][0]['items'][0]);
    $this->assertArrayNotHasKey('prevKey', $json_mapper['styles']['items'][0]['items'][0]);
    $this->assertArrayNotHasKey('allowedTypes', $json_mapper['styles']['items'][0]['items'][0]);
    $this->assertArrayNotHasKey('oldModelKey', $json_mapper['styles']['items'][0]['items'][0]);
    $this->assertArrayHasKey('uuid', $json_mapper['styles']['items'][0]['items'][0]);
    $this->assertEquals('e9a2b9dd-b370-46c6-923a-8b4b0965dccc', $json_mapper['styles']['items'][0]['items'][0]['uuid']);

    $this->assertEquals(':first-child', $json_mapper['styles']['items'][0]['items'][1]['title']);
    $this->assertArrayHasKey('items', $json_mapper['styles']['items'][0]['items'][1]);
    $this->assertArrayNotHasKey('form', $json_mapper['styles']['items'][0]['items'][1]);
    $this->assertArrayNotHasKey('prevKey', $json_mapper['styles']['items'][0]['items'][1]);
    $this->assertArrayNotHasKey('allowedTypes', $json_mapper['styles']['items'][0]['items'][1]);
    $this->assertArrayHasKey('uuid', $json_mapper['styles']['items'][0]['items'][1]);
    $this->assertEquals('b0ed739a-eb2f-4f18-b730-90bd68cea31e', $json_mapper['styles']['items'][0]['items'][1]['uuid']);

    $this->assertEquals('sdsd', $json_mapper['styles']['items'][0]['items'][2]['title']);
    $this->assertArrayHasKey('items', $json_mapper['styles']['items'][0]['items'][2]);
    $this->assertArrayNotHasKey('form', $json_mapper['styles']['items'][0]['items'][2]);
    $this->assertArrayNotHasKey('prevKey', $json_mapper['styles']['items'][0]['items'][2]);
    $this->assertArrayNotHasKey('allowedTypes', $json_mapper['styles']['items'][0]['items'][2]);
    $this->assertArrayHasKey('uuid', $json_mapper['styles']['items'][0]['items'][2]);
    $this->assertEquals('6a6c6855-817a-417b-aaab-b42e3a909974', $json_mapper['styles']['items'][0]['items'][2]['uuid']);

    $this->assertEquals(':ss', $json_mapper['styles']['items'][0]['items'][1]['items'][0]['title']);
    $this->assertArrayHasKey('items', $json_mapper['styles']['items'][0]['items'][1]['items'][0]);
    $this->assertArrayNotHasKey('form', $json_mapper['styles']['items'][0]['items'][1]['items'][0]);
    $this->assertArrayNotHasKey('prevKey', $json_mapper['styles']['items'][0]['items'][1]['items'][0]);
    $this->assertArrayNotHasKey('allowedTypes', $json_mapper['styles']['items'][0]['items'][1]['items'][0]);
    $this->assertArrayHasKey('uuid', $json_mapper['styles']['items'][0]['items'][1]['items'][0]);
    $this->assertEquals('2440d1ef-73fe-4c9c-9aac-90e814b6f94a', $json_mapper['styles']['items'][0]['items'][1]['items'][0]['uuid']);

  }

}
