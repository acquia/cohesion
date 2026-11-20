<?php

namespace Drupal\cohesion\Block;

use Drupal\block\BlockRepositoryInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Theme\ThemeManagerInterface;

/**
 * Prevents cache metadata bloat from blocks in Site Studio hidden region.
 *
 * Blocks in dx8_hidden are only rendered via drupal_block() in templates,
 * but BlockRepository merges their cache metadata into every page.
 *
 * @see https://docs.acquia.com/drupal-starter-kits/add-ons/site-studio/managing-blocks-regions
 */
class CohesionBlockRepository implements BlockRepositoryInterface {

  /**
   * Site Studio hidden region (cohesion_theme).
   */
  const HIDDEN_REGION = 'dx8_hidden';

  /**
   * Constructs CohesionBlockRepository.
   *
   * @param \Drupal\block\BlockRepositoryInterface $inner
   *   The decorated block repository.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $themeManager
   *   The theme manager.
   */
  public function __construct(
    protected BlockRepositoryInterface $inner,
    protected ThemeManagerInterface $themeManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function getVisibleBlocksPerRegion(array &$cacheable_metadata = []): array {
    $assignments = $this->inner->getVisibleBlocksPerRegion($cacheable_metadata);

    // Clear only when theme has dx8_hidden (Site Studio themes).
    $regions = $this->themeManager->getActiveTheme()->getRegions();
    if (in_array(self::HIDDEN_REGION, $regions, TRUE) && isset($cacheable_metadata[self::HIDDEN_REGION])) {
      $cacheable_metadata[self::HIDDEN_REGION] = new CacheableMetadata();
    }

    return $assignments;
  }

  /**
   * {@inheritdoc}
   */
  public function getUniqueMachineName(string $suggestion, ?string $theme = NULL): string {
    return $this->inner->getUniqueMachineName($suggestion, $theme);
  }

}
