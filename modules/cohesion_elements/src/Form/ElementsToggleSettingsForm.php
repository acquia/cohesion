<?php

namespace Drupal\cohesion_elements\Form;

use Drupal\cohesion_elements\ElementUsageManager;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements the  form controller.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class ElementsToggleSettingsForm extends ConfigFormBase {

  /**
   * @var \Drupal\cohesion\SettingsEndpointUtils
   */
  protected $settingsEndpoint;

  /**
   * Site Studio usage update manager
   *
   * @var \Drupal\cohesion_elements\ElementUsageManager
   */
  protected $elementUsageManager;

  /**
   * @var \Drupal\Core\KeyValueStore\KeyValueFactoryInterface
   */
  protected $keyValue;

  /**
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typed_configmanager
   *   The typed config manager.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    $settingsEndpoint,
    ElementUsageManager $elementUsageManager,
    TypedConfigManagerInterface $typed_configmanager,
    KeyValueFactoryInterface $keyValue,
  ) {
    $this->setConfigFactory($config_factory);
    $this->settingsEndpoint = $settingsEndpoint;
    $this->elementUsageManager = $elementUsageManager;
    $this->keyValue = $keyValue;
    parent::__construct($config_factory, $typed_configmanager);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('config.factory'),
      $container->get('settings.endpoint.utils'),
      $container->get('cohesion_element_usage.manager'),
      $container->get('config.typed'),
      $container->get('keyvalue'),
    );
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Elements endpoint.
    $group = 'elements';
    $assetLibrary = $this->keyValue->get('cohesion.assets.' . $group);
    [$error, $data, $message] = $this->settingsEndpoint->getAssets(FALSE, $assetLibrary, '__ALL__', $group, TRUE);

    // Get "element_toggle" setting in "cohesion.settings".
    $config = $this->config('cohesion.settings');
    $elementToggleSettings = ($config && $config->get("element_toggle")) ? $config->get("element_toggle") : "{}";
    $elementToggleSettings = Json::decode($elementToggleSettings);
    $elementLastRun = $config->get("element_usage_last_run");
    // Check the number of items in the element_usage queue.
    $numberOfItems = $this->elementUsageManager->numberOfItemsInQueue();

    $description = $this->t('Toggle Site Studio elements that your website may not require. Only elements that are not in use can be disabled.');
    $rebuildBtnText = $this->t('Regenerate in use');

    $showTable = 'table';
    $hideSubmitBtn = FALSE;
    $hideUsageActions = FALSE;
    // Hide the table if element usage not been run before.
    if (!isset($elementLastRun)) {
      $showTable = 'hidden';
      $hideSubmitBtn = TRUE;
      $description = $this->t('Please build the element usage report, if you would like to toggle Site Studio elements.');
      $rebuildBtnText = $this->t('Generate report');
    }

    // If there are items in the queue update the description.
    if ($numberOfItems > 0) {
      $showTable = 'hidden';
      $hideSubmitBtn = TRUE;
      $hideUsageActions = TRUE;

      $description = $this->t('@numberOfItems items in queue, once the queue has been processed via cron, elements can be toggled.', [
        '@numberOfItems' => $numberOfItems,
      ]);
    }

    $elementLastRunDescription = '<p><strong>' . $this->t('Element usage report was last run: @runTimeDate', [
      '@runTimeDate' => date('d-m-Y H:i:s', $elementLastRun),
    ]) . '</strong></p>';

    $form['description'] = [
      '#type' => 'markup',
      '#markup' => '<p>' . $description . '</p>' . (isset($elementLastRun) ? $elementLastRunDescription : ''),
    ];

    $form['actions'] = ['#type' => $hideUsageActions ? 'hidden' : 'actions'];

    $form['actions']['dropdown'] = [
      '#type' => 'hidden',
    ];

    $form['actions']['rebuild_element_in_use_batch'] = [
      '#type' => $hideUsageActions ? 'hidden' : 'submit',
      '#name' => 'rebuild_element_usage_batch',
      '#dropbutton' => 'dropdown',
      '#value' => $rebuildBtnText . ' via batch',
      '#button_type' => 'primary',
    ];

    $form['actions']['rebuild_element_in_use_cron'] = [
      '#type' => $hideUsageActions ? 'hidden' : 'submit',
      '#name' => 'rebuild_element_usage_cron',
      '#dropbutton' => 'dropdown',
      '#value' => $rebuildBtnText . ' via cron',
      '#button_type' => 'primary',
    ];

    // Hide the "top" rebuild button when no usage or two will show.
    if (isset($elementLastRun)) {
      $form['actions_top']['rebuild_element_in_use'] = $form['actions'];
    }

    $form['element_toggle'] = [
      '#type' => $showTable,
      '#header' => [
        $this->t('Element'),
        $this->t('In use'),
        $this->t('Enable'),
      ],
      '#id' => 'element_toggle',
      '#attributes' => ['class' => ['element-toggle', 'js-element-toggle']],
      '#sticky' => TRUE,
    ];

    foreach ($data['categories'] as $elementCategory) {
      // Exclude custom elements.
      if ($elementCategory['title'] !== 'custom_elements') {
        $form['element_toggle'][$elementCategory['title']] = [
          [
            '#wrapper_attributes' => [
              'class' => ['element-category'],
              'colspan' => 3,
              'id' => 'element-category-' . $elementCategory['title'],
            ],
            '#markup' => $elementCategory['label'],
          ],
        ];

        foreach ($elementCategory['children'] as $element) {
          $hasInUse = $this->elementUsageManager->hasInUse($element['uid']);

          $form['element_toggle'][$element['uid']]['description'] = [
            '#type' => 'inline_template',
            '#template' => '<div class="element"><span class="title">{{ title }}</span></div>',
            '#context' => [
              'title' => $element['title'],
            ],
          ];

          $form['element_toggle'][$element['uid']]['in-use'] = [
            '#markup' => $this->elementUsageManager->getInUseMarkup($element['uid']),
          ];

          $form['element_toggle'][$element['uid']]['enabled'] = [
            '#title' => $element['title'],
            '#title_display' => 'invisible',
            '#wrapper_attributes' => [
              'class' => ['checkbox'],
            ],
            '#type' => 'checkbox',
            '#default_value' => $elementToggleSettings[$element['uid']] ?? TRUE,
            '#attributes' => [
              'class' => ['element-toggle'],
              '#parents' => [$element['uid']],
            ],
            '#disabled' => $hasInUse ? 'disabled' : FALSE,
          ];
        }
      }
    }

    // Add a submit button that handles the submission of the form.
    $form['actions']['submit'] = [
      '#type' => $hideSubmitBtn ? 'hidden' : 'submit',
      '#value' => $this->t('Save settings'),
      '#button_type' => 'primary',
    ];

    // Attach some styling.
    $form['#attached']['library'][] = 'cohesion_elements/element-toggle';

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
    return 'cohesion_elements_toggle_settings_form';
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
    // Are we triggering an element usage rebuild?
    if ($form_state->getTriggeringElement()['#name'] === 'rebuild_element_usage_batch') {
      $url = Url::fromRoute('cohesion_elements.element_usage_batch');
      $form_state->setRedirectUrl($url);
    } elseif ($form_state->getTriggeringElement()['#name'] === 'rebuild_element_usage_cron') {
      $this->elementUsageManager->buildRequires('cron');
      $this->messenger()->addStatus($this->t('The element usage report has been queued to be generated.'));
    }

    $elementToggle = [];
    if ($form_state->getValue('element_toggle')) {
      foreach ($form_state->getValue('element_toggle') as $element => $value) {
        $elementToggle[$element] = $value['enabled'];
      }
    }

    if ($config = $this->config('cohesion.settings')) {
      $config->set('element_toggle', Json::encode($elementToggle))->save();
    }

    parent::submitForm($form, $form_state);
  }

}
