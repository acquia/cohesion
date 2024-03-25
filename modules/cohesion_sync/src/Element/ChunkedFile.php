<?php

namespace Drupal\cohesion_sync\Element;

use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Element\ManagedFile;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides an form element for uploading managed files in chunks to bypass
 * `upload_max_filesize`.
 *
 * @FormElement("chunked_file")
 */
class ChunkedFile extends ManagedFile {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#process' => [
        [$class, 'processManagedFile'],
      ],
      '#pre_render' => [
        [$class, 'preRenderManagedFile'],
      ],
      '#theme' => 'file_managed_file',
      '#theme_wrappers' => ['form_element'],
      '#progress_indicator' => 'throbber',
      '#progress_message' => NULL,
      '#upload_validators' => [],
      '#upload_location' => NULL,
      '#size' => 22,
      '#multiple' => FALSE,
      '#extended' => FALSE,
      '#attached' => [
        'library' => ['cohesion_sync/sync-chunked-file'],
      ],
      '#accept' => NULL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    // Find the current value of this field.
    $fids = !empty($input['fids']) ? explode(' ', $input['fids']) : [];
    foreach ($fids as $key => $fid) {
      $fids[$key] = (int) $fid;
    }

    // Process any input and save new uploads.
    if ($input !== FALSE) {
      if ($input = $form_state->getUserInput()) {
        if (isset($input['files'])) {
          $fids = array_merge($fids, $input['files']);
        }
      }
    }

    $return['fids'] = $fids;
    return $return;
  }

  /**
   * {@inheritdoc].
   */
  public static function uploadAjaxCallback(&$form, FormStateInterface &$form_state, Request $request) {
    $response = parent::uploadAjaxCallback($form, $form_state, $request);

    // Only enable the upload button if there were no validation errors.
    if ($form_state->getTriggeringElement()['#name'] == 'package_yaml_upload_button' && !$form_state->hasAnyErrors()) {
      $response->addCommand(new InvokeCommand('[data-drupal-selector="edit-submit"]', 'removeAttr', ['disabled']));
      $response->addCommand(new InvokeCommand('[data-drupal-selector="edit-submit"]', 'removeClass', ['is-disabled']));
    }

    // Enable the upload button if resetting the form.
    if ($form_state->getTriggeringElement()['#name'] == 'package_yaml_remove_button') {
      $response->addCommand(new InvokeCommand('[data-drupal-selector="edit-submit"]', 'attr',
        [
          'disabled',
          'disabled',
        ]
      ));
      $response->addCommand(new InvokeCommand('[data-drupal-selector="edit-submit"]', 'addClass', ['is-disabled']));
    }

    return $response;
  }

}
