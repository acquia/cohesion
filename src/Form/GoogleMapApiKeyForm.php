<?php

namespace Drupal\cohesion\Form;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Site\Settings;

/**
 * Implements the  form controller.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class GoogleMapApiKeyForm extends ConfigFormBase {

  /**
   * @return \Drupal\Core\Access\AccessResultAllowed|\Drupal\Core\Access\AccessResultForbidden
   */
  public static function access() {
    if (Settings::get('dx8_no_google_keys', FALSE)) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('cohesion.settings');

    $form['google_map_api_key_description'] = [
      '#markup' => t('<p>To use Google Maps on your site you must have a Google account and a Google Maps API key with the correct credentials. Site Studio uses the Maps JavaScript API and Maps Embed API.</p>
                          <p>You can register your project and configure your API key in the <a href="https://console.developers.google.com/flows/enableapi?apiid=maps_backend,geocoding_backend,directions_backend,distance_matrix_backend,elevation_backend,places_backend&reusekey=true" target="_blank">Google API Console</a>.</p>'),
    ];

    $form["google_map_api_key"] = [
      "#type" => "textfield",
      "#title" => $this->t("Google Maps API key"),
      "#required" => FALSE,
      "#default_value" => $config ? $config->get("google_map_api_key") : "",
      '#attributes' => [
        'placeholder' => $this->t("API-KEY"),
      ],
    ];

    $form["google_map_api_key_geo_accordion"] = [
      '#type' => 'details',
      '#collapsible' => TRUE,
      '#title' => $this->t('Google Maps Geocoding API key'),
      '#wrapper_attributes' => ['class' => ['clearfix']],
      '#open' => FALSE,
      '#description' => $this->t('If left blank the Google Maps API key will be used.'),
    ];

    $form["google_map_api_key_geo_accordion"]["google_map_api_key_geo"] = [
      "#type" => "textfield",
      "#title" => $this->t("Google Maps Geocoding API key"),
      "#required" => FALSE,
      "#default_value" => $config ? $config->get("google_map_api_key_geo") : "",
      '#attributes' => [
        'placeholder' => $this->t("GEOCODING-API-KEY"),
      ],
    ];

    // Group submit handlers in an actions element with a key of "actions" so
    // that it gets styled correctly, and so that other modules may add actions
    // to the form. This is not required, but is convention.
    $form['actions'] = [
      '#type' => 'actions',
    ];

    // Add a submit button that handles the submission of the form.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'cohesion.settings',
    ];
  }

  /**
   * Getter method for Form ID.
   *
   * The form ID is used in implementations of hook_form_alter() to allow other
   * modules to alter the render array built by this form controller.  it must
   * be unique site wide. It normally starts with the providing module's name.
   *
   * @return string
   *   The unique ID of the form defined by this class.
   */
  public function getFormId() {
    return 'cohesion_google_map_apikey_form';
  }

  /**
   * Implements form validation.
   *
   * The validateForm method is the default method called to validate input on
   * a form.
   *
   * @param array $form
   *   The render array of the currently built form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object describing the current state of the form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * Implements a form submit handler.
   *
   * The submitForm method is the default method called for any submit elements.
   *
   * @param array $form
   *   The render array of the currently built form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object describing the current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('cohesion.settings');
    $config->set("google_map_api_key", $form_state->getValue("google_map_api_key"));
    $config->set("google_map_api_key_geo", $form_state->getValue("google_map_api_key_geo"));
    $config->save();

    $cohesion_module_libraries = \Drupal::keyValue('cohesion.elements.asset.libraries');
    $gmap_lib = $cohesion_module_libraries->get('element_templates.google-map');

    // Check if gmap_lib is set and value is in array.
    if (!empty($gmap_lib) && is_array($gmap_lib)) {
      // Alter Google Maps API key.
      array_walk_recursive($gmap_lib, function (&$value, $key) use (&$form_state) {
        if ('asset_url' == $key && (strpos($value, 'maps.googleapis.com') !== FALSE) && (strpos($value, 'key') !== FALSE) && $form_state instanceof FormStateInterface) {
          $url_parts = parse_url($value);
          $value = str_replace($url_parts['query'], 'key=' . $form_state->getValue("google_map_api_key"), $value);
        }
      });

      // Override Google Maps API key settings in keyValue storage.
      $cohesion_module_libraries->set('element_templates.google-map', $gmap_lib);
    }

    // Invalidate "libray_info" tag so that udated map api key is loaded.
    \Drupal::service('cache_tags.invalidator')->invalidateTags(['library_info']);

    // Rebuild site routes.
    \Drupal::service('router.builder')->rebuild();

    parent::submitForm($form, $form_state);
  }

}
