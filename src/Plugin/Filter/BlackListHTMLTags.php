<?php

namespace Drupal\cohesion\Plugin\Filter;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\filter\Attribute\Filter;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\filter\Plugin\FilterInterface;

/**
 * Provides a filter to remove all HTML tags in the body.
 *
 * @package Drupal\cohesion\Plugin\Filter
 */
#[Filter(
  id: "black_list_html_tags",
  title: new TranslatableMarkup("Black list HTML tags"),
  type: FilterInterface::TYPE_HTML_RESTRICTOR,
  description: new TranslatableMarkup("Remove all html tags in the body"),
)]
class BlackListHTMLTags extends FilterBase {

  /**
   * @param string $text
   * @param string $langcode
   *
   * @return \Drupal\filter\FilterProcessResult
   */
  public function process($text, $langcode): FilterProcessResult {
    return new FilterProcessResult($this->removeTags($text, 'a'));
  }

  /**
   * @param $html
   * @param $tag
   *
   * @return string|false
   * @see https://stackoverflow.com/a/48362353/830680
   */
  public function removeTags($html, $tag): string|false {
    $dom = new \DOMDocument();
    $dom->loadHTML($html);
    foreach (iterator_to_array($dom->getElementsByTagName($tag)) as $item) {
      $item->parentNode->removeChild($item);
    }
    return $dom->saveHTML();
  }

}
