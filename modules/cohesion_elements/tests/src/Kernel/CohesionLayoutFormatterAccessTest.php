<?php

namespace Drupal\Tests\cohesion_elements\Kernel;

use Drupal\Component\Serialization\Json;
use Drupal\cohesion_elements\Entity\CohesionLayout;
use Drupal\Core\Session\AnonymousUserSession;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\menu_link_content\Entity\MenuLinkContent;

/**
 * Tests layout access for owners that deny view access to anonymous users.
 *
 * @group Cohesion
 */
class CohesionLayoutFormatterAccessTest extends KernelTestBase {

  /**
   * Marker in the seeded template, proving canvas content is rendered.
   */
  const MARKER = 'layout-canvas-marker';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'field',
    'file',
    'link',
    'menu_link_content',
    'entity_reference_revisions',
    'token',
    'cohesion',
    'cohesion_elements',
    'cohesion_templates',
  ];

  /**
   * The menu link owning the layout.
   *
   * @var \Drupal\menu_link_content\Entity\MenuLinkContent
   */
  protected $menuLink;

  /**
   * The layout.
   *
   * @var \Drupal\cohesion_elements\Entity\CohesionLayout
   */
  protected $layout;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installSchema('cohesion', ['coh_usage']);
    $this->installEntitySchema('user');
    $this->installEntitySchema('menu_link_content');
    $this->installEntitySchema('cohesion_layout');

    FieldStorageConfig::create([
      'field_name' => 'field_layout',
      'entity_type' => 'menu_link_content',
      'type' => 'cohesion_entity_reference_revisions',
      'cardinality' => 1,
      'settings' => ['target_type' => 'cohesion_layout'],
    ])->save();
    FieldConfig::create([
      'field_name' => 'field_layout',
      'entity_type' => 'menu_link_content',
      'bundle' => 'menu_link_content',
      'label' => 'Layout',
    ])->save();

    $this->layout = CohesionLayout::create([
      'json_values' => '{"canvas":[],"mapper":{},"model":{}}',
      'template' => Json::encode([
        'coh-generic-theme' => Json::encode(['twig' => '<p>' . self::MARKER . '</p>']),
      ]),
      'parent_type' => 'menu_link_content',
      'parent_field_name' => 'field_layout',
    ]);
    $this->layout->save();

    $this->menuLink = MenuLinkContent::create([
      'title' => 'Link',
      'link' => ['uri' => 'internal:/'],
      'menu_name' => 'main',
      'field_layout' => [
        'target_id' => $this->layout->id(),
        'target_revision_id' => $this->layout->getRevisionId(),
      ],
    ]);
    $this->menuLink->save();

    $this->layout->set('parent_id', $this->menuLink->id());
    $this->layout->save();
  }

  /**
   * Direct access to the layout follows the owner, which denies anonymous.
   */
  public function testDirectViewFollowsOwnerAccess(): void {
    $anonymous = new AnonymousUserSession();
    $this->assertFalse($this->menuLink->access('view', $anonymous));
    $this->assertFalse($this->layout->access('view', $anonymous));
  }

  /**
   * The field formatter still renders the layout for anonymous users.
   */
  public function testFormatterRendersLayoutForAnonymous(): void {
    $this->container->get('current_user')->setAccount(new AnonymousUserSession());
    $menu_link = MenuLinkContent::load($this->menuLink->id());
    $build = $menu_link->get('field_layout')->view([
      'type' => 'cohesion_entity_reference_revisions_entity_view',
      'label' => 'hidden',
    ]);
    $this->assertSame(self::MARKER, trim(strip_tags($this->layout->getTwig('coh-generic-theme'))), 'Fixture template is seeded.');
    $renderer = $this->container->get('renderer');
    $output = (string) (method_exists($renderer, 'renderInIsolation')
      ? $renderer->renderInIsolation($build)
      : $renderer->renderPlain($build));
    $this->assertStringContainsString(self::MARKER, $output);
  }

}
