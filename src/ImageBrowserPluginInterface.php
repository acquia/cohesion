<?php

namespace Drupal\cohesion;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 */
interface ImageBrowserPluginInterface extends PluginInspectionInterface {

  /**
   * Return the name of the reusable form plugin.
   *
   * @return string
   */
  public function getName();

  /**
   * Return the machine name of the module this plugin requires.
   *
   * @return string
   */
  public function getModule();

  /**
   * Get the config form for this image browser.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param $browser_type
   * @param $config_object
   *
   * @return mixed
   */
  public function buildForm(FormStateInterface &$form_state, $browser_type, $config_object);

  /**
   * Validation handler for the config form.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return mixed
   */
  public function validateForm(FormStateInterface &$form_state);

  /**
   * The submit handler for the config form.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param $browser_type
   * @param $image_browser_object
   *
   * @return mixed
   */
  public function submitForm(FormStateInterface $form_state, $browser_type, &$image_browser_object);

  /**
   * This image browser has been initialized for this site.
   *
   * @return mixed
   */
  public function onInit();

  /**
   * Run this whenever an entity is inserted or updated.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return mixed
   */
  public function onEntityInsertUpdate(EntityInterface $entity);

  /**
   * Modify attachments (usually drupalSettings.cohesion) for pages that use
   * the Angular forms.
   *
   * @param $type
   * @param $attachments
   *
   * @return mixed
   */
  public function sharedPageAttachments($type, &$attachments);

}
