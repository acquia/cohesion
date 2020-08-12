<?php

namespace Drupal\cohesion\EventSubscriber;

use Drupal\Core\Render\HtmlResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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

    // Wrap the scripts_bottom placeholder with a marker before and after,
    // because \Drupal\big_pipe\Render\BigPipe needs to be able to extract that
    // markup if there are no-JS BigPipe placeholders.
    // @see \Drupal\big_pipe\Render\BigPipe::sendPreBody()
    $attachments = $response->getAttachments();
    if (isset($attachments['html_response_attachment_placeholders']['cohesion']) && isset($attachments['placeholders']) && !empty($attachments['placeholders'])) {
      $css_on_page = [];

      foreach ($attachments['placeholders'] as $key => $placeholder) {
        if (substr($key, 0, 20) === 'cohesion_inline_css_') {
          $css_on_page[] = \Drupal::service('renderer')->renderRoot($placeholder);
        }
      }

      $scripts_bottom_placeholder = $attachments['html_response_attachment_placeholders']['cohesion'];
      // Set inline styles for dx8 and
      // remove bigpipe token key from style output.
      $content = str_replace([$scripts_bottom_placeholder, 'big_pipe_nojs_placeholder_attribute_safe:'], [implode("\n", $css_on_page), ''], $response->getContent());
      $response->setContent($content);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Run after HtmlResponsePlaceholderStrategySubscriber (priority 5), i.e.
    // after BigPipeStrategy has been applied, but before normal (priority 0)
    // response subscribers have been applied, because by then it'll be too late
    // to transform it into a BigPipeResponse.
    $events[KernelEvents::RESPONSE][] = ['onRespondEarly', 3];

    return $events;
  }

}
