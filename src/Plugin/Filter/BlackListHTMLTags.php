<?php

namespace Drupal\cohesion\Plugin\Filter;

use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * @Filter(
 *   id = "black_list_html_tags",
 *   title = "Black list HTML tags",
 *   description = "Remove all html tags in the body",
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_HTML_RESTRICTOR
 * )
 *
 * @package Drupal\cohesion\Plugin\Filter
 */
class BlackListHTMLTags extends FilterBase {

  /**
   * @param string $text
   * @param string $langcode
   *
   * @return \Drupal\filter\FilterProcessResult
   */
  public function process($text, $langcode) {
    return new FilterProcessResult($this->removeTags($text, 'a'));
  }

  /**
   * @param $html
   * @param $tag
   *
   * @return mixed
   * @see https://stackoverflow.com/a/48362353/830680
   */
  public function removeTags($html, $tag) {
    $dom = new \DOMDocument();
    $dom->loadHTML($html);
    foreach (iterator_to_array($dom->getElementsByTagName($tag)) as $item) {
      $item->parentNode->removeChild($item);
    }
    return $dom->saveHTML();
  }

}
