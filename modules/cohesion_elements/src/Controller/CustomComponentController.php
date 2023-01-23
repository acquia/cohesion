<?php

namespace Drupal\cohesion_elements\Controller;

use Drupal\cohesion\CohesionSupportUrl;
use Drupal\cohesion\Event\CohesionJsAppUrlsEvent;
use Drupal\cohesion\SettingsEndpointUtils;
use Drupal\cohesion\UsageUpdateManager;
use Drupal\cohesion_elements\CustomComponentsService;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Custom components controller.
 *
 * @package Drupal\cohesion_elements\Controller
 */
class CustomComponentController extends ControllerBase {

  const CANVAS_NAME = 'config_layout_canvas';
  const MODEL_CLASS_NAME = self::CANVAS_NAME . '_modelAsJson';

  /**
   * Event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * Cohesion settings endpoint.
   *
   * @var \Drupal\cohesion\SettingsEndpointUtils
   */
  protected $settingsEndpointUtils;

  /**
   * Cohesion support url.
   *
   * @var \Drupal\cohesion\CohesionSupportUrl
   */
  protected $cohesionSupportUrl;

  /**
   * Custom Components service.
   *
   * @var \Drupal\cohesion_elements\CustomComponentsService
   */
  protected $customComponents;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Site Studio usage update manager
   *
   * @var \Drupal\cohesion\UsageUpdateManager
   */
  protected $usageUpdateManager;

  /**
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   * @param \Drupal\cohesion\SettingsEndpointUtils $settingsEndpointUtils
   * @param \Drupal\cohesion\CohesionSupportUrl $cohesionSupportUrl
   * @param \Drupal\cohesion_elements\CustomComponentsService $customComponents
   * @param \Drupal\cohesion\UsageUpdateManager $usage_update_manager
   */
  public function __construct(
    EventDispatcherInterface $eventDispatcher,
    SettingsEndpointUtils $settingsEndpointUtils,
    CohesionSupportUrl $cohesionSupportUrl,
    CustomComponentsService $customComponents,
    MessengerInterface $messenger,
    UsageUpdateManager $usage_update_manager
  ) {
    $this->eventDispatcher = $eventDispatcher;
    $this->settingsEndpointUtils = $settingsEndpointUtils;
    $this->cohesionSupportUrl = $cohesionSupportUrl;
    $this->customComponents = $customComponents;
    $this->messenger = $messenger;
    $this->usageUpdateManager = $usage_update_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): CustomComponentController {
    return new static(
      $container->get('event_dispatcher'),
      $container->get('settings.endpoint.utils'),
      $container->get('cohesion.support_url'),
      $container->get('custom.components'),
      $container->get('messenger'),
      $container->get('cohesion_usage.update_manager')
    );
  }

  /**
   * Custom Component Builder.
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function builder(Request $request): array {

    // Does the request have a machine name?
    if ($machineName = $request->attributes->get('machine_name')) {
      if ($component = $this->customComponents->getComponent($machineName)) {
        $formValues = $component['form'];
      }

      // If we're "editing" / "updating" an existing custom component form
      // add a warning.
      $this->messenger->addWarning(t('When making edits to your custom component form, remember to download and place the updated JSON file in your custom component module.'));

    }

    // Attach URLs needed.
    $event = new CohesionJsAppUrlsEvent();
    $this->eventDispatcher->dispatch($event, $event::ADMIN_URL);

    $form = [
      '#type' => 'form',
      '#attributes' => [
        'id' => 'custom_component',
        'class' => [
          'ssa-form',
          'ssa-form-is-loading',
        ],
      ],
      '#attached' => [
        'library' => [
          'cohesion/cohesion-admin-scripts',
          'cohesion/cohesion-admin-styles',
          'cohesion_elements/custom-component-form-builder',
        ],
        'drupalSettings' => [
          'cohesion' => [
            'apps' => [
              self::CANVAS_NAME,
            ],
            'formGroup' => 'custom_component',
            'formId' => 'custom_component',
            'drupalFormId' => 'custom_component',
            'isComponentForm' => TRUE,
            'urls' => $event->getUrls(),
            'entityTypeId' => 'cohesion_component',
            'entityForm' => [
              'json_values' => [
                self::CANVAS_NAME => $formValues ?? "{}",
              ],
            ],
          ],
          'cohOnInitForm' => $this->settingsEndpointUtils->getCohFormOnInit('custom_component', 'custom_component'),
        ],
      ],
      'title' => [
        '#type' => 'hidden',
        '#attributes' => [
          'id' => 'edit-label',
        ],
      ],
    ];

    $form['react_router'] = [
      '#markup' => '<div id="ssaApp" class="ssa-app ssa-is-loading coh-preloader-large"></div>',
      '#weight' => 3,
    ];

    // Field instance.
    $form['cohesion'] = [
      // Drupal\cohesion\Element\CohesionField.
      '#type' => 'cohesionfield',
      '#name' => 'cohesion',
      "#input" => TRUE,
      '#json_values' => '{}',
      '#json_mapper' => '{}',
      '#cohFormGroup' => 'component',
      '#cohFormId' => 'component',
      '#isContentEntity' => FALSE,
      '#canvas_name' => self::CANVAS_NAME,
      '#weight' => 2,
      'json_values' => [
        '#name' => 'json_values',
        '#type' => 'hidden',
        '#title' => t('Values data'),
        '#default_value' => '{}',
        '#value' => '{}',
        '#description' => t('Values data for the website settings.'),
        '#required' => FALSE,
        '#attributes' => [
          'class' => [
            self::MODEL_CLASS_NAME,
            'ssa-app',
          ],
          'id' => self::MODEL_CLASS_NAME,
        ],
      ],
    ];

    $form['download_json'] = [
      '#type' => 'html_tag',
      '#tag' => 'button',
      '#value' => t('Download form JSON'),
      '#weight' => 3,
      '#attributes' => [
        'id' => 'download_json_values',
        'type' => 'button',
        'class' => 'button button--primary',
      ],
    ];

    _cohesion_shared_page_attachments($form);

    return $form;
  }

  /**
   * Displays add links for the available bundles.
   * Redirects to the add form if there's only one bundle available.
   *
   * @param $machine_name
   *
   * @return array
   */
  public function inUse($machine_name) {

    // Fetch the custom component.
    $custom_component = $this->customComponents->getComponent($machine_name);
    // Format it.
    $formatted_custom_component = $this->customComponents->formatAsComponent([$custom_component]);
    $entity = reset($formatted_custom_component);

    $list = $entity->getInUseMessage();

    $rows = function ($result = []) {
      $rows_data = [];
      foreach ($result as $entity) {
        $rows_data[] = [
          [
            'data' => new FormattableMarkup('<a href=":link">@name</a>', [
              ':link' => $entity['url'],
              '@name' => $entity['name'],
            ]),
          ],
        ];
      }
      return $rows_data;
    };

    $in_use_entities = $this->usageUpdateManager->getFormattedInUseEntitiesList($entity);

    foreach ($in_use_entities as $type => $result) {
      $list[] = [
        '#type' => 'details',
        '#open' => TRUE,
        '#title' => $type,
        'table' => [
          '#type' => 'table',
          '#header' => [],
          '#rows' => $rows($result),
        ],
      ];
    }

    return $list;
  }

  /**
   * The title for the custom component in use page, if a custom component
   * was found.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|void
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function inUseTitle(RouteMatchInterface $route_match) {
    $machine_name = $route_match->getParameter('machine_name');

    if ($component = $this->customComponents->getComponent($machine_name)) {
      return $this->t('In use: %name', ['%name' => $component['name']]);
    }
  }

}
