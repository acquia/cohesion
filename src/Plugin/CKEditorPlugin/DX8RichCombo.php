<?php

namespace Drupal\cohesion\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "dx8_richcombo" plugin.
 *
 * @CKEditorPlugin(
 *   id = "dx8_richcombo",
 *   label = @Translation("DX8 Rich Combo")
 * )
 */
class DX8RichCombo extends CKEditorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getDependencies(Editor $editor) {
    return ['floatpanel', 'listblock', 'button'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return drupal_get_path('module', 'cohesion') . '/js/ckeditor/dx8_richcombo.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return [];
  }

}
