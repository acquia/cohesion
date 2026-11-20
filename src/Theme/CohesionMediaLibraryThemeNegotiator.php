<?php

namespace Drupal\cohesion\Theme;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Forces the admin theme for the media library UI opened by Site Studio.
 *
 * The media library UI route is not an admin route, so it inherits whatever
 * theme is active on the page that opened it. When Page Builder opens the
 * dialog over AJAX from the front end, core's AjaxBasePageNegotiator (which
 * pins AJAX responses to the calling page's theme) outranks every other
 * negotiator, so this must run at a higher priority to win.
 */
class CohesionMediaLibraryThemeNegotiator implements ThemeNegotiatorInterface {

  /**
   * The cohesion media library opener ID.
   */
  const OPENER_ID = 'media_library.opener.cohesion';

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a CohesionMediaLibraryThemeNegotiator object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(ConfigFactoryInterface $config_factory, RequestStack $request_stack) {
    $this->configFactory = $config_factory;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    if ($route_match->getRouteName() !== 'media_library.ui') {
      return FALSE;
    }

    $opener_id = $route_match->getParameter('media_library_opener_id');
    if ($opener_id === NULL) {
      $request = $this->requestStack->getCurrentRequest();
      $opener_id = $request ? $request->query->get('media_library_opener_id') : NULL;
    }

    return $opener_id === static::OPENER_ID;
  }

  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(RouteMatchInterface $route_match) {
    return $this->configFactory->get('system.theme')->get('admin') ?: NULL;
  }

}
