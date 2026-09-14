<?php

namespace Drupal\cohesion\Services;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\ckeditor5\HTMLRestrictions;

/**
 * Service to retrieve styles available in text formats.
 *
 * @package Drupal\cohesion_website_settings
 */
class TextFormatStyles {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * RebuildInuseBatch constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Gets the list of available styles for text format as a string.
   *
   * @return string
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getStylesText() {
    $styles = array_merge(
      $this->buildCustomStyles(),
      $this->buildColorStyles()
    );
    return implode("\n", $styles) . "\n";
  }

  /**
   * Builds custom style entries from active custom styles.
   *
   * @return array
   *   Array of style strings.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function buildCustomStyles() {
    $storage = $this->entityTypeManager->getStorage('cohesion_custom_style');
    if (!$storage) {
      return [];
    }

    $custom_styles = array_filter(
      $storage->loadMultiple(),
      fn($style) => $style->get('status')
    );

    if (empty($custom_styles)) {
      return [];
    }

    $style_type_storage = $this->entityTypeManager->getStorage('custom_style_type');
    $styles = [];

    foreach ($custom_styles as $custom_style) {
      $name = $custom_style->label();
      $class_name = str_replace('.', '', $custom_style->getClass());

      // Block styles.
      if ($custom_style->get('available_in_wysiwyg')) {
        $type_id = $custom_style->getCustomStyleType();
        if ($custom_style_type = $style_type_storage->load($type_id)) {
          $elements = array_filter(explode(',', $custom_style_type->getElement()));
          $is_multiple = count($elements) > 1;
          foreach ($elements as $element) {
            $label = $is_multiple ? "{$name} [{$element}]" : $name;
            $styles[] = "{$element}.{$class_name}|{$label}";
          }
        }
      }

      // Inline styles.
      if ($custom_style->get('available_in_wysiwyg_inline')) {
        $styles[] = "span.{$class_name}|{$name} [inline]";
      }
    }

    return $styles;
  }

  /**
   * Builds color style entries from wysiwyg-enabled colors.
   *
   * @return array
   *   Array of style strings.
   */
  private function buildColorStyles() {
    $colors = \Drupal::service('settings.endpoint.utils')->getColorsList();
    if (empty($colors)) {
      return [];
    }

    return array_map(
      fn($color) => 'span.' . str_replace('.', '', $color['class']) . '|' . $color['name'],
      array_filter($colors, fn($color) => !empty($color['wysiwyg']))
    );
  }

  /**
   * Parses the line-based (for form) style configuration.
   *
   * @param string $form_value
   *   A string containing >=1 lines with on each line a CSS selector targeting
   *   1 tag with >=1 classes, a pipe symbol and a label. An example of a single
   *   line: p.foo.bar|Foo bar paragraph.
   *
   * @return array
   *   The parsed equivalent: a list of arrays with each containing:
   *   - label: the label after the pipe symbol, with whitespace trimmed
   *   - element: the CKEditor 5 element equivalent of the tag + classes
   */
  public function parseStylesFormValue(string $form_value): array {
    $invalid_lines = [];

    $lines = explode("\n", $form_value);
    $styles = [];
    foreach ($lines as $index => $line) {
      if (empty(trim($line))) {
        continue;
      }

      // Parse the line.
      [$selector, $label] = array_map('trim', explode('|', $line));

      // Validate the selector.
      $selector_matches = [];
      // @see https://www.w3.org/TR/CSS2/syndata.html#:~:text=In%20CSS%2C%20identifiers%20(including%20element,hyphen%20followed%20by%20a%20digit
      if (!preg_match('/^([a-z][0-9a-zA-Z\-]*)((\.[a-zA-Z0-9\x{00A0}-\x{FFFF}\-_]+)+)$/u', $selector, $selector_matches)) {
        $invalid_lines[$index + 1] = $line;
        continue;
      }

      // Parse selector into tag + classes and normalize.
      $tag = $selector_matches[1];
      $classes = array_filter(explode('.', $selector_matches[2]));
      $normalized = HTMLRestrictions::fromString(sprintf('<%s class="%s">', $tag, implode(' ', $classes)));

      $styles[] = [
        'label' => $label,
        'element' => $normalized->toCKEditor5ElementsArray()[0],
      ];
    }
    return [$styles, $invalid_lines];
  }

  public function getStyleList(array $styles, $has_cohesion_styles = TRUE) {
    $styles = $styles ?: [];
    $parsed_styles = $has_cohesion_styles
      ? $this->parseStylesFormValue($this->getStylesText())[0]
      : [];

    $parsed_keys = array_flip(array_map('serialize', $parsed_styles));
    $styles_keys = array_flip(array_map('serialize', $styles));

    foreach ($parsed_styles as $parsed_style) {
      $key = serialize($parsed_style);
      if (!isset($styles_keys[$key])) {
        $styles[] = $parsed_style;
        $styles_keys[$key] = TRUE;
      }
    }

    return array_values(array_filter(
      $styles,
      fn($style) => strpos($style['element'], 'coh-') === FALSE
        || isset($parsed_keys[serialize($style)])
    ));
  }

}
