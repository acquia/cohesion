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
    $style_string = '';

    if (($storage = $this->entityTypeManager->getStorage('cohesion_custom_style')) && ($custom_styles = $storage->loadMultiple())) {
      foreach ($custom_styles as $custom_style) {

        // Load the type data.
        if ($custom_style->get('status')) {
          $name = $custom_style->label();
          $class_name = str_replace('.', '', $custom_style->getClass());

          // Block style
          if ($custom_style->get('available_in_wysiwyg')) {
            $type_id = $custom_style->getCustomStyleType();
            $custom_style_type = $this->entityTypeManager->getStorage('custom_style_type')->load($type_id);
            $elements = explode(',', $custom_style_type->getElement());
            foreach ($elements as $element) {
              $name = $custom_style->label();
              // Block style
              if (count($elements) > 1) {
                $name = "{$name} [{$element}]";
              }
              $style_string .= "{$element}.{$class_name}|{$name}\n";
            }
          }

          // Inline style
          if ($custom_style->get('available_in_wysiwyg_inline')) {
            $style_string .= "span.{$class_name}|{$name} [inline]\n";
          }
        }
      }
    }

    if ($colors = \Drupal::service('settings.endpoint.utils')->getColorsList()) {
      foreach ($colors as $color) {
        if (isset($color['wysiwyg']) && $color['wysiwyg'] === TRUE) {
          $class_name = str_replace('.', '', $color['class']);
          $style_string .= "span.{$class_name}|{$color['name']}\n";
        }
      }
    }

    return $style_string;
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
    if (!$styles) {
      $styles = [];
    }

    // If the editor is not site studiop style enabled, set the parsed_styles
    // to an empty array. This will ensure that the site studio styles get
    // removed from the list.
    if ($has_cohesion_styles) {
      $parsed_styles = $this->parseStylesFormValue($this->getStylesText())[0];
    } else {
      $parsed_styles = [];
    }

    // only override styles that have not yet been saved
    foreach ($parsed_styles as $parsed_style) {
      $is_in_styles = FALSE;
      foreach ($styles as $style) {
        if ($style == $parsed_style) {
          $is_in_styles = TRUE;
        }
      }

      if (!$is_in_styles) {
        $styles[] = $parsed_style;
      }
    }

    // Unset styles that have been deleted or disabled since the last save
    // of the config
    foreach ($styles as $key => $style) {
      if (strpos($style['element'], 'coh-') !== FALSE) {
        $is_in_parsed_styles = FALSE;
        foreach ($parsed_styles as $parsed_style) {
          if ($parsed_style == $style) {
            $is_in_parsed_styles = TRUE;
          }
        }

        if (!$is_in_parsed_styles) {
          unset($styles[$key]);
        }
      }
    }

    return array_values($styles);
  }

}
