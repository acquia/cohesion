<?php

namespace Drupal\sitestudio_page_builder\Ajax;

use Drupal\Core\Ajax\CommandInterface;
use Drupal\Core\Ajax\CommandWithAttachedAssetsInterface;
use Drupal\Core\Ajax\CommandWithAttachedAssetsTrait;

/**
 * Provides an AJAX command for site studio page builder
 */
class SitestudioPageBuilderCommand implements CommandInterface, CommandWithAttachedAssetsInterface {

  use CommandWithAttachedAssetsTrait;

  /**
   * The content for the matched element(s).
   *
   * Either a render array or an HTML string.
   *
   * @var string|array
   */
  protected $content;

  /**
   * Constructs an SitestudioPageBuilderCommand object.
   *
   * @param string|array $content
   *   The content that will be inserted page builder aread of the page.
   */
  public function __construct($content) {
    $this->content = $content;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    return [
      'command' => 'sitestudio_page_builder',
      'data' => $this->getRenderedContent(),
    ];
  }

}
