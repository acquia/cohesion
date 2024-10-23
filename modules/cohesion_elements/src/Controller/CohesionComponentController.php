<?php

namespace Drupal\cohesion_elements\Controller;

use Drupal\cohesion\CohesionJsonResponse;
use Drupal\cohesion\Services\CohesionUtils;
use Drupal\cohesion_elements\Entity\Component;
use Drupal\Component\Serialization\Json;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CohesionComponentController.
 *
 * Controller routines for Site Studio component.
 *
 * @package Drupal\cohesion_elements\Controller
 */
class CohesionComponentController extends ControllerBase {

  /**
   * @var \Drupal\Core\TempStore\PrivateTempStore
   */
  protected $tempStore;

  /**
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuid;

  /**
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * @var \Drupal\cohesion\Services\CohesionUtils
   */
  protected $cohesionUtils;

  /**
   * CohesionComponentController constructor.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   * @param \Drupal\Component\Uuid\UuidInterface $uuid_manager
   * @param \Drupal\cohesion\Services\CohesionUtils $cohesionUtils
   */
  public function __construct(
    PrivateTempStoreFactory $temp_store_factory,
    UuidInterface $uuid_manager,
    CohesionUtils $cohesionUtils,
  ) {
    $this->tempStore = $temp_store_factory->get('cohesion');
    $this->uuid = $uuid_manager;
    $this->cohesionUtils = $cohesionUtils;
  }

  /**
   * Retrieves the entity type manager.
   *
   * @return \Drupal\Core\Extension\ThemeHandlerInterface
   *   The entity type manager.
   */
  protected function themeHandler() {
    if (!isset($this->themeHandler)) {
      $this->themeHandler = \Drupal::getContainer()->get('theme_handler');
    }
    return $this->themeHandler;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return \Drupal\cohesion_elements\Controller\CohesionComponentController|\Drupal\Core\Controller\ControllerBase
   */
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('tempstore.private'),
      $container->get('uuid'),
      $container->get('cohesion.utils')
    );
  }

  /**
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   * @throws \Drupal\Core\TempStore\TempStoreException
   */
  public function previewPost(Request $request) {

    $status = 200;
    $result = [];

    try {
      $body = $request->getContent();

      $component = Component::create();

      $send_to_api = $component->apiProcessorManager()->createInstance('templates_api');

      $send_to_api->isPreview(TRUE);
      $send_to_api->setEntity($component);
      $send_to_api->setJsonValues($body);
      $send_to_api->sendWithoutSave();

      $uuid = $this->tempStore->get(__FUNCTION__);
      if (!$uuid) {
        $uuid = $this->uuid->generate();
        $this->tempStore->set(__FUNCTION__, $uuid);
      }

      // If the APi call was successful, then merge the arrays.
      if (($data = $send_to_api->getData()) && \Drupal::service('cohesion.utils')->usedx8Status()) {
        $this->tempStore->set($uuid, $data);
      }

      $result['iframe_url'] = Url::fromRoute('cohesion_elements.component.preview', [
        'key' => $uuid,
        'coh_clean_page' => 'true',
      ])->toString();

    }
    catch (ClientException $e) {
      $status = 500;
      $result = [
        'error' => t('Connection error.'),
      ];
    }

    return new CohesionJsonResponse($result, $status);
  }

  /**
   * Render the contents of the component inside the wrapper template.
   * See: templates/page--cohesionapi--component--preview.html.twig.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return array
   */
  public function preview(Request $request) {
    $key = $request->get('key');

    if ($key) {
      $requestData = $this->tempStore->get($key);
      $data = FALSE;
      if (is_array($requestData)) {
        foreach ($requestData as $theme_data) {
          if ($this->themeHandler()->getDefault() == $theme_data['themeName']) {
            $data = $theme_data;
          }
        }
      }

      if ($data) {
        $template = isset($data['template']) ? Json::decode($data['template']) : [];

        $component_uuid = $this->uuid->generate();

        $build = &drupal_static('component_preview_build');

        $libraries = [
          'cohesion_elements/canvas-preview',
        ];

        if ($this->cohesionUtils->loadCustomStylesOnPageOnly()) {
          $libraries[] = 'cohesion/coh-preview';
        }

        $build = [
          '#type' => 'inline_template',
          '#template' => $template['twig'] ?? '',
          '#attached' => [
            'library' => $libraries,
          ],
          '#context' => [
            'componentUuid' => $component_uuid,
            'isPreview' => TRUE,
          ],
        ];

        $decodedStyles = Json::decode($data['css'])['styles']['added'];
        $component = Component::create();

        $styleValues = '';
        foreach ($decodedStyles as $css) {
          $styleValues .= $component->getApiPluginInstance()->processCssApiEntries($css);
        }

        $content = '<style>' . $styleValues . '</style>';
        $build['#attached']['cohesion'][] = $content;

        return $build;
      }
    }

    return [];
  }

  /**
   * Renders the preview full page wrapper.
   * See: templates/preview-full.html.twig.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return array
   */
  public function previewFull(Request $request) {
    $page = [
      '#theme' => 'component_preview_full',
      '#attached' => [
        'library' => [
          'cohesion/cohesion-admin-scripts',
          'cohesion/cohesion-admin-styles',
        ],
        'drupalSettings' => [
          'cohesion' => [
            'formGroup' => 'canvas',
            'formId' => 'preview',
            'drupalFormId' => 'cohPreviewForm',
          ],
          'cohOnInitForm' => \Drupal::service('settings.endpoint.utils')->getCohFormOnInit('canvas', 'preview'),
        ],
      ],
    ];

    _cohesion_shared_page_attachments($page);

    return $page;
  }

}
