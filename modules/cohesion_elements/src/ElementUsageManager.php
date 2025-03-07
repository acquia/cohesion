<?php

namespace Drupal\cohesion_elements;

use Drupal\cohesion\ExceptionLoggerTrait;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;

/**
 * Class ElementUsageManager.
 *
 * This service is what tracks Site Studio element usage.
 *
 * @package Drupal\cohesion_elements
 */
class ElementUsageManager {

  use ExceptionLoggerTrait;

  /**
   * Array of cohesion entities that can contain elements.
   */
  const CAN_CONTAIN_ELEMENTS = [
    'cohesion_component',
    'cohesion_helper',
    'cohesion_master_templates',
    'cohesion_menu_templates',
    'cohesion_content_templates',
    'cohesion_view_templates',
    'cohesion_layout',
  ];

  /**
   * Holds the database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Holds the entity repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepository
   */
  protected $entityRepository;

  /**
   * Holds the entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Stores Plugin Definitions.
   *
   * @var array|mixed[]|null
   */
  protected $definitions;

  /**
   * Logger Channel Factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerChannelFactory;

  /**
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The datetime.time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $timeService;

  /**
   * The queue service.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  protected $queueFactory;


  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * @var \Drupal\cohesion\SettingsEndpointUtils
   */
  protected $settingsEndpoint;

  /**
   * @var \Drupal\Core\KeyValueStore\KeyValueFactoryInterface
   */
  protected $keyValue;

  /**
   * ElementUsageManager constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   * @param \Drupal\Core\Entity\EntityRepository $entityRepository
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   * @param \Drupal\Core\Queue\QueueFactory $queueFactory
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   * @param \Drupal\Core\KeyValueStore\KeyValueFactoryInterface $keyValue
   */
  public function __construct(
    Connection $connection,
    EntityRepository $entityRepository,
    EntityTypeManagerInterface $entityTypeManager,
    LoggerChannelFactoryInterface $loggerChannelFactory,
    RendererInterface $renderer,
    TimeInterface $time_service,
    QueueFactory $queueFactory,
    ConfigFactoryInterface $configFactory,
    $settingsEndpoint,
    KeyValueFactoryInterface $keyValue,
  ) {
    $this->connection = $connection;
    $this->entityRepository = $entityRepository;
    $this->entityTypeManager = $entityTypeManager;
    $this->loggerChannelFactory = $loggerChannelFactory;
    $this->renderer = $renderer;
    $this->timeService = $time_service;
    $this->queueFactory = $queueFactory;
    $this->configFactory = $configFactory;
    $this->settingsEndpoint = $settingsEndpoint;
    $this->keyValue = $keyValue;
  }

  /**
   * Update the coh_element_usage table with an entities dependencies.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return int
   *
   * @throws \Exception
   */
  public function buildRequires($method) {
    // Get a list of elements.
    $group = 'elements';
    $type = '__ALL__';
    $assetLibrary = $this->keyValue->get('cohesion.assets.' . $group);
    [$error, $elements, $message] = $this->settingsEndpoint->getAssets(FALSE, $assetLibrary, $type, $group, FALSE);

    // Try deleting existing usage.
    try {
      $deleteUsage = $this->removeUsage();
    } catch (\Exception $e) {
      // DB connection problem.
      $this->logException($e);
      return '';
    }

    // Once existing usage has been deleted, re-populate.
    if ($deleteUsage) {
      foreach (self::CAN_CONTAIN_ELEMENTS as $entityCanContainElements) {
        // Fetch entities for specific entity that can contain elements.
        $entityIds = $this->entityTypeManager
          ->getStorage($entityCanContainElements)
          ->getQuery()
          ->accessCheck(FALSE)
          ->execute();

        // Slice and process 50 at a time.
        for ($i = 0; $i < count($entityIds); $i += 50) {
          $ids = array_slice($entityIds, $i, 50);
          $entities = $this->entityTypeManager->getStorage($entityCanContainElements)->loadMultiple($ids);

          if ($method === 'batch') {
            foreach ($entities as $entity) {
              // Check if the layout canvas has any elements.
              $layoutCanvas = $entity->getLayoutCanvasInstance();
              if (!$layoutCanvas->hasElements()) {
                continue;
              }

              $this->processItem($entity, $layoutCanvas, $elements);
            }
          } elseif ($method === 'cron') {
            $job = [
              'entities' => $entities,
              'elements' => $elements,
              'timestamp' => $this->timeService->getCurrentTime(),
            ];

            $this->queueFactory->get('element_usage')->createItem($job);
          }
        }
      }
    }

    if ($method === 'batch') {
      // Update element usage last run date/time.
      $dateTime = $this->timeService->getCurrentTime();
      $this->configFactory->getEditable('cohesion.settings')->set('element_usage_last_run', $dateTime)->save();
    }

    return '';
  }

  /**
   * Process an entity to see if it has elements.
   *
   * @param $entity
   * @param $layoutCanvas
   * @param $elements
   * @return array|void
   */
  public function processItem($entity, $layoutCanvas, $elements) {
    foreach ($layoutCanvas->iterateCanvas() as $item) {
      // Check the item on the canvas is a default "element".
      if (!array_key_exists($item->getProperty('uid'), $elements)) {
        continue;
      }

      $sourceUuid = $entity->uuid();
      $sourceType = $entity->getEntityTypeId();
      // If it's a cohesion layout on a node we need to get the parent info.
      if ($entity->getEntityTypeId() === 'cohesion_layout') {
        $parentEntity = $entity->getParentEntity();
        $sourceUuid = $parentEntity->uuid();
        $sourceType = $parentEntity->getEntityTypeId();
      }

      // Insert element usage into the database.
      try {
        $this->connection->insert('coh_element_usage')->fields([
          'element' => $item->getProperty('uid'),
          'source_uuid' => $sourceUuid,
          'source_type' => $sourceType,
        ])->execute();
      } catch (\Exception $e) {
        // DB connection problem.
        $this->logException($e);
        return [];
      }
    }
  }

  /**
   * Remove all element usage data.
   *
   * @return bool
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function removeUsage() {
    // Delete data from the usage table.
    try {
      $this->connection->delete('coh_element_usage')->execute();
    }
    catch (\Exception $e) {
      $this->logException($e);
      return FALSE;
    }

    return TRUE;
  }

  /**
   * @param string $element
   * @return bool
   */
  public function isElementInUse(string $element) {
    try {
      $usage = $this->connection->select('coh_element_usage', 'c1')
        ->fields('c1', ['source_uuid', 'source_type'])
        ->condition('c1.element', $element, '=')
        ->execute()
        ->fetchAllKeyed();
    }
    catch (\Exception $e) {
      $this->logException($e);
      return FALSE;
    }

    if ($usage) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Get the raw list of in-use elements by using element UID.
   *
   * @param string $element
   *   element string.
   *
   * @return array
   *
   */
  public function getInUseElementsList(string $element): array {
    // Get the usage from the table (right hand side lookup).
    try {
      $usage = $this->connection->select('coh_element_usage', 'c1')
        ->fields('c1', ['source_uuid', 'source_type'])
        ->condition('c1.element', $element, '=')
        ->execute()
        ->fetchAllKeyed();
    }
    catch (\Exception $e) {
      $this->logException($e);
      return [];
    }

    return $usage;
  }

  /**
   * Is a specific element in use?
   *
   * @return bool
   */
  public function hasInUse($element) {
    // Get the usage from the table (right hand side lookup).
    try {
      $query = $this->connection->select('coh_element_usage', 'c1')
        ->fields('c1', ['source_uuid', 'source_type'])
        ->condition('c1.element', $element, '=');

      $usage = $query->countQuery()->execute()->fetchField();

    }
    catch (\Exception $e) {
      // DB connection problem.
      return FALSE;
    }

    return $usage > 0;
  }

  /**
   * Is the coh_element_usage table empty?
   *
   * @return bool
   */
  public function isElementUsageEmpty() {
    try {
      $query = $this->connection->select('coh_element_usage');
      $count = $query->countQuery()->execute()->fetchField();
    }
    catch (\Exception $e) {
      $this->logException($e);
      return FALSE;
    }

    if ($count === "0") {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * The in-use modal calls this to get a grouped and formatted list of
   * dependencies.
   * This function is potentially expensive to perform.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return array
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function getFormattedInUseEntitiesList($element) {
    $grouped = [];
    $usage = $this->getInUseElementsList($element);

    // Build the grouped list.
    foreach ($usage as $source_uuid => $source_type) {
      // Load the entity, so we can get the title and url.
      /** @var \Drupal\Core\Entity\EntityInterface $entity */
      $entity = $this->entityRepository->loadEntityByUuid($source_type, $source_uuid);

      if ($entity) {
        // Get the edit URL.
        try {
          $entity_edit_url = $entity->toUrl('edit-form')->toString();
        }
        catch (\Exception $e) {
          $entity_edit_url = FALSE;
        }

        // Get the group label (from entity type).
        $group_label = $this->entityTypeManager->getDefinition($source_type)
          ->getLabel()
          ->render();

        // Update the grouped list.
        $grouped[$group_label][] = [
          'uuid' => $source_uuid,
          'name' => $entity->label(),
          'url' => $entity_edit_url,
          'entity_type' => $entity->getEntityTypeId(),
        ];
      }
    }

    return $grouped;
  }

  /**
   * @param $element
   * @return \Drupal\Component\Render\MarkupInterface
   * @throws \Exception
   */
  public function getInUseMarkup($element) {
    if ($this->hasInUse($element)) {
      $markup = [
        '#type' => 'link',
        '#title' => t('In use'),
        '#url' => URL::fromRoute('element_usage.' . $element . '.in_use', [
          'element' => $element,
        ]),
        '#options' => [
          'attributes' => [
            'class' => ['use-ajax'],
            'data-dialog-type' => 'modal',
            'data-dialog-options' => Json::encode([
              'width' => 700,
            ]),
          ],
        ],
        '#attached' => [
          'library' => [
            'core/drupal.dialog.ajax',
          ],
        ],
      ];
    }
    else {
      $markup = [
        '#markup' => t('Not in use'),
      ];
    }

    return $this->renderer->render($markup);
  }

  /**
   * Build a list of disabled elements.
   *
   * @return array
   */
  public function getDisabledElements() {
    $config = $this->configFactory->getEditable('cohesion.settings');
    $disabledElements = [];
    if (!empty($config->get('element_toggle'))) {
      $elements = JSON::decode($config->get('element_toggle'));

      if (is_iterable($elements)) {
        foreach ($elements as $key => $value) {
          if ($value === 0) {
            $disabledElements[] = $key;
          }
        }
      }
    }
    return $disabledElements;
  }

  /**
   * Return the number of items in the element_usage queue.
   *
   * @return mixed
   */
  public function numberOfItemsInQueue() {
    $queue = $this->queueFactory->get('element_usage');

    return $queue->numberOfItems();
  }

}
