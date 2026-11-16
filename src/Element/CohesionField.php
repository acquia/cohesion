<?php

namespace Drupal\cohesion\Element;

use Drupal\cohesion\Entity\CohesionSettingsInterface;
use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\Event\CohesionJsAppUrlsEvent;
use Drupal\cohesion_elements\Entity\CohesionLayout;
use Drupal\Component\Utility\Environment;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Site\Settings;

/**
 * Provides a layout form element. This is used in the BaseForm and
 * the CohesionLayout field formatter.
 *
 * @FormElement("cohesionfield")
 */
class CohesionField extends FormElement {

  /**
   * @return array
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#process' => [
        [$class, 'processCohesion'],
      ],
      '#pre_render' => [
        [$class, 'processCohesionError'],
      ],
      '#element_validate' => [
        [$class, 'validateElement'],
      ],
    ];
  }

  /**
   * @param $element
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public static function validateElement(&$element, FormStateInterface $form_state) {
    $entity = $element['#entity'];
    if ($entity instanceof CohesionSettingsInterface || $entity instanceof CohesionLayout) {
      $entity->setJsonValue($element['#json_values']);
      if (!$entity->isLayoutCanvas()) {
        $entity->setJsonMapper($element['#json_mapper']);
      }
      $errors = $entity->jsonValuesErrors();

      if ($errors !== FALSE) {
        // If errors has uuid it is a layout canvas error.
        if (isset($errors['uuid'])) {
          // Set the uuid so it can be added to drupalSettings later.
          $uuids = &drupal_static('cohesion_layout_canvas_error');
          if (is_null($uuids)) {
            $uuids = [];
          }
          $uuids[] = $errors['uuid'];
        }

        $form_state->setError($element, $errors['error']);
      }
    }
  }

  /**
   * @param $element
   *
   * @return mixed
   */
  public static function processCohesionError($element) {

    if (isset($element['#errors'])) {
      $uuids = &drupal_static('cohesion_layout_canvas_error');
      $element['#attached']['drupalSettings']['cohesion']['layout_canvas_errors'] = $uuids;
    }
    return $element;
  }

  /**
   * Get layoutCanvas field names for the current fieldable entity.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  protected static function getLayoutCanvasFields(FormStateInterface $form_state) {
    $layout_fields = [];
    $entity = $form_state->getFormObject()->getEntity();
    if ($entity instanceof FieldableEntityInterface) {
      $definitions = $entity->getFieldDefinitions();
      foreach ($definitions as $definition) {
        if ($definition->getType() == 'cohesion_entity_reference_revisions') {
          $layout_fields[] = $definition->getName();
        }
      }
    }

    return $layout_fields;
  }

  /**
   * @param $element
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param $complete_form
   *
   * @return mixed
   */
  public static function processCohesion(&$element, FormStateInterface $form_state, &$complete_form) {

    if (isset($element['#title'])) {
      $element['title'] = [
        '#type' => 'label',
        '#title' => $element['#title'],
        '#required' => $element['#required'] ?? FALSE,
        '#weight' => 0,
      ];
    }

    // Prevent progress spinning wheel from loading if form is field config
    // form.
    $is_loading = ($form_state->getFormObject()
      ->getFormId() == 'field_config_edit_form') ? '' : 'ssa-is-loading';

    // Add the entity style.
    $matches = [
      'cohesion-custom-style' => 'cohesion_custom_style',
      'cohesion-style-helper' => 'cohesion_custom_style',
      'cohesion-base-styles' => 'cohesion_base_styles',
      'cohesion-content-templates' => 'cohesion_content_templates',
      'cohesion-master-templates' => 'cohesion_master_templates',
      'cohesion-view-templates' => 'cohesion_view_templates',
      'cohesion-menu-templates' => 'cohesion_menu_templates',
      'cohesion-helper' => 'cohesion_helper',
      'cohesion-component' => 'cohesion_component',
      'cohesion-style-guide' => 'cohesion_style_guide',
    ];

    foreach ($matches as $search => $entityTypeId) {
      if (strstr($complete_form['#attributes']['data-drupal-selector'], $search)) {
        $element['#attached']['drupalSettings']['cohesion']['entityTypeId'] = $entityTypeId;
        break;
      }
    }

    $json_values = NULL;
    // Add the json values.
    if ($element['#entity'] instanceof EntityJsonValuesInterface && $element['#entity']->isLayoutCanvas()) {

      // If state exists - apply to the entity.
      $layout_fields = self::getLayoutCanvasFields($form_state);
      if ($form_state->isProcessingInput() && $form_state->getValues() && !empty($layout_fields)) {
        foreach ($layout_fields as $field) {
          if (strstr($element['#canvas_name'], $field) !== FALSE) {
            $form_state_value = $form_state->getValue($field);
            if (!is_numeric($form_state_value)) {
              $element['#entity']->setJsonValue($form_state_value);
            }
          }
        }
      }

      if ($payload = \Drupal::service('cohesion.utils')->getPayloadForLayoutCanvasDataMerge($element['#entity'])) {
        $response = \Drupal::service('cohesion.api_client')->layoutCanvasDataMerge($payload);

        if ($response && $response['code'] == 200) {
          $json_values = $response['data']->layoutCanvas;
          $element['#attached']['drupalSettings']['cohesion']['deletedComponents'] = $response['data']->deletedComponents;
        }
        else {
          throw new \Exception('Unable to parse layout canvas: ' . $response['data']['error']);
        }
      }
    }

    if (empty($json_values)) {
      $json_values = json_decode($element['#json_values']);
    }

    if (isset($element['#canvas_name'])) {
      $drupal_settings_json_values = [$element['#canvas_name'] => $json_values];
    }
    else {
      $drupal_settings_json_values = $json_values;
    }

    // Add the data.
    $element['#attached']['drupalSettings']['cohesion']['entityForm']['json_values'] = $drupal_settings_json_values;
    if (isset($element['#json_mapper'])) {
      $element['#attached']['drupalSettings']['cohesion']['entityForm']['json_mapper'] = json_decode($element['#json_mapper']);
    }

    // Add the max file size.
    $element['#attached']['drupalSettings']['cohesion']['upload_max_filesize'] = Environment::getUploadMaxSize();

    // Image browser page attachments.
    \Drupal::service('cohesion_image_browser.update_manager')
      ->sharedPageAttachments($element['#attached'], $element['#isContentEntity'] ? 'content' : 'config');

    // Attach the editor.module text format settings.
    $pluginManager = \Drupal::service('plugin.manager.editor');

    // Get the filter formats the current user has permissions to access.
    $formats = filter_formats(\Drupal::currentUser());
    $format_ids = array_keys($formats);
    $element['#attached'] = BubbleableMetadata::mergeAttachments($element['#attached'], $pluginManager->getAttachments($format_ids));

    // Patch the text format labels ("Full HTML") into the Drupal settings.
    if (isset($element['#attached']['drupalSettings']['editor']['formats'])) {
      foreach ($element['#attached']['drupalSettings']['editor']['formats'] as $key => $settings) {
        if (isset($formats[$key])) {
          $element['#attached']['drupalSettings']['editor']['formats'][$key]['label'] = $formats[$key]->get('name');
        }
      }
    }

    $element['#attached']['drupalSettings']['editor']['default'] = NULL;
    if (isset($element['#attached']['drupalSettings']['editor']['formats']['cohesion'])) {
      $element['#attached']['drupalSettings']['editor']['default'] = 'cohesion';
    }
    elseif (isset($element['#attached']['drupalSettings']['editor']['formats']) && is_array($element['#attached']['drupalSettings']['editor']['formats'])) {
      $last_format = end($element['#attached']['drupalSettings']['editor']['formats']);
      if ($last_format && isset($last_format['format'])) {
        $element['#attached']['drupalSettings']['editor']['default'] = $last_format['format'];
      }
    }

    // Attach the Angular app.
    $element['#attached']['library'][] = 'cohesion/cohesion-admin-scripts';
    $element['#attached']['library'][] = 'cohesion/cohesion-admin-styles';

    // Load icon library for admin pages if it has been generated.
    $icon_lib_path = COHESION_CSS_PATH . '/cohesion-icon-libraries.css';
    if (file_exists($icon_lib_path)) {
      $element['#attached']['library'][] = 'cohesion/admin-icon-libraries';
    }

    // Load responsive grid settings for admin pages if it has been generated.
    $grid_lib_path = COHESION_CSS_PATH . '/cohesion-responsive-grid-settings.css';
    if (file_exists($grid_lib_path)) {
      $element['#attached']['library'][] = 'cohesion/admin-responsive-grid-settings';
    }

    // Set Global form attributes.
    $complete_form['toast'] = [
      '#type' => 'item',
      '#markup' => '<toast></toast>',
      '#allowed_tags' => ['toast'],
      '#parents' => [],
    ];

    $classes = ['ssa-form', 'ssa-form-is-loading'];
    if (isset($element['#classes']) && is_array($element['#classes'])) {
      $classes = array_merge($classes, $element['#classes']);
    }

    $complete_form['#attributes']['class'] = array_merge($complete_form['#attributes']['class'], $classes);
    $complete_form['#attributes']['name'] = 'forms.formRenderer';

    $complete_form['#attached']['drupalSettings']['cohesion']['isLoading'] = $is_loading;
    $complete_form['#attached']['drupalSettings']['cohesion']['drupalFormId'] = $complete_form['#id'];

    if (!isset($complete_form['#attached']['drupalSettings']['cohesion']['formGroup']) && !isset($complete_form['#attached']['drupalSettings']['cohesion']['formId'])) {
      $complete_form['#attached']['drupalSettings']['cohesion']['formGroup'] = $element['#cohFormGroup'];
      $complete_form['#attached']['drupalSettings']['cohesion']['formId'] = $element['#cohFormId'];
      $complete_form['#attached']['drupalSettings']['cohOnInitForm'] = \Drupal::service('settings.endpoint.utils')
        ->getCohFormOnInit($element['#cohFormGroup'], $element['#cohFormId']);
    }

    // Define field.
    $class_name_canvas = 'ssaApp';
    $model_class_name = '_modelAsJson';
    $mapper_class_name = '_mapperAsJson';
    if (isset($element['#canvas_name'])) {
      $complete_form['react_router'] = [
        '#markup' => '<div id="' . $class_name_canvas . '" class="ssa-app"></div>',
        '#parents' => [],
        // Suppresses https://www.drupal.org/project/drupal/issues/3027240
      ];
      $class_name_canvas = $element['#canvas_name'];
      $complete_form['#attached']['drupalSettings']['cohesion']['apps'][] = $class_name_canvas;
      $model_class_name = $class_name_canvas . $model_class_name;
      $mapper_class_name = $class_name_canvas . $mapper_class_name;
    }

    $element['react_app'] = [
      '#markup' => '<div class="coh-form ssa-app ssa-is-loading coh-preloader-large" id="' . $class_name_canvas . '"></div>',
      '#weight' => 1,
    ];

    // Add the token browser.
    if (isset($element['#token_browser'])) {
      // Build the token tree (token.module).
      // Check if it's an array of "allowed" tokens if not put into an array.
      if (!is_array($token_browser = $element['#token_browser'])) {
        $token_browser = [$element['#token_browser']];
      }

      $token_tree = [
        '#theme' => 'token_tree_link',
        '#token_types' => ($element['#token_browser'] == 'all') ? 'all' : $token_browser,
        // Token types (usually 'node').
      ];

      // Render it using the service.
      \Drupal::service('renderer')->render($token_tree);

      // Attach the bootstrap fix to the form element.
      $element['#attached']['library'][] = 'cohesion/cohesion_token';
    }

    $event = new CohesionJsAppUrlsEvent($form_state);
    $event_dispatcher = \Drupal::service('event_dispatcher');
    $event_dispatcher->dispatch($event, $event::ADMIN_URL);
    $element['#attached']['drupalSettings']['cohesion']['urls'] = $event->getUrls();

    $element['json_values'] = [
      '#type' => 'hidden',
      '#title' => t('Values data'),
      '#default_value' => '{}',
      '#description' => t('Values data for the website settings.'),
      '#required' => FALSE,
      '#attributes' => [
        'class' => [$model_class_name],
        'id' => [$model_class_name],
      ],
      '#weight' => 3,
    ];

    if (isset($element['#json_mapper'])) {
      $element['json_mapper'] = [
        '#type' => 'hidden',
        '#title' => t('Mapper'),
        '#default_value' => '{}',
        '#description' => t("mapper for the Site Studio website settings."),
        '#required' => FALSE,
        '#weight' => 5,
        '#attributes' => [
          'class' => [$mapper_class_name],
          'id' => [$mapper_class_name],
        ],
      ];
    }

    // Show the JSON field.
    $show_json_fields = FALSE;

    $config = \Drupal::configFactory()->getEditable('cohesion_devel.settings');

    // Check config.
    if ($config && $config->get("show_json_fields")) {
      $show_json_fields = TRUE;
    }
    // Check global $settings[].
    else {
      if (Settings::get('dx8_json_fields', FALSE)) {
        $show_json_fields = TRUE;
      }
    }

    if ($show_json_fields) {
      $element['#attached']['drupalSettings']['cohesion']['showJsonFields'] = TRUE;

      $element['json_values_view'] = [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => [
          'class' => [$model_class_name . 'View', 'coh-devel-json-textarea'],
          'id' => [$model_class_name . 'View'],
          'title' => 'Model',
          'rows' => 8,
          'readonly' => '',
        ],
        '#weight' => 4,
      ];

      if (isset($element['#json_mapper'])) {
        $element['json_mapper_view'] = [
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#attributes' => [
            'class' => [$mapper_class_name . 'View', 'coh-devel-json-textarea'],
            'id' => [$mapper_class_name . 'View'],
            'title' => 'Mapper',
            'rows' => 8,
            'readonly' => '',
          ],
          '#weight' => 6,
        ];
      }
    }

    // Return the form definitions.
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    // Return element configuration if set.
    if ($input && is_array($input) && array_key_exists('json_values', $input)) {
      $element['#json_values'] = $input['json_values'];

      if (isset($element['#json_mapper'])) {
        $element['#json_mapper'] = $input['json_mapper'];
      }
      return $input['json_values'];
    }

    // Return NULL otherwise.
    return NULL;
  }

}
