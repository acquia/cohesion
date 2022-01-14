<?php

namespace Drupal\cohesion_style_guide\Plugin\Usage;

use Drupal\cohesion\UsagePluginBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * Style guide usage plugin.
 *
 * @package Drupal\cohesion_style_guide\Plugin\Usage
 *
 * @Usage(
 *   id = "style_guide_usage",
 *   name = @Translation("Style guide usage"),
 *   entity_type = "cohesion_style_guide",
 *   scannable = TRUE,
 *   scan_same_type = FALSE,
 *   group_key = "style_guide_type",
 *   group_key_entity_type = "style_guide_type",
 *   exclude_from_package_requirements = FALSE,
 *   exportable = TRUE,
 *   config_type = "site_studio",
 *   scan_groups = {"site_studio"},
 *   can_be_excluded = TRUE
 * )
 */
class StyleGuideUsage extends UsagePluginBase {

  /**
   * @var stringRegexforfindingstyleguidetokens
   */
  protected $styleGuideRegex = '/\[style-guide:(.*?):(.*?)\]/m';

  /**
   * {@inheritdoc}
   */
  public function getScannableData(EntityInterface $entity) {
    /** @var \Drupal\cohesion_style_guide\Entity\StyleGuide $entity */
    $scanable_data = [];

    // Always add the JSON model and form blobs.
    $scanable_data[] = [
      'type' => 'json_string',
      'value' => $entity->getJsonValues(),
      'decoded' => $entity->getDecodedJsonValues(),
    ];

    // Get child styles.
    return $scanable_data;
  }

  /**
   * {@inheritdoc}
   */
  public function scanForInstancesOfThisType($data, EntityInterface $entity) {
    $entities = parent::scanForInstancesOfThisType($data, $entity);
    $style_guide_ids = [];

    // Get all the custom styles used used.
    foreach ($data as $entry) {
      // Search cohesion_layout canvases and potentialy WYSIWYG content.
      if (in_array($entry['type'], ['json_string', 'string'])) {
        // Get all the style guide used by style-guide token.
        preg_match_all($this->styleGuideRegex, $entry['value'], $matches, PREG_SET_ORDER, 0);

        foreach ($matches as $match) {
          // Found a matching file.
          $style_guide_ids[] = $match[1];
        }
      }

      if ($entry['type'] == 'coh_style_guide') {
        $entities[] = [
          'type' => 'cohesion_style_guide',
          'uuid' => $entry['style_guide_uuid'],
          'subid' => NULL,
        ];
      }

    }

    foreach ($this->storage->loadMultiple($style_guide_ids) as $entity) {
      $entities[] = [
        'type' => $this->getEntityType(),
        'uuid' => $entity->uuid(),
        'subid' => NULL,
      ];
    }

    return $entities;
  }

}
