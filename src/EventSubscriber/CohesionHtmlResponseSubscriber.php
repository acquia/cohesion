<?php

namespace Drupal\cohesion\EventSubscriber;

use Drupal\Core\Render\HtmlResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Response subscriber that injects Site Studio's inline styles into the page.
 *
 * Site Studio renders its CSS as a single inline <style> block attached to
 * the response (see TwigExtension::renderInlineStyle()) rather than through
 * Drupal's normal placeholder/render pipeline. This subscriber swaps the
 * <cohesion-placeholder> marker left in the page markup for that compiled
 * CSS once the response body is otherwise final.
 *
 * @todo Refactor once https://www.drupal.org/node/2577631 lands.
 */
class CohesionHtmlResponseSubscriber implements EventSubscriberInterface {

  /**
   * Replaces the cohesion-placeholder marker with Site Studio's inline CSS.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The event to process.
   */
  public function onRespond(ResponseEvent $event): void {

    $response = $event->getResponse();
    if (!$response instanceof HtmlResponse) {
      return;
    }

    // Extract and render the cohesion attachments styles in the DOM.
    $attachments = $response->getAttachments();
    if (!empty($attachments['cohesion'])) {

      $inline_styles = [];
      // loop over each style block and minify the CSS.
      foreach($attachments['cohesion'] as $inline_css) {
        $this->minifyStyleBlock($inline_styles, $inline_css);
      }

      // Insert Site Studio's inline styles as-is. Any BigPipe no-JS
      // attribute-safe placeholder marker
      // ("big_pipe_nojs_placeholder_attribute_safe:<placeholder>") they
      // contain must be left completely intact: this subscriber runs at
      // priority -1, before \Drupal\big_pipe\EventSubscriber\
      // HtmlResponseBigPipeSubscriber::onRespond() runs at priority -10000
      // and converts this to a BigPipeResponse. The BigPipe renderer later
      // relies on each full marker to locate and replace no-JS placeholders
      // while streaming the response.
      // Stripping the prefix here - even only from Site Studio's own
      // output - would remove the later pass's only way to find and
      // resolve the marker, leaving the unresolved placeholder text in
      // the response sent to the browser.
      $site_studio_styles = implode("\n", $inline_styles);
      $content = str_replace(
        '<cohesion-placeholder></cohesion-placeholder>',
        $site_studio_styles,
        $response->getContent()
      );
      $response->setContent($content);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    // Run after HtmlResponseSubscriber (priority 0), but before BigPipe's
    // own HtmlResponseBigPipeSubscriber (priority -10000) resolves no-JS
    // placeholders in the response content.
    $events[KernelEvents::RESPONSE][] = ['onRespond', -1];

    return $events;
  }

  /**
   * Minifies an inline CSS style block onto a single line.
   *
   * @param array $inline_styles
   *   The array of minified style blocks to append to.
   * @param string $inline_css
   *   The raw inline CSS style block to minify.
   */
  public function minifyStyleBlock(array &$inline_styles, string $inline_css): void {
    // make it into one long line
    $inline_css = str_replace(["\n", "\r"], '', $inline_css);

    $inline_styles[] = $inline_css;
  }

}
