<?php

namespace Drupal\cohesion_elements;

use Drupal\Component\Serialization\Json;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Custom elements service.
 *
 * @package Drupal\cohesion_elements
 */
class CustomElementsService {

  /**
   * @var mixed
   */
  private $elements;

  /**
   * @var CustomElementPluginManager*/
  protected $customElementManager;

  /**
   * @var \Drupal\Core\KeyValueStore\KeyValueStoreInterface*/
  protected $staticAssets;

  /**
   * CustomElementsService constructor.
   *
   * @param CustomElementPluginManager $custom_element_manager
   */
  public function __construct(CustomElementPluginManager $custom_element_manager) {
    $this->customElementManager = $custom_element_manager;
    $this->staticAssets = \Drupal::keyValue('cohesion.assets.static_assets');
  }

  /**
   * Get all elements defined by other modules.
   *
   * @return array
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  private function getElementsFromManager() {
    // If the cached version is not available....
    if (!is_array($this->elements)) {
      $this->elements = [];

      // Get elements from plugin definitions.
      foreach ($this->customElementManager->getDefinitions() as $id => $definition) {
        if ($instance = $this->customElementManager->createInstance($id)) {
          // Get rendered label.
          $label = $instance->getLabel();
          if ($label instanceof TranslatableMarkup) {
            $label = $instance->getLabel()->render();
          }

          $this->elements[$id] = [
            'label' => $label,
            'plugin' => $instance,
          ];
        }

      }
    }

    return $this->elements;
  }

  /**
   * Return a value/label array of all hooks created by all modules.
   *
   * @return array
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  private function getElementsKeyLabel() {
    $elements = [];

    foreach ($this->getElementsFromManager() as $key => $element) {
      // If there is a key, label and form, add the element.
      /** @var \Drupal\cohesion_elements\CustomElementPluginInterface $plugin */
      $plugin = $element['plugin'];
      $fields = $plugin->getFields();
      if (isset($element['label']) && !empty($fields)) {
        $elements[] = [
          'value' => $key,
          'label' => $element['label'],
          'container' => $plugin->getPluginDefinition()['container'] ?? FALSE,
        ];
      }
    }

    return $elements;
  }

  /**
   * Return list of custom elements formatted for elementActionAll()
   *
   * @param $results
   *
   * @return mixed
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function patchElementList($results) {
    foreach ($this->getElementsKeyLabel() as $element) {
      // Build the element.
      $results[$element['value']] = [
        'type' => 'item',
        'uid' => $element['value'],
        'title' => $element['label'],
        'selected' => FALSE,
        'status' => [
          'collapsed' => TRUE,
        ],
      ];
    }

    return $results;
  }

  /**
   * Return list of custom elements formatted for getAssets()
   *
   * @param $results
   *
   * @return array
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function patchElementCategoryList($results) {
    $custom_element_list = $this->getElementsKeyLabel();

    if (count($custom_element_list)) {

      // Build the elements list.
      $children = [];

      foreach ($this->getElementsKeyLabel() as $element) {
        $item = [
          'type' => 'item',
          'uid' => $element['value'],
          'isCustom' => TRUE,
          'title' => $element['label'],
          'selected' => FALSE,
          'status' => [
            'collapsed' => TRUE,
          ],
          'model' => $this->elements[$element['value']]['plugin']->buildDefaultModelSettings(),
        ];

        // Set the custom element to a container if defined in its definition
        if (isset($element['container']) && $element['container'] === TRUE) {
          $item['type'] = 'container';
          $item['children'] = [];
          $item['status']['collapsed'] = FALSE;
        }

        $children[] = $item;
      }

      // Set up the wrapper.
      $results[] = [
        'title' => 'custom_elements',
        'label' => t('Custom elements'),
        'class' => 'coh-element-browser-custom-elements',
        'children' => $children,
      ];

    }

    return $results;
  }

  /**
   * Patch in the custom element form.
   *
   * @param $results
   *
   * @return mixed
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function patchElementBuilderForms($results) {
    $custom_element_list = $this->getElementsKeyLabel();

    if (count($custom_element_list)) {
      foreach ($this->getElementsKeyLabel() as $element) {
        // Get the template.
        $results[$element['value']] = $this->staticAssets->get('custom-element-builder-template');

        // Patch the data in.
        $results[$element['value']]['model']['styles']['settings']['element'] = $element['value'];
        $results[$element['value']]['form'][0]['tabs'][0]['items'][0]['propertyOptionsKey'] = $element['value'];
        $results[$element['value']]['form'][0]['tabs'][0]['items'][0]['defaultKey'] = $element['value'];
      }
    }

    return $results;
  }

  /**
   * @param $results
   *
   * @return mixed
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function patchFormDefaults($results) {
    $custom_element_list = $this->getElementsKeyLabel();

    if (count($custom_element_list)) {
      foreach ($this->getElementsKeyLabel() as $element) {
        // Get the template.
        $results[$element['value']] = $this->staticAssets->get('custom-form-defaults-template');

        // Patch the data.
        $results[$element['value']]['settings']['formDefinition'][0]['formKey'] = $element['value'] . '_settings';
        $results[$element['value']]['settings']['formDefinition'][0]['children'][0]['formKey'] = $element['value'] . '_dynamic';

      }
    }

    return $results;
  }

  /**
   * @param $results
   *
   * @return mixed
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function patchProperyGroupOptions($results) {
    $custom_element_list = $this->getElementsKeyLabel();

    if (count($custom_element_list)) {
      foreach ($this->getElementsKeyLabel() as $element) {
        // Get the template.
        $results[$element['value']] = $this->staticAssets->get('custom-property-group-options-template');

        // Patch the data.
        $results[$element['value']][0]['keyName'] = $element['value'] . '_settings';
        $results[$element['value']][0]['options'][0]['keyName'] = $element['value'] . '_dynamic';
        $results[$element['value']][0]['options'][0]['parentKeyName'] = $element['value'] . '_settings';

      }
    }

    return $results;
  }

  /**
   * @param $results
   *
   * @return mixed
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function patchElementProperties($results) {
    if (count($this->getElementsFromManager())) {
      foreach ($this->getElementsFromManager() as $element_key => $custom_element) {

        // Wrapper element.
        $results[$element_key . '_settings'] = $this->staticAssets->get('custom-element-properties-settings-template');
        $results[$element_key . '_settings']['form'][0]['formKey'] = $element_key . '_settings';
        $results[$element_key . '_settings']['form'][0]['options'][0] = $element_key . '_dynamic';

        // Dynamic element.
        $results[$element_key . '_dynamic'] = $this->staticAssets->get('custom-element-properties-dynamic-template');
        $results[$element_key . '_dynamic']['form'][0]['formKey'] = $element_key . '_dynamic';

        // Loop through the custom form definition and add the form items.
        $form_items = [];
        foreach ($custom_element['plugin']->getFields() as $field_key => $field) {

          // Check that the field type has been set.
          if (isset($field['type'])) {

            // Get the field definition.
            switch ($field['type']) {
              case 'textfield':
                $form_items[] = $this->fieldcohTextBox($field_key, $field);
                break;

              case 'textarea':
                $form_items[] = $this->fieldcohTextarea($field_key, $field);
                break;

              case 'wysiwyg':
                $form_items[] = $this->fieldcohWysiwyg($field_key, $field);
                break;

              case 'image':
                $form_items[] = $this->fieldcohFileBrowser($field_key, $field);
                break;

              case 'select':
                $form_items[] = $this->fieldcohSelect($field_key, $field);
                break;

              case 'checkbox':
                $form_items[] = $this->fieldcheckboxToggle($field_key, $field);
                break;
            }
          }
          else {
            \Drupal::logger('cohesion_elements')->notice('Custom element field type is not defined.');
          }
        }

        // Set up framework for this element.
        $results[$element_key . '_dynamic']['form'][0]['items'][] = [
          'type' => 'section',
          'condition' => '',
          'conditionVariables' => [
            'element' => 'element',
          ],
          'htmlClass' => 'ssa-grid',
          'items' => $form_items,
        ];
      }
    }

    return $results;
  }

  /**
   * @param $results
   *
   * @return mixed
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function patchApiUrls($results) {
    $custom_element_list = $this->getElementsKeyLabel();

    if (count($custom_element_list)) {
      foreach ($this->getElementsKeyLabel() as $element) {

        $results['form_defaults'][$element['value']] = 'form_defaults.' . $element['value'];
        $results['property_group_options'][$element['value']] = 'property_group_options.' . $element['value'];
        $results['forms'][$element['value'] . '_settings'] = 'element_properties.' . $element['value'] . '_settings';
        $results['forms'][$element['value'] . '_dynamic'] = 'element_properties.' . $element['value'] . '_dynamic';

      }
    }

    return $results;
  }

  /**
   * Create form part definition for this form field type.
   *
   * @param $field_key
   * @param $field
   *
   * @return array
   */
  private function fieldcohSelect($field_key, $field) {
    // Set up the <select> options array.
    $options = [];
    if (is_array($field['options'])) {
      foreach ($field['options'] as $value => $label) {
        $options[] = [
          'value' => $value,
          'label' => $label,
        ];
      }
    }

    // Return the field definition.
    $return = [
      'type' => 'section',
      'htmlClass' => $field['htmlClass'] ?? 'col-xs-12',
      'items' => [
        [
          'isStyle' => TRUE,
          'type' => 'cohSelect',
          'key' => $field_key,
          'title' => $field['title'],
          'required' => $field['required'] ?? FALSE,
          'validationMessage' => [
            '302' => $field['validationMessage'] ?? '',
          ],
          'nullOption' => isset($field['nullOption']) && $field['nullOption'],
          'options' => $options,
          'schema' => [
            'type' => [
              'string',
              'number',
            ],
          ],
        ],
      ],
    ];

    if (isset($field['defaultValue'])) {
      $return['items'][0]['defaultValue'] = $field['defaultValue'];
    }

    return $return;
  }

  /**
   * Create form part definition for this form field type.
   *
   * @param $field_key
   * @param $field
   *
   * @return array
   */
  private function fieldcohFileBrowser($field_key, $field) {
    // Return the field definition.
    return [
      'type' => 'section',
      'htmlClass' => $field['htmlClass'] ?? 'col-xs-12',
      'items' => [
        [
          'isStyle' => TRUE,
          'type' => 'cohFileBrowser',
          'key' => $field_key,
          'title' => $field['title'],
          'required' => $field['required'] ?? FALSE,
          'validationMessage' => [
            '302' => $field['validationMessage'] ?? '',
          ],
          'options' => [
            'buttonText' => $field['buttonText'] ?? 'Select file',
          ],
          'schema' => [
            'type' => 'string',
          ],
        ],
      ],
    ];
  }

  /**
   * Create form part definition for this form field type.
   *
   * @param $field_key
   * @param $field
   *
   * @return array
   */
  private function fieldcohWysiwyg($field_key, $field) {
    // Return the field definition.
    $return = [
      'type' => 'section',
      'htmlClass' => $field['htmlClass'] ?? 'col-xs-12',
      'items' => [
        [
          'isStyle' => TRUE,
          'type' => 'cohWysiwyg',
          'key' => $field_key,
          'title' => $field['title'],
          'schema' => [
            'type' => 'object',
          ],
        ],
      ],
    ];

    if (isset($field['defaultValue'])) {
      $return['items'][0]['defaultValue'] = $field['defaultValue'];
    }

    return $return;
  }

  /**
   * Create form part definition for this form field type.
   *
   * @param $field_key
   * @param $field
   *
   * @return array
   */
  private function fieldcohTextBox($field_key, $field) {
    // Return the field definition.
    $return = [
      'type' => 'section',
      'htmlClass' => $field['htmlClass'] ?? 'col-xs-12',
      'items' => [
        [
          'isStyle' => 1,
          'type' => 'cohTextBox',
          'key' => $field_key,
          'title' => $field['title'],
          'placeholder' => $field['placeholder'] ?? '',
          'required' => $field['required'] ?? FALSE,
          'validationMessage' => [
            '302' => $field['validationMessage'] ?? '',
          ],
          'schema' => [
            'type' => 'string',
          ],
        ],
      ],
    ];

    if (isset($field['defaultValue'])) {
      $return['items'][0]['defaultValue'] = $field['defaultValue'];
    }

    return $return;
  }

  /**
   * Create form part definition for this form field type.
   *
   * @param $field_key
   * @param $field
   *
   * @return array
   */
  private function fieldcohTextarea($field_key, $field) {
    // Return the field definition.
    $return = [
      'type' => 'section',
      'htmlClass' => $field['htmlClass'] ?? 'col-xs-12',
      'items' => [
        [
          'isStyle' => 1,
          'type' => 'cohTextarea',
          'key' => $field_key,
          'title' => $field['title'],
          'required' => $field['required'] ?? FALSE,
          'validationMessage' => [
            '302' => $field['validationMessage'] ?? '',
          ],
          'schema' => [
            'type' => 'string',
          ],
        ],
      ],
    ];

    if (isset($field['defaultValue'])) {
      $return['items'][0]['defaultValue'] = $field['defaultValue'];
    }

    return $return;
  }

  /**
   * Create form part definition for this form field type.
   *
   * @param $field_key
   * @param $field
   *
   * @return array
   */
  private function fieldcheckboxToggle($field_key, $field) {
    // Return the field definition.
    $return = [
      'type' => 'section',
      'htmlClass' => $field['htmlClass'] ?? 'col-xs-12',
      'items' => [
        [
          'isStyle' => 1,
          'type' => 'checkboxToggle',
          'key' => $field_key,
          'title' => $field['title'],
          'schema' => [
            'type' => 'string',
          ],
        ],
      ],
    ];

    if (isset($field['notitle'])) {
      $return['items'][0]['notitle'] = $field['notitle'] ?? FALSE;
    }

    if (isset($field['defaultValue'])) {
      $return['items'][0]['defaultValue'] = $field['defaultValue'] ?? FALSE;
    }

    return $return;
  }

  /**
   * Render the custom element using the developers render function.
   *
   * @param $settings
   * @param $markup
   * @param $elementClassName
   * @param $elementContext
   * @param $elementChildren
   *
   * @return array
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function render($settings, $markup, $elementClassName, $elementContext, $elementChildren) {
    // Patch the settings.
    $settings = Json::decode($settings);

    // Decode the markup.
    $markup = Json::decode($markup);

    // Decode the markup.
    $elementContext = Json::decode(str_replace('{{}}', '{}', $elementContext));

    // Get the render function name.
    $elements = $this->getElementsFromManager();

    // Misconfiguration, so return blank array (no render).
    $build = [];

    // And call it.
    if (isset($elements[$settings['element']]) && $elements[$settings['element']]['plugin'] instanceof CustomElementPluginInterface) {
      $build = $elements[$settings['element']]['plugin']->render($settings, $markup, $elementClassName, $elementContext, $elementChildren);
    }

    return $build;
  }

}
