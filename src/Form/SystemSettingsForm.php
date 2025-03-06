<?php

namespace Drupal\cohesion\Form;

use Drupal\cohesion\ImageBrowserPluginManager;
use Drupal\cohesion\ImageBrowserUpdateManager;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements the  form controller.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class SystemSettingsForm extends ConfigFormBase {

  /**
   * Holds the imageBrowserManager service.
   *
   * @var \Drupal\cohesion\ImageBrowserUpdateManager
   */
  protected $imageBrowserManager;

  /**
   * @var \Drupal\cohesion\ImageBrowserPluginManager
   */
  protected $imageBrowserPluginManager;

  /**
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  protected $cohesionSettings;

  /**
   * @var array
   */
  protected $image_browser_object;

  /**
   * Constructs the SystemSettingsForm.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\cohesion\ImageBrowserUpdateManager $imageBrowserManager
   *   The image browser manager.
   * @param \Drupal\cohesion\ImageBrowserPluginManager $imageBrowserPluginManager
   *   The image browser plugin manager.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typed_configmanager
   *   The typed config manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ImageBrowserUpdateManager $imageBrowserManager, ImageBrowserPluginManager $imageBrowserPluginManager, ThemeHandlerInterface $theme_handler, TypedConfigManagerInterface $typed_configmanager) {
    parent::__construct($config_factory, $typed_configmanager);
    $this->setConfigFactory($config_factory);
    $this->imageBrowserManager = $imageBrowserManager;
    $this->imageBrowserPluginManager = $imageBrowserPluginManager;
    $this->cohesionSettings = $config_factory->getEditable('cohesion.settings');
    $this->themeHandler = $theme_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('config.factory'),
      $container->get('cohesion_image_browser.update_manager'),
      $container->get('plugin.manager.image_browser.processor'),
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
    // Default sidebar view.
    $form['sidebar_view_style_accordion'] = [
      '#type' => 'details',
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#title' => $this->t('Default sidebar view style.'),
      '#weight' => -99,
      'sidebar_view_style' => [
        '#type' => 'radios',
        '#title' => $this->t('Default sidebar view style.'),
        '#required' => FALSE,
        '#default_value' => $this->cohesionSettings->get('sidebar_view_style') ?: 'titles',
        '#options' => [
          'titles' => $this->t('Show title'),
          'thumbnails' => $this->t('Show thumbnails'),
        ],
        '#wrapper_attributes' => ['class' => ['clearfix']],
        '#attributes' => [
          'class' => [],
        ],
        '#description' => $this->t('Set the default view style for elements, components and helpers in the sidebar.'),
      ],
      '#open' => 'panel-open',
    ];

    // Log errors group.
    $options = ['enable' => 'Enable', 'disable' => 'Disable'];
    $index = $this->cohesionSettings->get('log_dx8_error');

    $form['log_dx8_error_accordion'] = [
      '#type' => 'details',
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#title' => $this->t('Log errors.'),
      '#weight' => -99,
      'log_dx8_error' => [
        '#type' => 'radios',
        '#title' => $this->t('Log errors.'),
        '#required' => FALSE,
        '#default_value' => isset($options[$index]) ? $this->cohesionSettings->get('log_dx8_error') : 'disable',
        '#options' => $options,
        '#wrapper_attributes' => ['class' => ['clearfix']],
        '#attributes' => [
          'class' => [],
        ],
        '#description' => $this->t('Enabling this will stream javascript errors from the layout canvas, sidebar editor and other features to "Reports -> Recent log messages".'),
      ],
      '#open' => 'panel-open',
    ];

    // Image browser.
    $this->image_browser_object = $this->cohesionSettings->get('image_browser');

    foreach (['config', 'content'] as $browser_type) {
      $form['image_browser_' . $browser_type] = [
        '#type' => 'details',
        '#collapsible' => TRUE,
        '#collapsed' => FALSE,
        '#title' => $this->t('Image browser for @browser_type', ['@browser_type' => $browser_type]),
        '#weight' => -98,
        '#open' => 'panel-open',
      ];

      if ($available_plugins = $this->imageBrowserManager->getAvailablePlugins()) {
        // Build the available plugins up.
        array_walk($available_plugins, function (&$plugin, $key) {
          /** @var \Drupal\cohesion\ImageBrowserPluginInterface $plugin */
          $plugin = $plugin->getName();
        });

        $form['image_browser_' . $browser_type]['image_browser_' . $browser_type] = [
          '#type' => 'select',
          '#title' => $this->t('Select the image browser to use for @browser_type on this site.', ['@browser_type' => $browser_type]),
          '#required' => TRUE,
          '#options' => array_merge([FALSE => 'No image browser'], $available_plugins),
          '#default_value' => $this->image_browser_object[$browser_type]['type'] ?? FALSE,
          '#ajax' => [
            'callback' => $browser_type === 'config' ? '::updateImageBrowserConfig' : '::updateImageBrowserContent',
            'wrapper' => 'edit-image-browser-' . $browser_type . '-wrapper',
          ],
        ];

        $form['image_browser_' . $browser_type]['edit-image-browser-' . $browser_type . '-wrapper'] = [
          '#prefix' => '<div id="edit-image-browser-' . $browser_type . '-wrapper">',
          '#suffix' => '</div>',
        ];
        // Initial state.
        $form['image_browser_' . $browser_type]['edit-image-browser-' . $browser_type . '-wrapper'] = $this->updateImageBrowser($browser_type, $form, $form_state);

      }
      else {
        $form['image_browser_' . $browser_type]['no_modules'] = [
          '#prefix' => '<div class="messages messages--warning">',
          '#suffix' => '</div>',
          '#markup' => $this->t('No image modules have been enabled. Install and enable either Imce File Manager or Entity Browser'),
        ];
      }
    }

    // Animation settings group.
    $form['animate_on_view_accordion'] = [
      '#type' => 'details',
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#title' => $this->t('Animate on view settings'),
      '#weight' => -97,
      'animate_on_view_mobile' => [
        '#type' => 'select',
        '#title' => $this->t('Enable on-view animations on touch devices.'),
        '#required' => TRUE,
        '#options' => [
          'DISABLED' => $this->t('Disabled'),
          'ENABLED' => $this->t('Enabled'),
        ],
        '#default_value' => $this->cohesionSettings->get('animate_on_view_mobile') ? [$this->cohesionSettings->get('animate_on_view_mobile')] : ['DISABLED'],
      ],
      '#open' => 'panel-open',
    ];

    $form['animate_on_scroll_accordion'] = [
      '#type' => 'details',
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#title' => $this->t('Animate on scroll settings'),
      '#weight' => -96,
      'add_animation_classes' => [
        '#type' => 'select',
        '#title' => $this->t('Enable animation classes on body (is-scrolling, is-scrolled, is-scrolled-down, is-scrolled-up)'),
        '#required' => TRUE,
        '#options' => [
          'DISABLED' => $this->t('Disabled'),
          'ENABLED' => $this->t('Enabled'),
        ],
        '#default_value' => $this->cohesionSettings->get('add_animation_classes') ? [$this->cohesionSettings->get('add_animation_classes')] : ['DISABLED'],
      ],
      '#open' => 'panel-open',
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
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * AJAX form callback.
   *
   * @param $browser_type
   * @param $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return mixed
   */
  private function updateImageBrowser($browser_type, $form, FormStateInterface $form_state) {
    $plugin_id = $form_state->getValue('image_browser_' . $browser_type) ?? $this->image_browser_object[$browser_type]['type'] ?? FALSE;

    // Return the form from the image browser plugin.
    if ($plugin_id) {
      // Clean out any markup.
      if (isset($form['image_browser_' . $browser_type]['edit-image-browser-' . $browser_type . '-wrapper']['#markup'])) {
        unset($form['image_browser_' . $browser_type]['edit-image-browser-' . $browser_type . '-wrapper']['#markup']);
      }

      // Add the form from the plugin.
      try {
        $plugin_form = $this->imageBrowserPluginManager->createInstance($plugin_id)
          ->buildForm($form_state, $browser_type, $this->image_browser_object);
      }
      catch (\Exception $e) {
        $plugin_form = [];
      }

      $form['image_browser_' . $browser_type]['edit-image-browser-' . $browser_type . '-wrapper'] = array_merge($plugin_form, $form['image_browser_' . $browser_type]['edit-image-browser-' . $browser_type . '-wrapper']);
    }
    // Nothing selected.
    else {
      $form['image_browser_' . $browser_type]['edit-image-browser-' . $browser_type . '-wrapper']['#markup'] = $this->t('Select an image browser module.');
    }

    return $form['image_browser_' . $browser_type]['edit-image-browser-' . $browser_type . '-wrapper'];
  }

  /**
   * @param $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return mixed
   */
  public function updateImageBrowserConfig($form, FormStateInterface $form_state) {
    return $this->updateImageBrowser('config', $form, $form_state);
  }

  /**
   * @param $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return mixed
   */
  public function updateImageBrowserContent($form, FormStateInterface $form_state) {
    return $this->updateImageBrowser('content', $form, $form_state);
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
    return 'cohesion_system_settings_form';
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
    try {
      $this->imageBrowserPluginManager->createInstance($form_state->getValue('image_browser_config'))
        ->validateForm($form_state);
    }
    catch (\Exception $e) {
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
    // Sidebar view settings.
    $this->cohesionSettings->set('sidebar_view_style', $form_state->getValue('sidebar_view_style'));

    // Error settings.
    $this->cohesionSettings->set('log_dx8_error', $form_state->getValue('log_dx8_error'));

    if ($form_state->getValue('log_dx8_error') === 'enable') {
      $this->messenger()->addStatus(t('Site Studio errors will be logged.'));
    }
    else {
      $this->messenger()->addWarning(t('Site Studio errors will not be logged.'));
    }

    // Animation settings.
    $this->cohesionSettings->set('animate_on_view_mobile', $form_state->getValue('animate_on_view_mobile'));
    $this->cohesionSettings->set('add_animation_classes', $form_state->getValue('add_animation_classes'));

    // Image browser (config).
    $this->image_browser_object = $this->cohesionSettings->get('image_browser');

    if ($this->image_browser_object['config']['type'] = $form_state->getValue('image_browser_config')) {

      try {
        $instance = $this->imageBrowserPluginManager->createInstance($form_state->getValue('image_browser_config'));
        $instance->submitForm($form_state, 'config', $this->image_browser_object);
        $instance->onInit();
      }
      catch (\Exception $e) {
        return;
      }
    }
    else {
      unset($this->image_browser_object['config']);
    }

    // Image browser (content).
    if ($this->image_browser_object['content']['type'] = $form_state->getValue('image_browser_content')) {

      try {
        $instance = $this->imageBrowserPluginManager->createInstance($form_state->getValue('image_browser_content'));
        $instance->submitForm($form_state, 'content', $this->image_browser_object);
        $instance->onInit();
      }
      catch (\Exception $e) {
        return;
      }
    }
    else {
      unset($this->image_browser_object['content']);
    }

    $this->cohesionSettings->set('image_browser', $this->image_browser_object);
    $this->cohesionSettings->save(TRUE);

    // Flush the render cache.
    $renderCache = \Drupal::service('cache.render');
    $renderCache->invalidateAll();

    parent::submitForm($form, $form_state);
  }

}
