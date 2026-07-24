<?php

namespace Drupal\cohesion_templates\Hook;

use Drupal\Component\Utility\Html;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Pager\PagerManagerInterface;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;

/**
 *  Cohesion templates pager hooks.
 */
class CohesionTemplatesPagerHooks {

  public function __construct(
    protected PagerManagerInterface $pager_manager,
  ) {}

  /**
   * Implements hook_preprocess_pager().
   *
   * Preprocess pager to add variables needed for Site Studio views pagination.
   * Mirrors core's PagerPreprocess::preprocessPager() logic.
   */
  #[Hook('preprocess_pager')]
  public function preprocess_pager(&$variables): void {
    $element = $variables['pager']['#element'] ?? 0;
    /** @var \Drupal\Core\Pager\PagerManagerInterface $pager_manager */
    $pager_manager = \Drupal::service('pager.manager');
    $pager = $pager_manager->getPager($element);

    if (!isset($pager)) {
      return;
    }

    $total = $pager->getTotalPages();

    // Nothing to do if there is only one page.
    if ($total <= 1) {
      return;
    }

    $parameters = $variables['pager']['#parameters'] ?? [];
    $route_parameters = $variables['pager']['#route_parameters'] ?? [];
    $tags = $variables['pager']['#tags'] ?? [];
    $route_name = $variables['pager']['#route_name'] ?? '';
    $quantity = empty($variables['pager']['#quantity']) ? 0 : $variables['pager']['#quantity'];
    $current = $pager->getCurrentPage();
    // 1-indexed current page for display purposes.
    $pager_current = $current + 1;

    // Calculate visible page range centered around the current page.
    $pager_middle = ceil($quantity / 2);
    $pager_first = $pager_current - $pager_middle + 1;
    $pager_last = $pager_current + $quantity - $pager_middle;

    // Adjust window when near the boundaries.
    $i = $pager_first;
    if ($pager_last > $total) {
      $i += ($total - $pager_last);
      $pager_last = $total;
    }
    if ($i <= 0) {
      $pager_last += (1 - $i);
      $i = 1;
    }

    // Build items in same order as core: first/previous, pages, next/last.
    $items = [];

    // Create the "first" and "previous" links if we are not on the first page.
    if ($current > 0) {
      $items['first'] = [
        'href' => Url::fromRoute($route_name, $route_parameters, [
          'query' => $pager_manager->getUpdatedParameters($parameters, $element, 0),
        ])->toString(),
        'text' => $tags[0] ?? NULL,
        'attributes' => new Attribute(),
      ];
      $items['previous'] = [
        'href' => Url::fromRoute($route_name, $route_parameters, [
          'query' => $pager_manager->getUpdatedParameters($parameters, $element, $current - 1),
        ])->toString(),
        'text' => $tags[1] ?? NULL,
        'attributes' => new Attribute(),
      ];
    }

    // Ellipsis if there are pages before the visible window.
    if ($i > 1) {
      $variables['ellipses']['previous'] = TRUE;
    }

    // Generate the numbered page links within the quantity window.
    for (; $i <= $pager_last && $i <= $total; $i++) {
      $items['pages'][$i] = [
        'href' => Url::fromRoute($route_name, $route_parameters, [
          'query' => $pager_manager->getUpdatedParameters($parameters, $element, $i - 1),
        ])->toString(),
        'attributes' => new Attribute(),
      ];
      if ($i == $pager_current) {
        $variables['current'] = $i;
        $items['pages'][$i]['attributes']->setAttribute('aria-current', 'page');
      }
    }

    // Ellipsis if there are pages after the visible window.
    if ($i < $total + 1) {
      $variables['ellipses']['next'] = TRUE;
    }

    // Create the "next" and "last" links if we are not on the last page.
    if ($current < ($total - 1)) {
      $items['next'] = [
        'href' => Url::fromRoute($route_name, $route_parameters, [
          'query' => $pager_manager->getUpdatedParameters($parameters, $element, $current + 1),
        ])->toString(),
        'text' => $tags[3] ?? NULL,
        'attributes' => new Attribute(),
      ];
      $items['last'] = [
        'href' => Url::fromRoute($route_name, $route_parameters, [
          'query' => $pager_manager->getUpdatedParameters($parameters, $element, $total - 1),
        ])->toString(),
        'text' => $tags[4] ?? NULL,
        'attributes' => new Attribute(),
      ];
    }

    $variables['items'] = $items;
    $variables['heading_id'] = Html::getUniqueId('pagination-heading');
    $variables['pagination_heading_level'] = $variables['pager']['#pagination_heading_level'] ?? 'h4';
    if (!preg_match('/^h[1-6]$/', $variables['pagination_heading_level'])) {
      $variables['pagination_heading_level'] = 'h4';
    }
    $variables['#cache']['contexts'][] = 'url.query_args';

  }

  /**
   * Implements hook_preprocess_views_mini_pager().
   *
   * Preprocess mini pager to add variables needed for views pagination.
   *
   */
  #[Hook('preprocess_views_mini_pager')]
  public function preprocess_views_mini_pager(&$variables): void {
    /** @var \Drupal\Core\Pager\PagerManagerInterface $pager_manager */
    $pager_manager = \Drupal::service('pager.manager');
    if (empty($variables['pagination_heading_level'])) {
      $variables['pagination_heading_level'] = 'h4';
    }
    $tags =& $variables['tags'];
    $element = $variables['element'];
    $parameters = $variables['parameters'];
    $pager = $pager_manager->getPager($element);
    if (!$pager) {
      return;
    }
    $current = $pager->getCurrentPage();
    $total = $pager->getTotalPages();
    // Current is the page we are currently paged to.
    $variables['items']['current'] = $current + 1;
    if ($total > 1 && $current > 0) {
      $options = [
        'query' => $pager_manager->getUpdatedParameters($parameters, $element, $current - 1),
      ];
      $variables['items']['previous'] = [
        'href' => Url::fromRoute('<current>', [], $options)->toString(),
        'text' => $tags[1] ?? NULL,
        'attributes' => new Attribute(),
      ];
    }
    if ($current < $total - 1) {
      $options = [
        'query' => $pager_manager->getUpdatedParameters($parameters, $element, $current + 1),
      ];
      $variables['items']['next'] = [
        'href' => Url::fromRoute('<current>', [], $options)->toString(),
        'text' => $tags[3] ?? NULL,
        'attributes' => new Attribute(),
      ];
    }
    // This is based on the entire current query string. We need to ensure
    // cacheability is affected accordingly.
    $variables['#cache']['contexts'][] = 'url.query_args';
    $variables['heading_id'] = Html::getUniqueId('pagination-heading');
  }

}
