<?php

namespace Drupal\cohesion\Plugin\ImageBrowser;

use Drupal\cohesion\ImageBrowserPluginBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\media_library\MediaLibraryState;

/**
 * Plugin for media library image browser element.
 *
 * @package Drupal\cohesion
 *
 * @ImageBrowser(
 *   id = "medialib_imagebrowser",
 *   name = @Translation("Media Library"),
 *   module = "media_library"
 * )
 */
class MediaLibraryImageBrowser extends ImageBrowserPluginBase {

  /**
   * {@inheritdoc}
   */
  public function buildForm(FormStateInterface &$form_state, $browser_type, $config_object) {

    $options = [];
    $bundles = \Drupal::service('entity_type.bundle.info')
      ->getBundleInfo('media');

    foreach ($bundles as $key => $bundle) {
      $options[$key] = $bundle['label'] . ' (' . $key . ')';
    }

    $index = $config_object[$browser_type]['cohesion_media_lib_types'] ?? [];

    $form['cohesion_media_lib_types_' . $browser_type] = [
      '#type' => 'checkboxes',
      '#title' => t('Media types selectable in the Media Library'),
      '#description' => t('If none are selected, all will be allowed.'),
      '#required' => FALSE,
      '#default_value' => $index ?? '',
      '#options' => $options,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(FormStateInterface &$form_state) {
    // $form_state->setErrorByName('somefield', t('some error.'));
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(FormStateInterface $form_state, $browser_type, &$config_object) {
    $form_values = $form_state->getValue('cohesion_media_lib_types_' . $browser_type);

    $values = [];
    foreach ($form_values as $form_value) {
      $values[] = $form_value;
    }

    $config_object[$browser_type]['cohesion_media_lib_types'] = $values;
  }

  /**
   * {@inheritdoc}
   */
  public function onInit() {
  }

  /**
   * {@inheritdoc}
   */
  public function onEntityInsertUpdate(EntityInterface $entity) {
  }

  /**
   * {@inheritdoc}
   */
  public function sharedPageAttachments($type, &$attachments) {
    if ($image_browser_object = $this->config->get('image_browser')) {

      $allowed_types = array_filter($image_browser_object[$type]['cohesion_media_lib_types']);
      $selected_type = array_shift($image_browser_object[$type]['cohesion_media_lib_types']);

      // If no media types have been set, allow all.
      if (empty($allowed_types)) {

        $media_types = \Drupal::service('entity_type.bundle.info')
          ->getBundleInfo('media');

        $allowed_types = [];
        foreach ($media_types as $key => $media_type) {
          $allowed_types[] = $key;
        }

        $allowed_types = array_filter($allowed_types);
        $selected_type = $allowed_types[0];
      }

      $media_lib_state = MediaLibraryState::create('media_library.opener.cohesion', $allowed_types, $selected_type, 1);

      $url = Url::fromRoute('media_library.ui', [
        'media_library_opener_id' => 'media_library.opener.cohesion',
        'media_library_allowed_types' => $allowed_types,
        'media_library_selected_type' => $media_lib_state->getSelectedTypeId(),
        'media_library_remaining' => 1,
        'hash' => $media_lib_state->getHash(),
      ])->toString();

      $attachments['drupalSettings']['cohesion']['imageBrowser'] = [
        // Add the image browser URL, title & key for front-end app.
        'url' => $url,
        'title' => $this->getName(),
        'key' => 'media-library',
      ];
    }
  }

}
