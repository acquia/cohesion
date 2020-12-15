<?php

namespace Drupal\cohesion\Plugin\ImageBrowser;

use Drupal\cohesion\ImageBrowserPluginBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\imce\Entity\ImceProfile;

/**
 * Class ImceImageBrowser.
 *
 * @package Drupal\cohesion
 *
 * @ImageBrowser(
 *   id = "imce_imagebrowser",
 *   name = @Translation("Imce File Manager"),
 *   module = "imce"
 * )
 */
class ImceImageBrowser extends ImageBrowserPluginBase {

  /**
   * {@inheritdoc}
   */
  public function buildForm(FormStateInterface &$form_state, $browser_type, $config_object) {
    // Stream wrapper group.
    $stream_wrappers = $this->streamWrapperManager->getWrappers(StreamWrapperInterface::ALL);
    $wrapper_keys = array_keys($stream_wrappers);
    $options = [];
    foreach ($wrapper_keys as $path_key) {
      if (in_array($path_key, ['temporary', 'cohesion'])) {
        continue;
      }
      $options[$path_key] = t('@path', ['@path' => $path_key . '://']);
    }
    $index = $config_object[$browser_type]['dx8_imce_stream_wrapper'] ?? '';

    $form['dx8_imce_stream_wrapper_' . $browser_type] = [
      '#type' => 'radios',
      '#title' => t('Stream wrapper'),
      '#description' => t('Select a stream wrapper (base directory) for the IMCE file manager when used within Site Studio.'),
      '#required' => TRUE,
      '#default_value' => isset($options[$index]) ? $index : 'public',
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
    $config_object[$browser_type]['dx8_imce_stream_wrapper'] = $form_state->getValue('dx8_imce_stream_wrapper_' . $browser_type);
  }

  /**
   * {@inheritdoc}
   */
  public function onInit() {
    /*
     * Adds cohesion directory to imce profile and restrict access to it in imce
     * file browser
     */
    $profiles = $this->configFactory->listAll('imce');
    foreach ($profiles as $id) {
      $profile_config = $this->configFactory->getEditable($id);
      $profile = $profile_config->getRawData();
      if (isset($profile['conf']) && isset($profile['conf']['folders'])) {
        // If the cohesion folder is not in the profile.
        $found = FALSE;
        foreach ($profile['conf']['folders'] as $folder) {
          if (isset($folder['path']) && $folder['path'] == 'cohesion') {
            // Site Studio is already in the profile.
            $found = TRUE;
          }
        }

        if ($found) {
          continue;
        }

        // Restrict access to imce file browser content by disabling all permissions.
        $profile['conf']['folders'][] = [
          'path' => 'cohesion',
          'permissions' => [],
        ];
        $profile_config->setData($profile);
        $profile_config->save();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function onEntityInsertUpdate(EntityInterface $entity) {
    // _restrict_imce_cohesion_directory().
    if ($entity instanceof ImceProfile) {
      $profile_config = $this->configFactory->getEditable($entity->getConfigDependencyName());
      // Get imce profile data.
      $values = $profile_config->getRawData();
      if (isset($values['conf']) && isset($values['conf']['folders'])) {
        $values['conf']['folders'] = $this->filter_folders($values['conf']);
      }
      $profile_config->setData($values);
      $profile_config->save();
    }
  }

  /**
   * Helper for onEntityInsertUpdate.
   *
   * @param array $folders
   *
   * @return array
   */
  private function filter_folders($folders = []) {
    $results = [];
    if (in_array('folders', array_keys($folders))) {
      // Filter directories which contains the cohesion keyword and restrict access.
      foreach ($folders['folders'] as $folder) {
        $path = $folder['path'];
        if (strpos($path, 'cohesion') !== FALSE) {
          $folder['permissions'] = [];
        }
        $results[$path] = $folder;
      }
    }
    return array_values($results);
  }

  /**
   * {@inheritdoc}
   */
  public function sharedPageAttachments($type, &$attachments) {
    if ($image_browser_object = $this->config->get('image_browser')) {
      // Add the stream wrapper array.
      $stream_wrappers = \Drupal::service('stream_wrapper_manager')->getWrappers(StreamWrapperInterface::ALL);
      $wrapper_keys = array_keys($stream_wrappers);
      $base_path = \Drupal::request()->getSchemeAndHttpHost();

      // Set default stream wrapper.
      $stream_wrapper = [
        'name' => 'public',
        'path' => file_create_url('public://'),
      ];

      foreach ($wrapper_keys as $path_key) {
        $path = $path_key . '://';
        if ($image_browser_object[$type]['dx8_imce_stream_wrapper'] == $path_key) {
          $stream_wrapper = [
            'name' => $path_key,
            'path' => file_create_url($path),
          ];
          break;
        }
      }

      $attachments['drupalSettings']['cohesion']['imageBrowser'] = [
        'streamWrapper' => $stream_wrapper,
        // Add the image browser iFrame URL.
        'url' => $base_path . '/imce/' . $stream_wrapper['name'] . '?sendto=imceFileBrowserCallback',
        'title' => $this->getName(),
      ];
    }
  }

}
