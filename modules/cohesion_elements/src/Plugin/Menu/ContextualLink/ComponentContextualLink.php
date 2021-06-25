<?php

namespace Drupal\cohesion_elements\Plugin\Menu\ContextualLink;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Menu\ContextualLinkDefault;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ComponentContextualLink
 * This class exists to add options to cohesion_elements.links.contextual.yml.
 *
 * @package Drupal\cohesion_elements\Plugin\Menu\ContextualLink
 */
class ComponentContextualLink extends ContextualLinkDefault {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getTitle(Request $request = NULL) {
    return $this->t('Edit component');
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    return [
      'attributes' => [
        'class' => ['use-ajax'],
        'data-dialog-type' => 'dialog',
        'data-dialog-renderer' => 'off_canvas',
        'data-settings-tray-edit' => TRUE,
        'data-dialog-options' => Json::encode([
          'width' => COHESION_ELEMENTS_COHESION_COMPONENT_SETTINGS_TRAY_WIDTH,
          'dialogClass' => 'ui-dialog-off-canvas dx8-settings-dialog',
        ]),
      ],
    ];
  }

}
