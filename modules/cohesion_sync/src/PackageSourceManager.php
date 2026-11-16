<?php

namespace Drupal\cohesion_sync;

use Drupal\cohesion_sync\Exception\SourceServiceNotFoundException;
use Drupal\cohesion_sync\Services\PackageSourceServiceInterface;

/**
 * Sitestudio package source service collector and manager.
 */
class PackageSourceManager {

  /**
   * An unsorted array of arrays of active sitestudio package source services.
   *
   * An associative array. The keys are integers that indicate priority. Values
   * are arrays of PackageSourceServiceInterface objects.
   *
   * @var \Drupal\cohesion_sync\Services\PackageSourceServiceInterface[][]
   */
  protected $services = [];

  /**
   * Unsorted array of source service ids available.
   *
   * @var array
   */
  protected $serviceIds = [];

  /**
   * An array of sitestudio package source services, sorted by priority.
   *
   * If this is NULL a rebuild will be triggered.
   *
   * @var null|\Drupal\cohesion_sync\Services\PackageSourceServiceInterface[]
   */
  protected $sortedServices = NULL;

  /**
   * Adds service to the collector.
   *
   * @param \Drupal\cohesion_sync\Services\PackageSourceServiceInterface $service
   *   Package source service.
   * @param int $priority
   *   Service priority.
   *
   * @return $this
   */
  public function addSourceService(PackageSourceServiceInterface $service, string $id, int $priority = 0): self {
    $this->services[$priority][] = $service;
    $this->serviceIds[] = $service->getSupportedType();

    $this->sortedServices = NULL;

    return $this;
  }

  /**
   * Gets a package source service for specific type.
   *
   * @param string $type
   *   Source type, for example "default_module_package".
   *
   * @return \Drupal\cohesion_sync\Services\PackageSourceServiceInterface
   *   Package Source service or NULL if no matching service found.
   *
   * @throws \Drupal\cohesion_sync\Exception\SourceServiceNotFoundException
   */
  public function getSourceService(string $type): PackageSourceServiceInterface {
    if ($this->sortedServices === NULL) {
      $this->sortedServices = $this->getSortedServices();
    }
    foreach ($this->sortedServices as $sortedService) {
      if ($sortedService->supportedType($type)) {
        $service = $sortedService;
        break;
      }
    }

    if (!isset($service)) {
      throw new SourceServiceNotFoundException($type, $this->serviceIds);
    }

    return $service;
  }

  /**
   * Flattens and sorts services array.
   *
   * @return array
   *   One-dimensional services array, sorted by priority.
   */
  protected function getSortedServices(): array {
    $sorted = [];
    krsort($this->services);

    foreach ($this->services as $services) {
      $sorted = array_merge($sorted, $services);
    }
    return $sorted;
  }

}
