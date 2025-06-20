<?php

namespace Drupal\cohesion\Render;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Form\EnforcedResponseException;
use Drupal\Core\Render\AttachmentsInterface;
use Drupal\Core\Render\HtmlResponse;
use Drupal\Core\Render\HtmlResponseAttachmentsProcessor;
use Drupal\Core\Render\Markup;

/**
 * Processes attachments of HTML responses with Cohesion enabled.
 *
 * @see \Drupal\Core\Render\HtmlResponseAttachmentsProcessor
 */
class CohesionAttachmentsProcessor extends HtmlResponseAttachmentsProcessor {

  /**
   * {@inheritdoc}
   */
  public function processAttachments(AttachmentsInterface $response) {
    assert($response instanceof HtmlResponse);

    try {
      $response = $this->renderPlaceholders($response);
    }
    catch (EnforcedResponseException $e) {
      return $e->getResponse();
    }

    $attachments = $response->getAttachments();
    $processed_cohesion_attachments = [];
    if (isset($attachments['cohesion'])) {
      $cohesion_attachments = $attachments['cohesion'];
      unset($attachments['cohesion']);
      foreach ($cohesion_attachments as $cohesion_attachment) {
        // Check that we don't process the same inline style more than once.
        if (!in_array($cohesion_attachment, $processed_cohesion_attachments)) {
          if (is_string($cohesion_attachment) || $cohesion_attachment instanceof MarkupInterface) {
            $processed_styles = Markup::create($cohesion_attachment);
          } else {
            throw new \LogicException(sprintf('Site Studio attachment must be of string or markup, %s given', gettype($cohesion_attachment)));
          }
          if ($processed_styles != '<style></style>') {
            $processed_cohesion_attachments[] = $processed_styles;
          }
        }
      }
    }

    $html_response = clone $response;
    $html_response->setAttachments($attachments);

    // Call HtmlResponseAttachmentsProcessor to process all other attachments.
    $processed_html_response = parent::processAttachments($html_response);
    $attachments = $processed_html_response->getAttachments();
    $cohesion_response = clone $processed_html_response;
    if (count($processed_cohesion_attachments)) {
      $attachments['cohesion'] = $processed_cohesion_attachments;
    }

    $cohesion_response->setAttachments($attachments);

    return $cohesion_response;
  }

}
