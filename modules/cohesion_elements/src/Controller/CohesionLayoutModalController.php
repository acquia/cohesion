<?php

namespace Drupal\cohesion_elements\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

/**
 * Class CohesionLayoutModalController.
 *
 * @package Drupal\cohesion_elements\Controller
 */
class CohesionLayoutModalController extends ControllerBase {

  /**
   * Render the <iframe> that will contain the cohesion_layout sidebar editor form.
   *
   * @return array
   */
  public function iframe() {
    if (\Drupal::request()->attributes->get('entity_type_id') == 'cohesion_layout') {
      // Build the path of the cohesion_layout edit page.
      $entity_id = \Drupal::request()->attributes->get('id');
      $uuid = \Drupal::request()->attributes->get('uuid');
      $revision_id = \Drupal::request()->attributes->get('revision_id');
      $component_id = \Drupal::request()->attributes->get('component_id');

      $edit_path = Url::fromRoute('cohesion_elements.layout_canvas.edit_revision', [
        'cohesion_layout_revision' => $revision_id,
        'component_instance_uuid' => $uuid,
        'component_id' => $component_id,
      ])->toString();

      // Return a render array containing an iframe pointing to that url.
      return [
        '#type' => 'inline_template',
        '#template' => '<iframe class="dx8-contextual dx8-hidden" src="{{ edit_path }}?coh_clean_page=true" title="Component form"></iframe>',
        '#context' => [
          'edit_path' => $edit_path,
        ],
      ];
    }
  }

}
