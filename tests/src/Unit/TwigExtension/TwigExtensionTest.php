<?php

namespace Drupal\Tests\cohesion\Unit\TwigExtension;

use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\cohesion_templates\TwigExtension\TwigExtension;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Tests for TwigExtension (block rendering, cache pollution fix, permissions).
 *
 * @group Cohesion
 */
class TwigExtensionTest extends UnitTestCase {

  /**
   * The twig extension class under test.
   *
   * @var \Drupal\cohesion_templates\TwigExtension\TwigExtension
   */
  public $twigExtension;

  /**
   * Cohesion current route match service.
   *
   * @var \Drupal\cohesion\Routing\CohesionCurrentRouteMatch|\PHPUnit\Framework\MockObject\MockObject
   */
  public $cohesionCurrentRouteMatch;

  /**
   * A user.
   *
   * @var \Drupal\Core\Session\AccountInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  public $user;

  /**
   * A route object.
   *
   * @var \Symfony\Component\Routing\Route|\PHPUnit\Framework\MockObject\MockObject
   */
  public $route;

  /**
   * Sets up the test.
   */
  protected function setUp(): void {
    parent::setUp();
    $renderer = $this->createMock('Drupal\Core\Render\RendererInterface');
    $token = $this->createMock('\Drupal\Core\Utility\Token');
    $themeRegistry = $this->createMock('\Drupal\Core\Theme\Registry');
    $twigEnvironment = $this->createMock('\Drupal\Core\Template\TwigEnvironment');
    $uuid = $this->createMock('\Drupal\Component\Uuid\UuidInterface');
    $entity_type_manager = $this->createMock('\Drupal\Core\Entity\EntityTypeManagerInterface');
    $stream_wrapper_manager = $this->createMock('\Drupal\Core\StreamWrapper\StreamWrapperManager');
    $extension_mime_type_guesser = $this->createMock('\Symfony\Component\Mime\MimeTypeGuesserInterface');
    $theme_manager = $this->createMock('\Drupal\Core\Theme\ThemeManagerInterface');
    $cohesion_utils = $this->createMock('\Drupal\cohesion\Services\CohesionUtils');
    $loggerChannelFactory = $this->createMock('\Drupal\Core\Logger\LoggerChannelFactoryInterface');
    $this->route = $this->createMock('\Symfony\Component\Routing\Route');
    $this->cohesionCurrentRouteMatch = $this->createMock('Drupal\cohesion\Routing\CohesionCurrentRouteMatch');
    $this->cohesionCurrentRouteMatch->method('getRouteObject')->willReturn($this->route);
    $this->user = $this->createMock('Drupal\Core\Session\AccountInterface');
    $fileUrlGenerator = $this->createMock('\Drupal\Core\File\FileUrlGeneratorInterface');
    $entityRepository = $this->createMock('\Drupal\Core\Entity\EntityRepositoryInterface');
    $usageUpdateManager = $this->createMock('\Drupal\cohesion\UsageUpdateManager');

    $this->twigExtension = new TwigExtension($renderer, $token, $themeRegistry, $twigEnvironment, $uuid,
      $entity_type_manager, $stream_wrapper_manager, $extension_mime_type_guesser,
      $theme_manager, $cohesion_utils, $loggerChannelFactory, $this->cohesionCurrentRouteMatch, $this->user,
      $fileUrlGenerator, $entityRepository, $usageUpdateManager);

    $cacheContextsManager = $this->createMock(CacheContextsManager::class);
    $cacheContextsManager->method('optimizeTokens')->willReturnCallback(function (array $tokens) {
      return $tokens;
    });
    $cacheContextsManager->method('assertValidTokens')->willReturn(TRUE);
    $container = $this->createMock(ContainerInterface::class);
    $container->method('get')->willReturnCallback(function ($id) use ($cacheContextsManager) {
      if ($id === 'cache_contexts_manager') {
        return $cacheContextsManager;
      }
      return NULL;
    });
    $container->method('has')->willReturnCallback(function ($id) {
      return $id === 'cache_contexts_manager';
    });
    \Drupal::setContainer($container);
  }

  /**
   * Tests addComponentFrontEndBuilderMarkup wraps build with SSA markers.
   *
   * @dataProvider addComponentFrontEndBuilderMarkupDataProvider
   * @covers \Drupal\cohesion_templates\TwigExtension\TwigExtension::addComponentFrontEndBuilderMarkup
   */
  public function testAddComponentFrontEndBuilderMarkup(array $data): void {
    $node = $this->createMock('Drupal\node\Entity\Node');
    $node->method('getEntityTypeId')->willReturn('node');
    $node->method('id')->willReturn('1');

    $block = $this->createMock('Drupal\block_content\Entity\BlockContent');
    $block->method('getEntityTypeId')->willReturn('block_content');
    $block->method('id')->willReturn('2');

    $componentInstanceUuid = 'A-UUID';
    $input_build = ['key' => 'value'];

    $with_markup_expectation = [
      ['#type' => 'container', '#attributes' => ['data-ssa-start' => [$componentInstanceUuid]]],
      $input_build,
      ['#type' => 'container', '#attributes' => ['data-ssa-end' => [$componentInstanceUuid]]],
    ];
    $no_markup_expectation = [$input_build];

    $context_entity = $data['context_key'] === 'node' ? $node : $block;
    $context = [$data['context_key'] => $context_entity] + $data['context_extra'];

    if ($data['expectation_type'] === 'with_uuids') {
      $expectation = $with_markup_expectation;
      $expectation[0]['#attributes']['data-ssa-component-content-uuid'] = $data['component_content_UUID'];
      $expectation[0]['#attributes']['data-ssa-component-content-id'] = $data['component_content_id'];
    }
    elseif ($data['expectation_type'] === 'with_markup') {
      $expectation = $with_markup_expectation;
    }
    else {
      $expectation = $no_markup_expectation;
    }

    $this->user->method('hasPermission')->willReturn($data['has_permission']);
    $this->route->method('getOption')->with('sitestudio_build')->willReturn($data['is_page_builder']);
    $this->cohesionCurrentRouteMatch->method('getRouteEntities')->willReturn([$node]);

    $test_build = $this->twigExtension->addComponentFrontEndBuilderMarkup(
      $input_build,
      $context,
      $componentInstanceUuid,
      $data['component_content_UUID'] ?? NULL,
      $data['component_content_id'] ?? NULL
    );
    $this->assertEquals($expectation, $test_build);
  }

  /**
   * Data provider for ::testAddComponentFrontEndBuilderMarkup.
   */
  public static function addComponentFrontEndBuilderMarkupDataProvider(): array {
    return [
      [
        [
          'context_key' => 'node',
          'context_extra' => [],
          'expectation_type' => 'with_markup',
          'has_permission' => TRUE,
          'is_page_builder' => 'TRUE',
        ],
      ],
      [
        [
          'context_key' => 'block',
          'context_extra' => [],
          'expectation_type' => 'no_markup',
          'has_permission' => TRUE,
          'is_page_builder' => 'TRUE',
        ],
      ],
      [
        [
          'context_key' => 'node',
          'context_extra' => [],
          'expectation_type' => 'no_markup',
          'has_permission' => FALSE,
          'is_page_builder' => 'TRUE',
        ],
      ],
      [
        [
          'context_key' => 'node',
          'context_extra' => [],
          'expectation_type' => 'no_markup',
          'has_permission' => TRUE,
          'is_page_builder' => 'FALSE',
        ],
      ],
      [
        [
          'context_key' => 'node',
          'context_extra' => ['hideContextualLinks' => TRUE],
          'expectation_type' => 'no_markup',
          'has_permission' => TRUE,
          'is_page_builder' => 'TRUE',
        ],
      ],
      [
        [
          'context_key' => 'node',
          'context_extra' => ['isPreview' => TRUE],
          'expectation_type' => 'no_markup',
          'has_permission' => TRUE,
          'is_page_builder' => 'TRUE',
        ],
      ],
      [
        [
          'context_key' => 'node',
          'context_extra' => [],
          'expectation_type' => 'with_uuids',
          'has_permission' => TRUE,
          'is_page_builder' => 'TRUE',
          'component_content_UUID' => 'component-content-UUID',
          'component_content_id' => 'component-content-id',
        ],
      ],
    ];
  }

  /**
   * Tests drupalBlock access and cache metadata handling.
   *
   * @dataProvider drupalBlockProvider
   * @covers \Drupal\cohesion_templates\TwigExtension\TwigExtension::drupalBlock
   */
  public function testDrupalBlock(array $accessArgs, ?array $blockExtra, ?array $viewReturn, array $expect): void {
    $access = $this->mockAccess(...$accessArgs);
    $block = $this->createMock('\Drupal\block\Entity\Block');
    $block->method('access')->willReturn($access);
    foreach ($blockExtra ?? [] as $method => $return) {
      $block->method($method)->willReturn($return);
    }

    $viewBuilder = $viewReturn !== NULL ? $this->createViewBuilder($viewReturn) : NULL;
    $this->mockEntityTypeManager($block, $viewBuilder);
    $result = $this->twigExtension->drupalBlock($expect['id'] ?? 'test_block');

    if (isset($expect['has_markup'])) {
      $expect['has_markup'] ? $this->assertArrayHasKey('#markup', $result) : $this->assertArrayNotHasKey('#markup', $result);
    }
    if (isset($expect['markup'])) {
      $this->assertEquals($expect['markup'], $result['#markup'] ?? NULL);
    }
    foreach ($expect['tags_contain'] ?? [] as $tag) {
      $this->assertContains($tag, $result['#cache']['tags'] ?? []);
    }
    foreach ($expect['tags_exclude'] ?? [] as $tag) {
      $this->assertNotContains($tag, $result['#cache']['tags'] ?? []);
    }
    foreach ($expect['contexts_contain'] ?? [] as $ctx) {
      $this->assertContains($ctx, $result['#cache']['contexts'] ?? []);
    }
    foreach ($expect['contexts_exclude'] ?? [] as $ctx) {
      $this->assertNotContains($ctx, $result['#cache']['contexts'] ?? []);
    }
    if (isset($expect['max_age'])) {
      $this->assertEquals($expect['max_age'], $result['#cache']['max-age'] ?? NULL);
    }
  }

  /**
   * Data provider for ::testDrupalBlock.
   */
  public static function drupalBlockProvider(): array {
    $allowed = [
      '\Drupal\Core\Access\AccessResultAllowed',
      TRUE,
      ['user'],
      ['config:block.block.test_block'],
    ];
    $forbidden = [
      '\Drupal\Core\Access\AccessResultForbidden',
      FALSE,
      ['user.permissions'],
      ['config:block.block.test_block'],
    ];

    return [
      'access allowed' => [
        $allowed,
        NULL,
        [
          '#markup' => 'Block content',
          '#cache' => ['tags' => [], 'contexts' => [], 'max-age' => 3600],
        ],
        [
          'has_markup' => TRUE,
          'markup' => 'Block content',
          'tags_contain' => ['config:block.block.test_block'],
          'contexts_contain' => ['user'],
        ],
      ],
      'access denied, no metadata pollution (ACMS-5715)' => [
        $forbidden,
        [
          'getCacheTags' => ['block:test', 'node:123'],
          'getCacheContexts' => ['url.query_args', 'user.roles'],
          'getCacheMaxAge' => 0,
        ],
        NULL,
        [
          'has_markup' => FALSE,
          'tags_exclude' => ['block:test', 'node:123'],
          'contexts_exclude' => ['url.query_args', 'user.roles'],
          'tags_contain' => ['config:block.block.test_block'],
          'contexts_contain' => ['user.permissions'],
          'max_age' => -1,
        ],
      ],
    ];
  }

  /**
   * Creates a mock entity view builder returning the given render array.
   *
   * @param array $return
   *   The render array to return from view().
   *
   * @return \Drupal\Core\Entity\EntityViewBuilderInterface
   *   The mock view builder.
   */
  private function createViewBuilder(array $return): EntityViewBuilderInterface {
    $vb = $this->createMock('\Drupal\Core\Entity\EntityViewBuilderInterface');
    $vb->method('view')->willReturn($return);
    return $vb;
  }

  /**
   * Tests drupalBlock edge cases: nonexistent block, Twig Markup, neutral.
   *
   * @dataProvider edgeCasesProvider
   * @covers \Drupal\cohesion_templates\TwigExtension\TwigExtension::drupalBlock
   */
  public function testDrupalBlockEdgeCases(string $case): void {
    $block = NULL;
    $id = 'nonexistent';
    $has_markup = FALSE;
    $needs_view_builder = FALSE;

    switch ($case) {
      case 'twig_markup':
        $twig_markup = $this->createMock('\Twig\Markup');
        $twig_markup->method('__toString')->willReturn('test_block');
        $id = $twig_markup;
        $allowed_access = $this->mockAccess('\Drupal\Core\Access\AccessResultAllowed', TRUE);
        $block = $this->createMock('\Drupal\block\Entity\Block');
        $block->method('access')->willReturn($allowed_access);
        $has_markup = TRUE;
        $needs_view_builder = TRUE;
        break;

      case 'neutral_access':
        $id = 'neutral';
        $neutral_access = $this->mockAccess('\Drupal\Core\Access\AccessResultNeutral', FALSE);
        $block = $this->createMock('\Drupal\block\Entity\Block');
        $block->method('access')->willReturn($neutral_access);
        break;

      default:
        // non_existent.
        $id = 'nonexistent';
        break;
    }

    $view_builder = NULL;
    if ($needs_view_builder) {
      $view_builder = $this->createMock('\Drupal\Core\Entity\EntityViewBuilderInterface');
      $view_builder->method('view')->willReturn(['#markup' => 'Content']);
    }

    $this->mockEntityTypeManager($block, $view_builder);
    $result = $this->twigExtension->drupalBlock($id);

    if ($has_markup) {
      $this->assertArrayHasKey('#markup', $result);
    }
    else {
      $this->assertArrayNotHasKey('#markup', $result);
    }
  }

  /**
   * Data provider for ::testDrupalBlockEdgeCases.
   */
  public static function edgeCasesProvider(): array {
    return [
      'non_existent' => ['non_existent'],
      'twig_markup' => ['twig_markup'],
      'neutral_access' => ['neutral_access'],
    ];
  }

  /**
   * Tests hasDrupalPermission with various permission inputs.
   *
   * @dataProvider hasDrupalPermissionProvider
   * @covers \Drupal\cohesion_templates\TwigExtension\TwigExtension::hasDrupalPermission
   */
  public function testHasDrupalPermission($permissions, $permission_map, $expected) {
    $user = $this->createMock('Drupal\Core\Session\AccountInterface');
    $user->method('hasPermission')->willReturnMap($permission_map);

    $this->setProtectedProperty($this->twigExtension, 'currentUser', $user);

    $result = $this->twigExtension->hasDrupalPermission($permissions);
    $this->assertEquals($expected, $result);
  }

  /**
   * Data provider for ::testHasDrupalPermission.
   */
  public static function hasDrupalPermissionProvider(): array {
    return [
      'single_permission_granted' => [
        'view content',
        [['view content', TRUE]],
        TRUE,
      ],
      'single_permission_denied' => [
        'administer site',
        [['administer site', FALSE]],
        FALSE,
      ],
      'array_all_granted' => [
        ['view content', 'access content'],
        [['view content', TRUE], ['access content', TRUE]],
        TRUE,
      ],
      'array_one_denied' => [
        ['view content', 'administer site'],
        [['view content', TRUE], ['administer site', FALSE]],
        FALSE,
      ],
      'array_all_denied' => [
        ['administer site', 'administer users'],
        [['administer site', FALSE], ['administer users', FALSE]],
        FALSE,
      ],
      'empty_array' => [[], [], TRUE],
    ];
  }

  /**
   * Sets a protected or private property on an object via reflection.
   *
   * @param object $object
   *   The object to modify.
   * @param string $property
   *   The property name.
   * @param mixed $value
   *   The value to set.
   */
  private function setProtectedProperty(object $object, string $property, mixed $value) {
    $reflection = new \ReflectionClass($object);
    $property = $reflection->getProperty($property);
    $property->setAccessible(TRUE);
    $property->setValue($object, $value);
  }

  /**
   * Creates a mock access result object with given cache metadata.
   *
   * @param string $class
   *   Access result class (e.g. AccessResultAllowed).
   * @param bool $is_allowed
   *   Whether access is allowed.
   * @param array $contexts
   *   Cache contexts.
   * @param array $tags
   *   Cache tags.
   *
   * @return \PHPUnit\Framework\MockObject\MockObject
   *   The mock access result.
   */
  private function mockAccess(
    string $class,
    bool $is_allowed,
    array $contexts = [],
    array $tags = [],
  ) {
    $access = $this->createMock($class);
    $access->method('isAllowed')->willReturn($is_allowed);
    $access->method('getCacheContexts')->willReturn($contexts);
    $access->method('getCacheTags')->willReturn($tags);
    $access->method('getCacheMaxAge')->willReturn(-1);
    $access->method('orIf')->willReturnSelf();
    $access->method('andIf')->willReturnSelf();
    return $access;
  }

  /**
   * @covers \Drupal\cohesion_templates\TwigExtension\TwigExtension::drupalBlock
   */
  public function testDrupalBlockPreservesExistingCacheContexts(): void {
    $access = $this->mockAccess(
      '\Drupal\Core\Access\AccessResultAllowed',
      TRUE,
      ['languages:language_interface'],
      ['config:block.block.views_block'],
    );

    $block = $this->createMock('\Drupal\block\Entity\Block');
    $block->method('access')->willReturn($access);

    $viewBuilderReturn = [
      '#markup' => 'Filtered view content',
      '#cache' => [
        'contexts' => ['url', 'url.query_args', 'user.permissions', 'languages:language_content'],
        'tags' => ['node_list', 'config:views.view.test_view'],
        'max-age' => 3600,
      ],
    ];
    $viewBuilder = $this->createViewBuilder($viewBuilderReturn);
    $this->mockEntityTypeManager($block, $viewBuilder);

    $result = $this->twigExtension->drupalBlock('views_block');

    // Original block contexts preserved.
    $this->assertContains('url', $result['#cache']['contexts']);
    $this->assertContains('url.query_args', $result['#cache']['contexts']);
    $this->assertContains('user.permissions', $result['#cache']['contexts']);
    $this->assertContains('languages:language_content', $result['#cache']['contexts']);
    // Access contexts merged in.
    $this->assertContains('languages:language_interface', $result['#cache']['contexts']);
    // Original block tags preserved.
    $this->assertContains('node_list', $result['#cache']['tags']);
    $this->assertContains('config:views.view.test_view', $result['#cache']['tags']);
    // Access tags merged in.
    $this->assertContains('config:block.block.views_block', $result['#cache']['tags']);
  }

  /**
   * Mocks the entity type manager with block storage.
   */
  private function mockEntityTypeManager($block, $view_builder = NULL) {
    $storage = $this->createMock('\Drupal\Core\Entity\EntityStorageInterface');
    $storage->method('load')->willReturn($block);

    $manager = $this->createMock('\Drupal\Core\Entity\EntityTypeManagerInterface');
    $manager->method('getStorage')->willReturn($storage);

    if ($view_builder) {
      $manager->method('getViewBuilder')->willReturn($view_builder);
    }

    $this->setProtectedProperty($this->twigExtension, 'entityTypeManager', $manager);
  }

}
