<?php

declare(strict_types=1);

namespace Drupal\sitestudio_page_builder\Plugin\TopBarItem;

/**
 * @file
 * Provides the Page Builder button for Navigation top bar (Drupal 11.3+).
 */

// @phpstan-ignore-next-line class.notFound
if (!class_exists('\\Drupal\\navigation\\TopBarItemBase')) {
  return;
}

use Drupal\navigation\Attribute\TopBarItem;
use Drupal\navigation\TopBarItemBase;
use Drupal\navigation\TopBarRegion;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\sitestudio_page_builder\Services\SitestudioPageBuilderManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the Page Builder button for Navigation top bar.
 *
 * Navigation bypasses hook_toolbar() and uses TopBarItem plugins instead.
 * Only available when Navigation module is installed (Drupal 11+).
 *
 * Drupal 10/11 compatibility: This file early-returns before the class is
 * defined when Navigation module classes are absent (Drupal 10).
 *
 * @internal
 */
/**
 *@phpstan-ignore-next-line class.notFound
 */
#[TopBarItem(
    id: 'sitestudio_page_builder',
    // @phpstan-ignore-next-line classConstant.notFound
    region: TopBarRegion::Actions,
    label: new TranslatableMarkup('Page builder'),
)]
class PageBuilderButton extends TopBarItemBase implements ContainerFactoryPluginInterface {

  use StringTranslationTrait;

  /**
   * Constructs a PageBuilderButton object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   Current user.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   Current route match.
   * @param \Drupal\sitestudio_page_builder\Services\SitestudioPageBuilderManagerInterface $page_builder_manager
   *   Page builder manager.
   */
  // @phpstan-ignore-next-line method.nonObject
  public function __construct(
    array $configuration,
    string $plugin_id,
    mixed $plugin_definition,
    protected readonly AccountProxyInterface $current_user,
    protected readonly RouteMatchInterface $route_match,
    protected readonly SitestudioPageBuilderManagerInterface $page_builder_manager,
  ) {
    // @phpstan-ignore-next-line method.nonObject
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition,
  ): static {
    // @phpstan-ignore-next-line unsafeInstantiation
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user'),
      $container->get('current_route_match'),
      $container->get('sitestudio_page_builder.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $build = [
      '#cache' => [
        'contexts' => ['user.permissions', 'route'],
      ],
    ];

    // Check permissions - inline visibility logic matches Admin Toolbar.
    if (!$this->current_user->hasPermission('access visual page builder')) {
      return $build;
    }

    // Don't show on admin routes.
    $is_admin = \Drupal::service('router.admin_context')->isAdminRoute();
    if ($is_admin) {
      return $build;
    }

    // Check if page builder should be enabled for this entity.
    $entity = $this->page_builder_manager->shouldEnablePageBuilder();
    if (!$entity) {
      return $build;
    }

    $build['#type'] = 'container';
    $build['#weight'] = -100;
    $build['#attributes'] = [
      'id' => 'ssa-builder-toggle',
      'class' => [
        'ssa-builder-toggle',
        'hidden',
      ],
    ];

    $build['button'] = [
      '#type' => 'component',
      '#component' => 'navigation:toolbar-button',
      '#props' => [
        // @phpstan-ignore-next-line methodCallOnUntyped
        'text' => $this->t('Page builder'),
        'html_tag' => 'button',
        'icon' => [
          'icon_id' => 'sitestudio',
          'pack_id' => 'cohesion',
          'settings' => [
            'class' => 'toolbar-button__icon',
            'size' => 20,
          ],
        ],
        'modifiers' => ['primary'],
        'attributes' => [
          'id' => 'coh-builder-btn',
          'type' => 'button',
          'aria-pressed' => 'false',
        ],
      ],
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function access($operation, ?AccountProxyInterface $account = NULL, ?Url $url = NULL): AccessResult {
    $account ??= $this->current_user;
    return $account->hasPermission('access visual page builder')
      ? AccessResult::allowed()
      : AccessResult::forbidden();
  }

}
