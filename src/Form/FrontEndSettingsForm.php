<?php

namespace Drupal\cohesion\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
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
  public function __construct(ConfigFactoryInterface $config_factory, ThemeHandlerInterface $theme_handler, TypedConfigManagerInterface $typed_configmanager) {
    parent::__construct($config_factory, $typed_configmanager);
    $this->setConfigFactory($config_factory);
    $this->cohesionFrontEndSettings = $config_factory->getEditable('cohesion.frontend.settings');
    $this->themeHandler = $theme_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('config.factory'),
      $container->get('theme_handler'),
      $container->get('config.typed')
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
        '#default_value' => $this->cohesionFrontEndSettings->get('js.' . $key) ?? TRUE,
        '#weight' => 10,
      ];
    }

    $form['global-css'] = [
      '#type' => 'details',
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#title' => $this->t('Global CSS settings'),
      '#weight' => -99,
      '#open' => 'panel-open',
    ];

    $form['global-css']['custom_styles_on_page'] = [
      '#title' => $this->t('Only load custom styles needed on the page'),
      '#type' => 'checkbox',
      '#default_value' => $this->cohesionFrontEndSettings->get('css.custom_styles_on_page') ?? FALSE,
      '#description' => $this->t('When this is enabled,
      only the custom styles that are used on the page will be loaded. When this is disabled,
      all custom styles will be loaded. By enabling this it will reduce the amount of
      CSS being loaded on each page, but it will make each page less cachable as each page will have
      a different set of CSS. Enabling will also break any Site Studio custom styles used in custom code, unless the library is attached in the custom code.'),
      '#weight' => 10,
    ];

    $form['global-css']['element_styles_on_page'] = [
      '#title' => $this->t('Only load component & template styles needed on the page'),
      '#type' => 'checkbox',
      '#default_value' => $this->cohesionFrontEndSettings->get('css.element_styles_on_page') ?? FALSE,
      '#description' => $this->t('When this is enabled,
      only the component & template styles that are used on the page will be loaded. When this is disabled,
      all component & template styles will be loaded. By enabling this it will reduce the amount of
      CSS being loaded on each page, but it will make each page less cachable as each page will have
      a different set of CSS.'),
      '#weight' => 10,
    ];

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
      'cohesion.frontend.settings',
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
      $this->cohesionFrontEndSettings->set('js.' . $key, $form_state->getValue($key));
    }

    // If matchHeights enabled then also enable cohMatchHeights
    $this->cohesionFrontEndSettings->set('js.cohMatchHeights', $form_state->getValue('matchHeight'));
    // Set CSS options.
    $this->cohesionFrontEndSettings->set('css.custom_styles_on_page', $form_state->getValue('custom_styles_on_page'));
    $this->cohesionFrontEndSettings->set('css.element_styles_on_page', $form_state->getValue('element_styles_on_page'));

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
