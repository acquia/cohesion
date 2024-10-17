<?php

namespace Drupal\sitestudio_legacy_ckeditor\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ExtensionPathResolver;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\editor\Entity\Editor;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the "dx8_stylescombo" plugin.
 *
 * @CKEditorPlugin(
 *   id = "dx8_stylescombo",
 *   label = @Translation("Element styles")
 * )
 */
class DX8StylesCombo extends CKEditorPluginBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\Core\Extension\ExtensionPathResolver $extensionPathResolver
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    ExtensionPathResolver $extensionPathResolver,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
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
      $container->get('entity_type.manager'),
      $container->get('extension.path.resolver')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies(Editor $editor) {
    return ['dx8_richcombo'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return $this->extensionPathResolver->getPath('module', 'sitestudio_legacy_ckeditor') . '/js/ckeditor/dx8_stylescombo.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    return [
      'DX8Styles' => [
        'label' => $this->t('Element styles'),
        'image_alternative' => [
          '#type' => 'inline_template',
          '#template' => '<a href="#" role="button" aria-label="{{ styles_text }}"><span class="ckeditor-button-dropdown">{{ styles_text }}<span class="ckeditor-button-arrow"></span></span></a>',
          '#context' => [
            'styles_text' => $this->t('Element styles'),
          ],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    // Load custom style set (appened with DX8 to avoid overwriting core
    // stylesSet config).
    $config['stylesSetDX8'] = $this->getStyleSet();

    return $config;
  }

  /**
   * Get a list of DX8 custom styles in a format that CKEditor can use as
   * a style set.
   *
   * @return array
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  protected function getStyleSet() {
    $list = [];

    if (($storage = $this->entityTypeManager->getStorage('cohesion_custom_style')) && ($custom_styles = $storage->loadMultiple())) {
      foreach ($custom_styles as $custom_style) {

        // Load the type data.
        $type_id = $custom_style->getCustomStyleType();
        if ($custom_style->get('status') && $custom_style_type = \Drupal::service('entity_type.manager')->getStorage('custom_style_type')->load($type_id)) {
          if ($custom_style->get('available_in_wysiwyg')) {

            if ($custom_style_type->getElement() != '') {
              $elements = explode(',', $custom_style_type->getElement());
              foreach ($elements as $element) {
                $group = $custom_style_type->get('label');
                $name = $custom_style->label();
                if (count($elements) > 1) {
                  $group = "{$group} [{$element}]";
                  $name = "{$name} [{$element}]";
                }

                $list[$group][] = [
                  'name' => $name,
                  'element' => $element,
                  'attributes' => ['class' => str_replace('.', '', $custom_style->getClass())],
                  'displayGroup' => $group,
                ];
              }
            }
            elseif ($type_id == 'generic') {
              $list['generic'][] = [
                'name' => $custom_style->label(),
                'element' => '',
                'attributes' => ['class' => str_replace('.', '', $custom_style->getClass())],
                'displayGroup' => $custom_style_type->get('label'),
              ];
            }
          }
        }
      }
    }

    // Reorder style list so generic styles come last.
    $generic = $list['generic'] ?? [];
    // Remove generic styles from list.
    unset($list['generic']);
    $results = [];
    foreach ($list as $value) {
      $results = array_merge($results, $value);
    }
    return array_merge($results, $generic);
  }

}
