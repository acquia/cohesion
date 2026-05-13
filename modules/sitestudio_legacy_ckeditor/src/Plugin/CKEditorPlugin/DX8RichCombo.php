<?php

namespace Drupal\sitestudio_legacy_ckeditor\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\Core\Extension\ExtensionPathResolver;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\editor\Entity\Editor;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the "dx8_richcombo" plugin.
 *
 * @CKEditorPlugin(
 *   id = "dx8_richcombo",
 *   label = @Translation("DX8 Rich Combo")
 * )
 */
class DX8RichCombo extends CKEditorPluginBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Extension\ExtensionPathResolver
   */
  protected $extensionPathResolver;

  /**
   * Constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\Core\Extension\ExtensionPathResolver $extensionPathResolver
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ExtensionPathResolver $extensionPathResolver,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->extensionPathResolver = $extensionPathResolver;
  }

  /**
   * Static create method.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   *
   * @return static
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition,
  ) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('extension.path.resolver')
    );
  }

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
    return $this->extensionPathResolver->getPath('module', 'sitestudio_legacy_ckeditor') . '/js/ckeditor/dx8_richcombo.js';
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
