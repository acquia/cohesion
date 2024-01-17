<?php

namespace Drupal\cohesion\Render;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Asset\AssetCollectionRendererInterface;
use Drupal\Core\Asset\AssetResolverInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\EnforcedResponseException;
use Drupal\Core\Render\AttachmentsInterface;
use Drupal\Core\Render\AttachmentsResponseProcessorInterface;
use Drupal\Core\Render\HtmlResponse;
use Drupal\Core\Render\HtmlResponseAttachmentsProcessor;
use Drupal\Core\Render\Markup;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Processes attachments of HTML responses with Cohesion enabled.
 *
 * @see \Drupal\Core\Render\HtmlResponseAttachmentsProcessor
 */
class CohesionAttachmentsProcessor extends HtmlResponseAttachmentsProcessor {

  /**
   * The HTML response attachments processor service.
   *
   * @var \Drupal\Core\Render\AttachmentsResponseProcessorInterface
   */
  protected $htmlResponseAttachmentsProcessor;

  /**
   * Constructs a CohesionAttachmentsProcessor object.
   *
   * @param \Drupal\Core\Render\AttachmentsResponseProcessorInterface $html_response_attachments_processor
   *   The HTML response attachments processor service.
   * @param \Drupal\Core\Asset\AssetResolverInterface $asset_resolver
   *   An asset resolver.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   A config factory for retrieving required config objects.
   * @param \Drupal\Core\Asset\AssetCollectionRendererInterface $css_collection_renderer
   *   The CSS asset collection renderer.
   * @param \Drupal\Core\Asset\AssetCollectionRendererInterface $js_collection_renderer
   *   The JS asset collection renderer.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   */
  public function __construct(AttachmentsResponseProcessorInterface $html_response_attachments_processor, AssetResolverInterface $asset_resolver, ConfigFactoryInterface $config_factory, AssetCollectionRendererInterface $css_collection_renderer, AssetCollectionRendererInterface $js_collection_renderer, RequestStack $request_stack, RendererInterface $renderer, ModuleHandlerInterface $module_handler) {
    $this->htmlResponseAttachmentsProcessor = $html_response_attachments_processor;
    parent::__construct($asset_resolver, $config_factory, $css_collection_renderer, $js_collection_renderer, $request_stack, $renderer, $module_handler);
  }

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
    $processed_html_response = $this->htmlResponseAttachmentsProcessor->processAttachments($html_response);
    $attachments = $processed_html_response->getAttachments();
    $cohesion_response = clone $processed_html_response;
    if (count($processed_cohesion_attachments)) {
      $attachments['cohesion'] = $processed_cohesion_attachments;
    }

    $cohesion_response->setAttachments($attachments);

    return $cohesion_response;
  }

}
