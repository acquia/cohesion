<?php

namespace Drupal\cohesion\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements the  form controller.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class FrontEndSettingsForm extends ConfigFormBase {

  // Global libraries that can be toggled.
  const GLOBAL_JS_LIBRARIES = [
    'matchHeight' => 'Match Heights',
    'parallax_scrolling' => 'Parallax Scrolling',
  ];

  /**
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  protected $cohesionFrontEndSettings;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, ThemeHandlerInterface $theme_handler) {
    parent::__construct($config_factory);
    $this->setConfigFactory($config_factory);
    $this->cohesionFrontEndSettings = $config_factory->getEditable('cohesion.frontend.globaljs_settings');
    $this->themeHandler = $theme_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('theme_handler')
    );
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['global-js'] = [
      '#type' => 'details',
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#title' => $this->t('Global Javascript libraries'),
      '#description' => $this->t('Provides the ability to toggle global libraries used by Site Studio. It is recommended to leave these enabled on existing sites, disabling these could break existing sites that use these libraries.'),
      '#weight' => -99,
      '#open' => 'panel-open',
    ];

    foreach (self::GLOBAL_JS_LIBRARIES as $key => $global_js_lib) {
      $form['global-js'][$key] = [
        '#title' => $global_js_lib,
        '#type' => 'checkbox',
        '#default_value' => $this->cohesionFrontEndSettings->get($key) ?? TRUE,
        '#weight' => 10,
      ];
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
      '#value' => $this->t('Save and rebuild'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'cohesion.frontend.globaljs_settings',
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
    return 'cohesion_frontend_settings_form';
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

    // Set the settings.
    foreach (self::GLOBAL_JS_LIBRARIES as $key => $global_js_lib) {
      $this->cohesionFrontEndSettings->set($key, $form_state->getValue($key));
    }

    // If matchHeights enabled then also enable cohMatchHeights
    $this->cohesionFrontEndSettings->set('cohMatchHeights', $form_state->getValue('matchHeight'));

    // Save.
    $this->cohesionFrontEndSettings->save(TRUE);

    parent::submitForm($form, $form_state);

    // Rebuild cohesion configs.
    $url = Url::fromRoute('cohesion_website_settings.batch_reload');
    if ($url->isRouted()) {
      $form_state->setRedirectUrl($url);
    }
  }

}
