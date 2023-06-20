<?php

namespace Drupal\cohesion\Render\MainContent;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\cohesion\Ajax\OpenIframeCommand;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Render\MainContent\DialogRenderer;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;

/**
 * Renders the requested page inside an iframe.
 *
 * The renderer returns the markup of an iframe, with the currently requested
 * url set as the iframe src. A destination query parameter is appended to the
 * url. This destination route will indicate to the iframe's parent window that
 * the dialog containing the iframe can be closed.
 */
class IframeRenderer extends DialogRenderer {

  /**
   * {@inheritdoc}
   */
  public function renderResponse(array $main_content, Request $request, RouteMatchInterface $route_match): AjaxResponse {
    $response = new AjaxResponse();

    $params = array_merge($request->query->all(), ['coh_clean_page' => 'true']);
    unset($params['_wrapper_format']);
    $iframe_src = Url::fromRoute('media_library.ui', $params);

    $iframe_attributes = new Attribute([
      'src' => $iframe_src->toString(),
      'class' => ['ssa-dialog-iframe'],
      'name' => 'ssa-dialog-iframe',
      'id' => 'ssaIframe',
    ]);

    $render_array = [
      '#theme' => 'sitestudio_iframe',
      '#iframe_attributes' => $iframe_attributes,
    ];

    $content = $this->renderer->renderRoot($render_array);
    $response->setAttachments([
      'library' => [
        'cohesion/coh-media-iframe',
        'core/drupal.dialog.ajax',
        'core/jquery.ui.resizable',
        'core/jquery.ui.draggable',
      ],
    ]);

    $title = $main_content['#title'] ?? $this->titleResolver->getTitle($request, $route_match->getRouteObject());

    if (is_array($title)) {
      $title = $this->renderer->renderPlain($title);
    }

    $options = $request->request->all('dialogOptions');
    $response->addCommand(new OpenIframeCommand($title, $content, $options));

    return $response;
  }

}
