<?php

namespace Drupal\cohesion\Plugin\ImageBrowser;

use Drupal\cohesion\ImageBrowserPluginBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Class MediaImageBrowser.
 *
 * @package Drupal\cohesion
 *
 * @ImageBrowser(
 *   id = "entity_imagebrowser",
 *   name = @Translation("Entity Browser"),
 *   module = "entity_browser"
 * )
 */
class EntityImageBrowser extends ImageBrowserPluginBase {

  /**
   * {@inheritdoc}
   */
  public function buildForm(FormStateInterface &$form_state, $browser_type, $config_object) {
    // Load all entity browser entities.
    try {
      $browsers = \Drupal::entityTypeManager()->getStorage('entity_browser')->loadMultiple();
    }
    catch (\Exception $e) {
      return [];
    }

    $options = [];

    foreach ($browsers as $key => $browser) {
      $options[$key] = $browser->label();
    }

    // Set the media entity brower to be default (if it's available).
    $default_entity_browser = isset($options['media_browser']) ? 'media_browser' : FALSE;

    // Build the form partial.
    $index = $config_object[$browser_type]['dx8_entity_browser'] ?? $default_entity_browser;
    $form['dx8_entity_browser_' . $browser_type] = [
      '#type' => 'select',
      '#title' => t('Entity browser'),
      '#description' => t('Select an entity browser to use.'),
      '#required' => TRUE,
      '#default_value' => isset($options[$index]) ? $index : $default_entity_browser,
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
    $config_object[$browser_type]['dx8_entity_browser'] = $form_state->getValue('dx8_entity_browser_' . $browser_type);
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
      // Load the entity browser entity.
      if (isset($image_browser_object[$type]['dx8_entity_browser']) && $entity_browser_id = $image_browser_object[$type]['dx8_entity_browser']) {
        /** @var \Drupal\entity_browser\EntityBrowserInterface $entity_browser */
        if ($entity_browser = \Drupal::entityTypeManager()->getStorage('entity_browser')->load($entity_browser_id)) {
          // Only Displays inherited form iframe will have the path() function.
          if (method_exists($entity_browser->getDisplay(), 'path')) {
            // Generate the Url through Drupal.
            $url = Url::fromUserInput($entity_browser->getDisplay()->path(), [
              'query' => [
                'uuid' => 'dx8',
              ],
            ])->toString();

            // Tell Angular which iframe URL to popup.
            $attachments['drupalSettings']['cohesion']['imageBrowser'] = [
              'url' => $url,
              'title' => $this->getName(),
              'key' => 'entity-browser',
            ];

            // Tell the entity browser the count and cardinality (how many
            // entities can be selected at once).
            $attachments['drupalSettings']['entity_browser']['dx8'] = [
              'count' => 1,
              'cardinality' => 1,
            ];
          }
        }
      }
    }
  }

}
