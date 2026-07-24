<?php

namespace Drupal\sitestudio_page_builder\Plugin\TopBarItem;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\AdminContext;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Template\Attribute;
use Drupal\sitestudio_page_builder\Services\SitestudioPageBuilderManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

// Only define this plugin if Navigation module is available (Drupal 11+).
// For Drupal 10, hook_toolbar() handles the button instead.
if (!class_exists('\\Drupal\\navigation\\TopBarItemBase')) {
  return;
}

use Drupal\navigation\Attribute\TopBarItem;
use Drupal\navigation\TopBarItemBase;
use Drupal\navigation\TopBarRegion;

/**
 * Provides the Page Builder button for Navigation top bar.
 *
 * Navigation bypasses hook_toolbar() and uses TopBarItem plugins instead.
 * Only available when Navigation module is installed (Drupal 11+).
 *
 * @internal
 */
/**
 * Drupal 10/11 compatibility: Navigation classes only exist in Drupal 11+.
 *
 * @phpstan-ignore-next-line class.notFound
 */
#[TopBarItem(
    id: 'sitestudio_page_builder',
    /** @phpstan-ignore-next-line classConstant.notFound */
    region: TopBarRegion::Actions,
    label: new TranslatableMarkup('Page builder'),
)]
/**
 * @phpstan-ignore-next-line class.notFound
 */
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
   * @param \Drupal\sitestudio_page_builder\Services\SitestudioPageBuilderManagerInterface $pageBuilderManager
   *   The page builder manager service.
   * @param \Drupal\Core\Routing\AdminContext $adminContext
   *   The admin context service.
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   The current user account.
   */

  /**
   * @phpstan-ignore-next-line method.nonObject
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected SitestudioPageBuilderManagerInterface $pageBuilderManager,
    protected AdminContext $adminContext,
    protected AccountInterface $currentUser,
  ) {
    /** @phpstan-ignore-next-line method.nonObject */
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
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('sitestudio_page_builder.manager'),
      $container->get('router.admin_context'),
      $container->get('current_user')
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
    if (!$this->currentUser->hasPermission('access visual page builder')) {
      return $build;
    }

    // Don't show on admin routes.
    if ($this->adminContext->isAdminRoute()) {
      return $build;
    }

    // Check if page builder should be enabled for this entity.
    $entity = $this->pageBuilderManager->shouldEnablePageBuilder();
    if (!$entity) {
      return $build;
    }

    // Same IDs/classes as Admin Toolbar for JavaScript compatibility.
    $build['#type'] = 'container';
    $build['#weight'] = -100;
    $build['#attributes'] = [
      'id' => 'ssa-builder-toggle',
      'class' => [
        'ssa-builder-toggle',
        'hidden',
      ],
    ];

    // Matches Edit button styling.
    $build['button'] = [
      '#type' => 'component',
      '#component' => 'navigation:toolbar-button',
      '#props' => [
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
        'attributes' => new Attribute([
          'id' => 'coh-builder-btn',
          'type' => 'button',
          'aria-pressed' => 'false',
        ]),
      ],
    ];

    return $build;
  }

}
