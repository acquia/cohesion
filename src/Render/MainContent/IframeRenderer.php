<?php

namespace Drupal\cohesion\Render\MainContent;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\cohesion\Ajax\OpenIframeCommand;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Render\RendererInterface;
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
   * The title resolver.
   *
   * @var \Drupal\Core\Controller\TitleResolverInterface
   */
  protected $titleResolver;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * @param \Drupal\Core\Controller\TitleResolverInterface $title_resolver
   * @param \Drupal\Core\Render\RendererInterface $renderer
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   */
  public function __construct(
    TitleResolverInterface $title_resolver,
    RendererInterface $renderer,
    ModuleHandlerInterface $module_handler
  ) {
    parent::__construct($title_resolver, $renderer);
    $this->moduleHandler = $module_handler;
  }

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

    $libraries = [
      'cohesion/coh-media-iframe',
      'core/drupal.dialog.ajax',
      'core/jquery.ui.resizable',
      'core/jquery.ui.draggable',
    ];

    if ($this->moduleHandler->moduleExists('acquia_dam')) {
      $libraries[] = 'acquia_dam/media_library.style';
    }

    $response->setAttachments([
      'library' => $libraries,
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
