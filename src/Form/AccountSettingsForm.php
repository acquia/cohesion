<?php

namespace Drupal\cohesion\Form;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Site\Settings;

/**
 * Implements the  form controller.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class AccountSettingsForm extends ConfigFormBase {

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $config = \Drupal::config('cohesion.settings');
    $site_config = \Drupal::config('system.site');

    $form['environment'] = [
      '#type' => 'hidden',
      '#value' => Settings::get('dx8_env', 'production'),
    ];

    // Only show the api key and agency key if this is NOT a sandbox site.
    if (!Settings::get('dx8_no_api_keys', FALSE)) {
      if (is_null($config->get('api_key')) || $config->get('api_key') === '') {
        $message = $this->t('Looking to try Site Studio? Visit <a href=":site-studio-page" target="_blank">Acquia.com</a> to get a sandbox or 30 day trial credentials.', [':site-studio-page' => 'https://www.acquia.com/products/drupal-cloud/site-studio']);
        $form['cta_message'] = [
          '#theme' => 'status_messages',
          '#message_list' => [
            'status' => [$message],
          ],
        ];
      }

      $form['api_key'] = [
        '#type' => 'textfield',
        '#title' => $this->t('API key'),
        '#required' => TRUE,
        '#default_value' => $config ? $config->get('api_key') : '',
        '#disabled' => $this->isOverridden('api_key'),
      ];

      $form['organization_key'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Agency key'),
        '#required' => TRUE,
        '#default_value' => $config ? $config->get('organization_key') : '',
        '#disabled' => $this->isOverridden('organization_key'),
      ];
    }

    $form['site_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Site ID'),
      '#required' => TRUE,
      '#default_value' => $site_config ? $site_config->get('uuid') : '',
      '#attributes' => ['disabled' => 'disabled'],
    ];

    $form['api_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API server URL'),
      '#required' => TRUE,
      '#default_value' => \Drupal::service('cohesion.api.utils')
        ->getAPIServerURL(),
    ];

    if (Settings::get('dx8_editable_version_number', FALSE)) {
      $form['override_version_number'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Override version number'),
        '#required' => TRUE,
        '#default_value' => \Drupal::service('cohesion.api.utils')
          ->getApiVersionNumber(),
      ];
    }

    $options = ['enable' => 'Enable', 'disable' => 'Disable'];
    $index = $config->get('use_dx8');
    $form['use_dx8'] = [
      '#type' => 'radios',
      '#title' => $this->t('Use Site Studio'),
      '#required' => FALSE,
      '#default_value' => isset($options[$index]) ? $config->get('use_dx8') : 'enable',
      '#options' => $options,
      '#wrapper_attributes' => ['class' => ['clearfix']],
      '#attributes' => [
        'class' => [],
      ],
      '#description' => $this->t('Disabling Site Studio will prevent Drupal from making requests to the Site Studio API. Your website will continue to work but you will not be able to access Site Studio features, including the Layout canvas, Style builder and Component builder.'),
    ];

    // The API URL should not be editable.
    if (!Settings::get('dx8_editable_api_url', FALSE)) {
      $form['api_url']['#attributes']['disabled'] = 'disabled';
    }

    // Group submit handlers in an actions element with a key of 'actions' so
    // that it gets styled correctly, and so that other modules may add actions
    // to the form. This is not required, but is convention.
    $form['actions'] = [
      '#type' => 'actions',
    ];

    // Add a submit button that handles the submission of the form.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save and import assets'),
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
    return 'cohesion_account_settings_form';
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
    $api_url = $form_state->getValue('api_url');
    // UrlHelper::isExternal($api_url)
    if (!(UrlHelper::isExternal($api_url) && UrlHelper::isValid($api_url))) {
      $form_state->setErrorByName('api_url', $this->t('API server URL is not valid'));
    }
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

    if (!Settings::get('dx8_no_api_keys', FALSE)) {
      $config->set('api_key', $form_state->getValue('api_key'));
      $config->set('organization_key', $form_state->getValue('organization_key'));
    }

    if (Settings::get('dx8_editable_version_number', FALSE)) {
      $config->set('override_version_number', $form_state->getValue('override_version_number'));
    }

    $config->set('site_id', $form_state->getValue('site_id'));
    $config->set('api_url', $form_state->getValue('api_url'));
    $config->set('use_dx8', $form_state->getValue('use_dx8'));
    $config->save();
    parent::submitForm($form, $form_state);
    if ($form_state->getValue('use_dx8') === 'enable') {
      $form_state->setRedirect('cohesion.configuration.batch');
    }
    else {
      \Drupal::messenger()->addWarning($this->t('Site Studio has been disabled. Your website will continue to work but you will not be able to access Site Studio features, including the Layout canvas, Style builder and Component builder.'));
      // Rebuild routes to deny access to Site Studio menu items.
      \Drupal::service('router.builder')->rebuild();
    }
  }

  /**
   * Check if config variable is overridden by the settings.php.
   *
   * @param string $name
   *   SMTP settings key.
   *
   * @return bool
   *   Boolean.
   */
  protected function isOverridden($name) {
    $original = $this->configFactory->getEditable('cohesion.settings')->get($name);
    $current = $this->configFactory->get('cohesion.settings')->get($name);
    return $original != $current;
  }

}
