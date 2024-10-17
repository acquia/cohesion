<?php

namespace Drupal\cohesion_elements\Controller;

use Drupal\cohesion_elements\ElementUsageManager;
use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\Core\Utility\Error;

/**
 * Elements controller.
 *
 * @package Drupal\cohesion_elements\Controller
 */
class ElementsController extends ControllerBase {

  /**
   * Site Studio element usage update manager
   *
   * @var \Drupal\cohesion_elements\ElementUsageManager
   */
  protected $elementUsageManager;

  /**
   *
   * @param \Drupal\cohesion_elements\ElementUsageManager $elementUsageManager
   */
  public function __construct(ElementUsageManager $elementUsageManager) {
    $this->elementUsageManager = $elementUsageManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('cohesion_element_usage.manager'),
    );
  }

  /**
   * Return list of all categories for a given element type.
   *
   * @param string $entity_type_id
   *   Type of category entity, i.e. helpers, components, etc.
   *
   * @param bool $bypass_permission_check
   *
   * @return array|bool
   *   Return array of category objects,
   *   or FALSE if element is not of a supported type.
   */
  public static function getElementCategories($entity_type_id, $bypass_permission_check = FALSE) {
    // Get list of categories sorted by weight.
    try {
      $storage = \Drupal::entityTypeManager()->getStorage($entity_type_id);
    }
    catch (\Throwable $e) {
      return [];
    }

    $category_entities = $storage->getQuery()
      ->accessCheck(TRUE)
      ->sort('weight')
      ->execute();
    $categories = [];

    if ($category_entities = $storage->loadMultiple($category_entities)) {
      /** @var ElementCategoryBase $entity */
      foreach ($category_entities as $entity) {
        if ($entity->hasGroupAccess() || $bypass_permission_check) {
          // Add to the array.
          $categories[$entity->id()] = [
            'label' => $entity->label(),
            'class' => $entity->getClass(),
            'id' => $entity->id(),
          ];
        }
      }
    }

    return $categories;
  }

  /**
   * @param $entity_type
   * @param $element_id
   * @return false|mixed|string
   */
  public static function getElementPreviewImageURL($entity_type, $element_id) {
    // Get list of entities matching the specified type.
    try {
      $storage = \Drupal::entityTypeManager()->getStorage($entity_type);
    }
    catch (PluginException $exception) {
      Error::logException('cohesion_elements', $exception);
    }

    if (isset($storage)) {
      $query = $storage->getQuery()
        ->accessCheck(TRUE)
        ->condition('id', $element_id, '=')
        ->range(0, 1);
      $ids = $query->execute();
      $entities = $storage->loadMultiple($ids);

      foreach ($entities as $entity) {
        // Check the component/helper has a preview image defined.
        if ($preview_image = $entity->getPreviewImage()) {
          if ($file = File::load($preview_image)) {
            if ($is = ImageStyle::load('dx8_component_preview')) {
              $url = $is->buildUrl($file->getFileUri());
              $url = parse_url($url);
              $decoded = $url['path'];

              if (isset($url['query']) && !empty($url['query'])) {
                $decoded .= '?' . $url['query'];
              }

              return $decoded;
            }
          } else {
            return FALSE;
          }
        }
      }
    }

    return FALSE;
  }

  /**
   * @param $element
   * @return array[]
   */
  public function inUse($element) {

    $list = $this->getInUseMessage();

    $rows = function ($result = []) {
      $rowsData = [];
      foreach ($result as $entity) {
        $rowsData[] = [
          [
            'data' => new FormattableMarkup('<a href=":link">@name</a>', [
              ':link' => $entity['url'],
              '@name' => $entity['name'],
            ]),
          ],
        ];
      }
      return $rowsData;
    };

    $inUseEntities = $this->elementUsageManager->getFormattedInUseEntitiesList($element);

    foreach ($inUseEntities as $type => $result) {
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
   * @param $element
   * @return mixed
   */
  public function inUseTitle($element) {
    // make the title more readable.
    $title = ucfirst(str_replace('-', ' ', $element));

    return $title . ' element usage';
  }

  /**
   * @return array[]
   */
  public function getInUseMessage() {
    return [
      'message' => [
        '#markup' => t('This element has been tracked as in use in the places listed below. You can not disable it until you have removed its use.'),
      ],
    ];
  }

  /**
   * @return mixed
   */
  public function runElementUsageBatch() {
    batch_set($this->setElementUsageBatch());
    return batch_process(Url::fromRoute('cohesion_elements.configuration.elements_toggle_settings')->toString());
  }

  /**
   *
   * Setup element usage batch.
   * @return mixed
   */
  public static function setElementUsageBatch() {

    return (new BatchBuilder())
      ->setFile(\Drupal::service('extension.path.resolver')->getPath('module', 'cohesion_elements') . '/cohesion_elements.batch.inc')
      ->setTitle(t('Regenerating element usage report'))
      ->setInitMessage(t('Regenerating element usage report'))
      ->setFinishCallback('element_usage_batch_finished')
      ->setErrorMessage(t('Failed to build element usage report'))
      ->addOperation('_rebuild_element_usage', [])->toArray();
  }

}
