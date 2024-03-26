<?php

namespace Drupal\cohesion\Plugin;

/**
 *
 */
use Drupal\Core\Cache\CacheBackendInterface;

/**
 *
 */
class DX8JsonFormUtils {

  /**
   * ??????
   *
   * @param array $form_data
   *
   * @return array
   */
  private function getElementFieldGroupSelectOptions($form_data = []) {
    $results = [];
    if ($form_data) {
      foreach ($form_data as $value) {
        if (isset($value['type']) && $value['type'] == 'cohSelect') {
          $id = isset($value['selectUuid']) ? trim($value['selectUuid']) : trim($value['key']);
          $results[$id] = $value;
        }
        if (isset($value['items'][0])) {
          $results += $this->getElementFieldGroupSelectOptions($value['items']);
        }
      }
    }

    return $results;
  }

  /**
   * ??????
   *
   * @param array $options
   *
   * @return array
   */
  private function getElementFieldGroups($options = []) {
    $data = [];
    $element_properties = \Drupal::keyValue('cohesion.assets.element_properties');
    if ($options) {
      foreach ($options as $option) {
        $results = [];
        $key = $option['keyName'];
        $form = $element_properties->get(str_replace('-', '_', $key)) ?: $element_properties->get($key);

        unset($form['schema']);
        if (isset($form['form']) && ($results = $this->getElementFieldGroupSelectOptions($form['form']))) {
          $data[$key] = [
          // Field groupd label.
            'title' => $option['title'],
          // Select fields.
            'options' => $results,
          ];
        }
      }
    }
    return $data;
  }

  /**
   * Generates list of Element, Styles and Context visibility group options
   * for component select field from DX8's asset data.
   * Generated list is cached until the next DX8 asset import operation or
   * entire site cache is cleared.
   *
   * @return array
   */
  public function loadDX8FormSelectItems() {
    $data = &drupal_static(__FUNCTION__);
    $cid = 'dx8-select-options';
    if (($cache = \Drupal::cache('data')->get($cid))) {
      $data = $cache->data;
    }
    else {
      // Settings.
      if (($property_group_options = \Drupal::keyValue('cohesion.assets.property_group_options'))) {
        $element_info = $this->elementsGroupOptions();
        $data['settings']['title'] = t("Settings");
        $data['settings']['childLabels'] = ["Element", "Field Group", "Field"];
        $element_options = [];
        foreach ($property_group_options->getAll() as $element_id => $values) {
          $options = [];
          foreach ($values as $value) {
            // Ignore JS settings and Admin option.
            if (($value['title'] == 'JS settings') && ($value['title'] == 'Admin')) {
              continue;
            }

            // Set element field group options.
            if (($results = $this->getElementFieldGroups($value['options'])) &&
              isset($element_info[$element_id]['title'])) {
              $element_options[$element_id]['title'] = $element_info[$element_id]['title'];
              $options += $results;
            }
          }

          if ($options) {
            $element_options[$element_id]['options'] = $options;
          }
        }
        $data['settings']['options'] = $element_options;
      }

      // Styles.
      if (($style_builder = \Drupal::keyValue('cohesion.assets.style_builder'))) {
        $styles = [];
        foreach ($style_builder->getAll() as $element_id => $values) {
          if (!isset($values['form'])) {
            continue;
          }

          if (isset($values['schema']) && isset($values['model'])) {
            unset($values['schema'], $values['model']);
          }

          $style_label = ucfirst(strtr($element_id, ['-' => ' ', '_' => ' ']));

          foreach ($values['form'] as $value) {
            if (($field_groups = $this->stylesFieldGroups($value))) {
              $styles[$element_id] = [
              // Style label.
                'title' => $style_label,
              // Load field groups.
                'options' => $field_groups,
              ];
            }
          }
        }
        $data['styles']['title'] = t("Styles");
        $data['styles']['childLabels'] = ["Property", "Field Group", "Field"];
        $data['styles']['options'] = $styles;
      }

      // Context Visibilty.
      $tab_context_visibility = [];
      if (($context_visibility = \Drupal::keyValue('cohesion.assets.element_properties'))) {
        foreach ($context_visibility->getAll() as $element_id => $values) {
          if (isset($values['schema']) && isset($values['model'])) {
            unset($values['schema'], $values['model']);
          }

          if (strpos($element_id, 'tab_context_visibility_') === FALSE) {
            continue;
          }

          $label = ucfirst(strtr($element_id, ['-' => ' ', '_' => ' ']));

          foreach ($values['form'] as $value) {
            if (($field_groups = $this->getCvFieldGroups($value))) {
              $tab_context_visibility[$element_id] = [
              // Style label.
                'title' => $label,
              // Load field groups.
                'options' => $field_groups,
              ];
            }
          }
        }
        $data['context_visibility']['title'] = t("Context Visibilty");
        $data['context_visibility']['childLabels'] = [
          "Property",
          "Field Group",
          "Field",
        ];
        $data['context_visibility']['options'] = $tab_context_visibility;
      }

      \Drupal::cache('data')
        ->set($cid, $data, CacheBackendInterface::CACHE_PERMANENT, ['dx8-form-data-tag']);
    }

    return $data;
  }

  /**
   * Return only arrays with type => cohSelect.
   *
   * @param array $results
   *
   * @return array
   */
  public function stylesFieldGroupSelectOptions($results = []) {
    $data = [];
    foreach ($results as $result) {
      if (isset($result['type']) && $result['type'] !== 'cohSelect') {
        continue;
      }

      $select_field_id = $result['selectUuid'] ?? $result['key'];

      $data[$select_field_id] = $result;
    }
    return $data;
  }

  /**
   * Return only style field groups with select options.
   *
   * @param array $values
   *
   * @return array
   */
  public function stylesFieldGroups($values = []) {
    $data = [];
    if (isset($values['formKey'])) {
      $id = $values['formKey'];

      if (
        isset($values['items']) &&
        ($select_options = $this->stylesFieldGroupSelectOptions($values['items']))
      ) {
        $data[$id] = [
        // Field group title.
          'title' => $values['title'],
          'options' => $select_options,
        ];
      }
      // Process field groups with more multiple options.
      if (isset($values['items']) && isset($values['items'][0])) {
        foreach ($values['items'] as $items) {
          if (isset($items['items']) && ($select_options = $this->stylesFieldGroupSelectOptions($items['items']))) {
            $data[$id] = [
            // Field group title.
              'title' => $values['title'],
              'options' => $select_options,
            ];
          }
        }
      }
    }
    return $data;
  }

  /**
   * Generates context visibility field groups with select options.
   *
   * @return array
   */
  public function getCvFieldGroups($values = []) {
    $data = [];
    if (isset($values['formKey']) && ($id = $values['formKey'])) {
      // Process field groups with more multiple options.
      if (isset($values['items']) && isset($values['items'][0])) {
        foreach ($values['items'] as $items) {
          if (
            isset($items['items']) &&
            ($select_options = $this->cvFieldGroupSelectOptions($items['items']))
          ) {
            $data[$id] = [
            // Field group title.
              'title' => $values['title'],
              'options' => $select_options,
            ];
          }
        }
      }
    }
    return $data;
  }

  /**
   * Generates Context Visibility fields group select options.
   *
   * @param array $results
   *
   * @return array
   */
  public function cvFieldGroupSelectOptions($results = []) {
    $data = [];
    foreach ($results as $result) {
      if (array_key_exists('items', $result) && isset($result['items'][0])) {
        $data += $this->cvFieldGroupSelectOptions($result['items']);
      }

      if (isset($result['type']) && $result['type'] !== 'cohSelect') {
        continue;
      }

      $select_field_id = $result['selectUuid'] ?? $result['key'];
      $data[$select_field_id] = $result;
    }
    return $data;
  }

  /**
   * Map element to element property.
   *
   * @return array
   */
  public function elementToElementPropertyMapper() {
    $static_assets = \Drupal::keyValue('cohesion.assets.static_assets');

    $element_to_option_mapper = array_map(function ($value) {
      $result = explode('.', $value);
      $element_id = end($result);
      return $element_id;
    }, $static_assets->get('api-urls')['property_group_options']);

    $element_mapper_callback = function ($element_id) {
      $elements = \Drupal::keyValue('cohesion.assets.elements')->getAll();
      return $elements[$element_id]['title'];
    };

    $element_mapper = array_map($element_mapper_callback, array_flip($element_to_option_mapper));

    return array_filter($element_mapper);
  }

  /**
   * ??????
   *
   * @param array $data
   *
   * @return array of element property option
   */
  public function elementPropertyOptions($data = []) {

    $results = [];
    if ($data) {
      foreach ($data as $value) {
        if (isset($value['mapperKey']) && $value['mapperKey'] === 'settings') {
          $results += $value;
        }

        if (isset($value) && is_array($value)) {
          $results += $this->elementPropertyOptions($value);
        }
      }
    }
    return $results;
  }

  /**
   * Generates list of element group options.
   *
   * @return array
   */
  public function elementsGroupOptions() {
    $results = [];
    if (!($static_assets = \Drupal::keyValue('cohesion.assets.static_assets'))) {
      return $results;
    }

    $element_forms = \Drupal::keyValue('cohesion.assets.element_forms');
    // Get element/options mapper form 'property_group_options'.
    $property_group_options = array_map(function ($value) {
      $result = explode('.', $value);
      $element_id = end($result);
      return $element_id;
    }, $static_assets->get('api-urls')['property_group_options']);

    $option_to_element = array_map(function ($element_id) {
      return $element_id;
    }, array_flip($property_group_options));

    $element_info = array_flip($option_to_element);

    // List of non-applicable elements to be excluded from select list.
    $excludes = \Drupal::keyValue('cohesion.assets.static_assets')
      ->get('component-select-exclude');

    foreach (\Drupal::keyValue('cohesion.assets.elements')->getAll() as $element_id => $value) {
      if (
        !in_array($element_id, $excludes) &&
        ($element_form = $this->elementPropertyOptions($element_forms->get($element_id)))
      ) {
        $default_key = $element_form['propertyOptionsKey'];
        if (isset($element_info[$default_key])) {
          $id = $element_info[$default_key];
          $results[$id] = [
            'title' => $value['title'],
            'elementId' => $element_id,
          ];
        }
      }
    }
    return $results;
  }

}
