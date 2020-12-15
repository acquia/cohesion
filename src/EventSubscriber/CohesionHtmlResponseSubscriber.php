<?php

namespace Drupal\cohesion\EventSubscriber;

use Drupal\Core\Render\HtmlResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Response subscriber to replace the HtmlResponse with a BigPipeResponse.
 *
 * @see \Drupal\big_pipe\Render\BigPipe
 *
 * @todo Refactor once https://www.drupal.org/node/2577631 lands.
 */
class CohesionHtmlResponseSubscriber implements EventSubscriberInterface {

  /**
   * Adds markers to the response necessary for the BigPipe render strategy.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   The event to process.
   */
  public function onRespondEarly(FilterResponseEvent $event) {

    $response = $event->getResponse();
    if (!$response instanceof HtmlResponse) {
      return;
    }

    // Extract and render the cohesion attachments styles in the DOM.
    $attachments = $response->getAttachments();
    if (isset($attachments['cohesion']) && !empty($attachments['cohesion'])) {

      $inline_styles = [];
      // loop over each style block and minify the CSS.
      foreach($attachments['cohesion'] as $inline_css) {
        $this->minifyStyleBlock($inline_styles, $inline_css);
      }

      // Set inline styles for dx8 and
      // remove bigpipe token key from style output.
      $content = str_replace(['<cohesion-placeholder></cohesion-placeholder>', 'big_pipe_nojs_placeholder_attribute_safe:'], [implode("\n", $inline_styles), ''], $response->getContent());
      $response->setContent($content);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['onRespondEarly', -100];

    return $events;
  }

  /**
   *  Minify inline CSS style blocks.
   *
   * @param $inline_styles
   * @param $inline_css
   *
   * @return mixed
   */
  public function minifyStyleBlock(&$inline_styles, $inline_css) {
    // make it into one long line
    $inline_css = str_replace(["\n","\r"],'', $inline_css);

    return $inline_styles[] = $inline_css;
  }

}
