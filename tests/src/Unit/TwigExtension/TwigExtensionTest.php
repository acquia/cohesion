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
  public function testHasDrupalPermission($permissions, $permission_map, $expected): void {
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

  /**
   * Tests tokenReplace does not encode ampersands in layout canvas context.
   *
   * @dataProvider tokenReplaceAmpersandProvider
   * @covers \Drupal\cohesion_templates\TwigExtension\TwigExtension::tokenReplace
   */
  public function testTokenReplaceAmpersandNotEncoded(string $input, string $expected, bool $isTemplate): void {
    $token = $this->createMock('\Drupal\Core\Utility\Token');
    $token->method('replace')->willReturnArgument(0);
    $this->setProtectedProperty($this->twigExtension, 'token', $token);

    $languageManager = $this->createMock('\Drupal\Core\Language\LanguageManagerInterface');
    $language = $this->createMock('\Drupal\Core\Language\LanguageInterface');
    $language->method('getId')->willReturn('en');
    $languageManager->method('getCurrentLanguage')->willReturn($language);

    $container = \Drupal::getContainer();
    $newContainer = $this->createMock(ContainerInterface::class);
    $newContainer->method('get')->willReturnCallback(function ($id) use ($container, $languageManager) {
      if ($id === 'language_manager') {
        return $languageManager;
      }
      return $container->get($id);
    });
    $newContainer->method('has')->willReturnCallback(function ($id) use ($container) {
      return $container->has($id);
    });
    \Drupal::setContainer($newContainer);

    try {
      $result = $this->twigExtension->tokenReplace($input, [], [], $isTemplate);
      $this->assertEquals($expected, (string) $result);
    }
    finally {
      \Drupal::setContainer($container);
    }
  }

  /**
   * Data provider for ::testTokenReplaceAmpersandNotEncoded.
   */
  public static function tokenReplaceAmpersandProvider(): array {
    return [
      'ampersand in layout canvas context not encoded' => [
        'Health & Benefits - testing',
        'Health & Benefits - testing',
        FALSE,
      ],
      'multiple ampersands in layout canvas context' => [
        'Tom & Jerry & Friends',
        'Tom & Jerry & Friends',
        FALSE,
      ],
      'encoded ampersand decoded in layout canvas' => [
        'Health &amp; Benefits',
        'Health & Benefits',
        FALSE,
      ],
      'ampersand in template context not double-encoded' => [
        'Health & Benefits - testing',
        'Health & Benefits - testing',
        TRUE,
      ],
      'encoded ampersand decoded in template context' => [
        'Health &amp; Benefits',
        'Health & Benefits',
        TRUE,
      ],
      'script tags not escaped in layout canvas (escaping deferred)' => [
        '<script>alert("xss")</script>',
        '<script>alert("xss")</script>',
        FALSE,
      ],
      'script tags escaped in template context' => [
        '<script>alert("xss")</script>',
        '&lt;script&gt;alert("xss")&lt;/script&gt;',
        TRUE,
      ],
      'encoded script tags decoded in layout canvas (escaping deferred)' => [
        '&lt;script&gt;alert("xss")&lt;/script&gt;',
        '<script>alert("xss")</script>',
        FALSE,
      ],
      'p tags not escaped in layout canvas (escaping deferred)' => [
        '<p>a wysiwyg with content</p>',
        '<p>a wysiwyg with content</p>',
        FALSE,
      ],
      'p tags escaped in template context' => [
        '<p>a wysiwyg with content</p>',
        '&lt;p&gt;a wysiwyg with content&lt;/p&gt;',
        TRUE,
      ],
    ];
  }

  /**
   * Tests renderStyles returns empty string when entity not found.
   *
   * @covers \Drupal\cohesion_templates\TwigExtension\TwigExtension::renderStyles
   */
  public function testRenderStylesReturnsEmptyWhenEntityNotFound(): void {
    $storage = $this->createMock('\Drupal\Core\Entity\EntityStorageInterface');
    $storage->method('load')->willReturn(NULL);

    $manager = $this->createMock('\Drupal\Core\Entity\EntityTypeManagerInterface');
    $manager->method('getStorage')->willReturn($storage);

    $this->setProtectedProperty($this->twigExtension, 'entityTypeManager', $manager);

    $result = $this->twigExtension->renderStyles('nonexistent_id', 'cohesion_component');
    $this->assertEquals('', $result);
  }

  /**
   * Tests renderStyles attaches element style library when enabled.
   *
   * @covers \Drupal\cohesion_templates\TwigExtension\TwigExtension::renderStyles
   */
  public function testRenderStylesAttachesElementLibrary(): void {
    $entity = $this->createMock('\Drupal\cohesion_elements\Entity\Component');
    $entity->method('uuid')->willReturn('test-uuid');

    $storage = $this->createMock('\Drupal\Core\Entity\EntityStorageInterface');
    $storage->method('load')->willReturn($entity);

    $manager = $this->createMock('\Drupal\Core\Entity\EntityTypeManagerInterface');
    $manager->method('getStorage')->willReturn($storage);

    $cohesionUtils = $this->createMock('\Drupal\cohesion\Services\CohesionUtils');
    $cohesionUtils->method('loadElementStylesOnPageOnly')->willReturn(TRUE);

    $usageUpdateManager = $this->createMock('\Drupal\cohesion\UsageUpdateManager');
    $usageUpdateManager->method('getEntitiesInUseInSource')->willReturn([]);

    $renderer = $this->createMock('\Drupal\Core\Render\RendererInterface');
    $renderer->method('render')->willReturnCallback(function ($build) {
      $this->assertArrayHasKey('#attached', $build);
      $this->assertContains('cohesion/cohesion_component_test_component', $build['#attached']['library']);
      return 'rendered';
    });

    $this->setProtectedProperty($this->twigExtension, 'entityTypeManager', $manager);
    $this->setProtectedProperty($this->twigExtension, 'cohesionUtils', $cohesionUtils);
    $this->setProtectedProperty($this->twigExtension, 'usageUpdateManager', $usageUpdateManager);
    $this->setProtectedProperty($this->twigExtension, 'renderer', $renderer);

    $result = $this->twigExtension->renderStyles('test_component', 'cohesion_component');
    $this->assertEquals('rendered', $result);
  }

  /**
   * Tests renderStyles attaches custom style libraries.
   *
   * @covers \Drupal\cohesion_templates\TwigExtension\TwigExtension::renderStyles
   */
  public function testRenderStylesAttachesCustomStyleLibraries(): void {
    $entity = $this->createMock('\Drupal\cohesion_elements\Entity\Component');
    $entity->method('uuid')->willReturn('test-uuid');

    $customStyle = $this->createMock('\Drupal\cohesion_custom_styles\Entity\CustomStyle');
    $customStyle->method('id')->willReturn('my_custom_style');

    $componentStorage = $this->createMock('\Drupal\Core\Entity\EntityStorageInterface');
    $componentStorage->method('load')->willReturn($entity);

    $customStyleStorage = $this->createMock('\Drupal\Core\Entity\EntityStorageInterface');
    $customStyleStorage->method('loadByProperties')
      ->with(['uuid' => 'custom-style-uuid'])
      ->willReturn([$customStyle]);

    $manager = $this->createMock('\Drupal\Core\Entity\EntityTypeManagerInterface');
    $manager->method('getStorage')->willReturnCallback(function ($entityType) use ($componentStorage, $customStyleStorage) {
      if ($entityType === 'cohesion_custom_style') {
        return $customStyleStorage;
      }
      return $componentStorage;
    });

    $cohesionUtils = $this->createMock('\Drupal\cohesion\Services\CohesionUtils');
    $cohesionUtils->method('loadElementStylesOnPageOnly')->willReturn(FALSE);

    $usageUpdateManager = $this->createMock('\Drupal\cohesion\UsageUpdateManager');
    $usageUpdateManager->method('getEntitiesInUseInSource')
      ->willReturn(['custom-style-uuid' => ['source_uuid' => 'test-uuid']]);

    $renderer = $this->createMock('\Drupal\Core\Render\RendererInterface');
    $renderer->method('render')->willReturnCallback(function ($build) {
      $this->assertArrayHasKey('#attached', $build);
      $this->assertContains('cohesion/coh_custom_style_my_custom_style', $build['#attached']['library']);
      return 'rendered_with_custom_styles';
    });

    $this->setProtectedProperty($this->twigExtension, 'entityTypeManager', $manager);
    $this->setProtectedProperty($this->twigExtension, 'cohesionUtils', $cohesionUtils);
    $this->setProtectedProperty($this->twigExtension, 'usageUpdateManager', $usageUpdateManager);
    $this->setProtectedProperty($this->twigExtension, 'renderer', $renderer);

    $result = $this->twigExtension->renderStyles('test_component_2', 'cohesion_component');
    $this->assertEquals('rendered_with_custom_styles', $result);
  }

  /**
   * Tests renderStyles returns empty when no styles to load.
   *
   * @covers \Drupal\cohesion_templates\TwigExtension\TwigExtension::renderStyles
   */
  public function testRenderStylesReturnsEmptyWhenNoStylesToLoad(): void {
    $entity = $this->createMock('\Drupal\cohesion_elements\Entity\Component');
    $entity->method('uuid')->willReturn('test-uuid');

    $storage = $this->createMock('\Drupal\Core\Entity\EntityStorageInterface');
    $storage->method('load')->willReturn($entity);

    $manager = $this->createMock('\Drupal\Core\Entity\EntityTypeManagerInterface');
    $manager->method('getStorage')->willReturn($storage);

    $cohesionUtils = $this->createMock('\Drupal\cohesion\Services\CohesionUtils');
    $cohesionUtils->method('loadElementStylesOnPageOnly')->willReturn(FALSE);

    $usageUpdateManager = $this->createMock('\Drupal\cohesion\UsageUpdateManager');
    $usageUpdateManager->method('getEntitiesInUseInSource')->willReturn([]);

    $this->setProtectedProperty($this->twigExtension, 'entityTypeManager', $manager);
    $this->setProtectedProperty($this->twigExtension, 'cohesionUtils', $cohesionUtils);
    $this->setProtectedProperty($this->twigExtension, 'usageUpdateManager', $usageUpdateManager);

    $result = $this->twigExtension->renderStyles('test_component_no_styles', 'cohesion_component');
    $this->assertEquals('', $result);
  }

  /**
   * Tests renderStyles uses memoization for repeated calls.
   *
   * @covers \Drupal\cohesion_templates\TwigExtension\TwigExtension::renderStyles
   */
  public function testRenderStylesMemoizesResults(): void {
    $entity = $this->createMock('\Drupal\cohesion_elements\Entity\Component');
    $entity->method('uuid')->willReturn('test-uuid');

    $storage = $this->createMock('\Drupal\Core\Entity\EntityStorageInterface');
    $storage->expects($this->once())
      ->method('load')
      ->with('memoized_component')
      ->willReturn($entity);

    $manager = $this->createMock('\Drupal\Core\Entity\EntityTypeManagerInterface');
    $manager->method('getStorage')->willReturn($storage);

    $cohesionUtils = $this->createMock('\Drupal\cohesion\Services\CohesionUtils');
    $cohesionUtils->method('loadElementStylesOnPageOnly')->willReturn(TRUE);

    $usageUpdateManager = $this->createMock('\Drupal\cohesion\UsageUpdateManager');
    $usageUpdateManager->method('getEntitiesInUseInSource')->willReturn([]);

    $renderer = $this->createMock('\Drupal\Core\Render\RendererInterface');
    $renderer->expects($this->once())
      ->method('render')
      ->willReturn('memoized_result');

    $this->setProtectedProperty($this->twigExtension, 'entityTypeManager', $manager);
    $this->setProtectedProperty($this->twigExtension, 'cohesionUtils', $cohesionUtils);
    $this->setProtectedProperty($this->twigExtension, 'usageUpdateManager', $usageUpdateManager);
    $this->setProtectedProperty($this->twigExtension, 'renderer', $renderer);

    $result1 = $this->twigExtension->renderStyles('memoized_component', 'cohesion_component');
    $result2 = $this->twigExtension->renderStyles('memoized_component', 'cohesion_component');

    $this->assertEquals('memoized_result', $result1);
    $this->assertEquals('memoized_result', $result2);
  }

  /**
   * Tests renderStyles handles CohesionLayout by getting parent entity.
   *
   * @covers \Drupal\cohesion_templates\TwigExtension\TwigExtension::renderStyles
   */
  public function testRenderStylesHandlesCohesionLayout(): void {
    $parentEntity = $this->createMock('\Drupal\Core\Entity\EntityInterface');
    $parentEntity->method('uuid')->willReturn('parent-uuid');

    $cohesionLayout = $this->createMock('\Drupal\cohesion_elements\Entity\CohesionLayout');
    $cohesionLayout->method('getParentEntity')->willReturn($parentEntity);

    $storage = $this->createMock('\Drupal\Core\Entity\EntityStorageInterface');
    $storage->method('load')->willReturn($cohesionLayout);

    $manager = $this->createMock('\Drupal\Core\Entity\EntityTypeManagerInterface');
    $manager->method('getStorage')->willReturn($storage);

    $cohesionUtils = $this->createMock('\Drupal\cohesion\Services\CohesionUtils');
    $cohesionUtils->method('loadElementStylesOnPageOnly')->willReturn(TRUE);

    $usageUpdateManager = $this->createMock('\Drupal\cohesion\UsageUpdateManager');
    $usageUpdateManager->expects($this->once())
      ->method('getEntitiesInUseInSource')
      ->with($parentEntity, 'cohesion_custom_style')
      ->willReturn([]);

    $renderer = $this->createMock('\Drupal\Core\Render\RendererInterface');
    $renderer->method('render')->willReturn('layout_rendered');

    $this->setProtectedProperty($this->twigExtension, 'entityTypeManager', $manager);
    $this->setProtectedProperty($this->twigExtension, 'cohesionUtils', $cohesionUtils);
    $this->setProtectedProperty($this->twigExtension, 'usageUpdateManager', $usageUpdateManager);
    $this->setProtectedProperty($this->twigExtension, 'renderer', $renderer);

    $result = $this->twigExtension->renderStyles('layout_id', 'cohesion_layout');
    $this->assertEquals('layout_rendered', $result);
  }

  /**
   * Tests renderStyles caches UUID-to-ID mapping across different components.
   *
   * When multiple components reference the same custom style UUID, the
   * loadByProperties call should only happen once per UUID for the entire
   * request, not once per component.
   *
   * @covers \Drupal\cohesion_templates\TwigExtension\TwigExtension::renderStyles
   */
  public function testRenderStylesCachesUuidToIdMapping(): void {
    $sharedCustomStyleUuid = 'shared-custom-style-uuid';

    $customStyle = $this->createMock('\Drupal\cohesion_custom_styles\Entity\CustomStyle');
    $customStyle->method('id')->willReturn('shared_style');

    $entity1 = $this->createMock('\Drupal\cohesion_elements\Entity\Component');
    $entity1->method('uuid')->willReturn('entity-1-uuid');

    $entity2 = $this->createMock('\Drupal\cohesion_elements\Entity\Component');
    $entity2->method('uuid')->willReturn('entity-2-uuid');

    $componentStorage = $this->createMock('\Drupal\Core\Entity\EntityStorageInterface');
    $componentStorage->method('load')->willReturnCallback(function ($id) use ($entity1, $entity2) {
      return $id === 'component_uuid_cache_1' ? $entity1 : $entity2;
    });

    $customStyleStorage = $this->createMock('\Drupal\Core\Entity\EntityStorageInterface');
    $customStyleStorage->expects($this->once())
      ->method('loadByProperties')
      ->with(['uuid' => $sharedCustomStyleUuid])
      ->willReturn([$customStyle]);

    $manager = $this->createMock('\Drupal\Core\Entity\EntityTypeManagerInterface');
    $manager->method('getStorage')->willReturnCallback(function ($entityType) use ($componentStorage, $customStyleStorage) {
      return $entityType === 'cohesion_custom_style' ? $customStyleStorage : $componentStorage;
    });

    $cohesionUtils = $this->createMock('\Drupal\cohesion\Services\CohesionUtils');
    $cohesionUtils->method('loadElementStylesOnPageOnly')->willReturn(FALSE);

    $usageUpdateManager = $this->createMock('\Drupal\cohesion\UsageUpdateManager');
    $usageUpdateManager->method('getEntitiesInUseInSource')
      ->willReturn([$sharedCustomStyleUuid => ['source_uuid' => 'any']]);

    $renderer = $this->createMock('\Drupal\Core\Render\RendererInterface');
    $renderer->method('render')->willReturn('rendered');

    $this->setProtectedProperty($this->twigExtension, 'entityTypeManager', $manager);
    $this->setProtectedProperty($this->twigExtension, 'cohesionUtils', $cohesionUtils);
    $this->setProtectedProperty($this->twigExtension, 'usageUpdateManager', $usageUpdateManager);
    $this->setProtectedProperty($this->twigExtension, 'renderer', $renderer);

    $this->twigExtension->renderStyles('component_uuid_cache_1', 'cohesion_component');
    $this->twigExtension->renderStyles('component_uuid_cache_2', 'cohesion_component');
  }

  /**
   * Tests renderStyles handles NULL custom style gracefully in UUID cache.
   *
   * When a custom style UUID doesn't resolve to an entity (e.g., deleted),
   * the NULL result should be cached to avoid repeated failed lookups.
   *
   * @covers \Drupal\cohesion_templates\TwigExtension\TwigExtension::renderStyles
   */
  public function testRenderStylesCachesNullCustomStyleId(): void {
    $deletedCustomStyleUuid = 'deleted-custom-style-uuid';

    $entity1 = $this->createMock('\Drupal\cohesion_elements\Entity\Component');
    $entity1->method('uuid')->willReturn('entity-uuid');

    $entity2 = $this->createMock('\Drupal\cohesion_elements\Entity\Component');
    $entity2->method('uuid')->willReturn('entity-2-uuid');

    $componentStorage = $this->createMock('\Drupal\Core\Entity\EntityStorageInterface');
    $componentStorage->method('load')->willReturnCallback(function ($id) use ($entity1, $entity2) {
      return $id === 'component_null_cache_1' ? $entity1 : $entity2;
    });

    $customStyleStorage = $this->createMock('\Drupal\Core\Entity\EntityStorageInterface');
    $customStyleStorage->expects($this->once())
      ->method('loadByProperties')
      ->with(['uuid' => $deletedCustomStyleUuid])
      ->willReturn([]);

    $manager = $this->createMock('\Drupal\Core\Entity\EntityTypeManagerInterface');
    $manager->method('getStorage')->willReturnCallback(function ($entityType) use ($componentStorage, $customStyleStorage) {
      return $entityType === 'cohesion_custom_style' ? $customStyleStorage : $componentStorage;
    });

    $cohesionUtils = $this->createMock('\Drupal\cohesion\Services\CohesionUtils');
    $cohesionUtils->method('loadElementStylesOnPageOnly')->willReturn(TRUE);

    $usageUpdateManager = $this->createMock('\Drupal\cohesion\UsageUpdateManager');
    $usageUpdateManager->method('getEntitiesInUseInSource')
      ->willReturn([$deletedCustomStyleUuid => ['source_uuid' => 'any']]);

    $renderer = $this->createMock('\Drupal\Core\Render\RendererInterface');
    $renderer->method('render')->willReturn('rendered');

    $this->setProtectedProperty($this->twigExtension, 'entityTypeManager', $manager);
    $this->setProtectedProperty($this->twigExtension, 'cohesionUtils', $cohesionUtils);
    $this->setProtectedProperty($this->twigExtension, 'usageUpdateManager', $usageUpdateManager);
    $this->setProtectedProperty($this->twigExtension, 'renderer', $renderer);

    $this->twigExtension->renderStyles('component_null_cache_1', 'cohesion_component');
    $this->twigExtension->renderStyles('component_null_cache_2', 'cohesion_component');
  }

  /**
   * Tests renderStyles skips library for NULL custom style ID.
   *
   * When a custom style UUID resolves to NULL (deleted style), no library
   * should be attached for that style.
   *
   * @covers \Drupal\cohesion_templates\TwigExtension\TwigExtension::renderStyles
   */
  public function testRenderStylesSkipsNullCustomStyleLibrary(): void {
    $validUuid = 'valid-custom-style-uuid';
    $deletedUuid = 'deleted-custom-style-uuid-skip-null-library';

    $validCustomStyle = $this->createMock('\Drupal\cohesion_custom_styles\Entity\CustomStyle');
    $validCustomStyle->method('id')->willReturn('valid_style');

    $entity = $this->createMock('\Drupal\cohesion_elements\Entity\Component');
    $entity->method('uuid')->willReturn('entity-uuid');

    $componentStorage = $this->createMock('\Drupal\Core\Entity\EntityStorageInterface');
    $componentStorage->method('load')->willReturn($entity);

    $customStyleStorage = $this->createMock('\Drupal\Core\Entity\EntityStorageInterface');
    $customStyleStorage->method('loadByProperties')->willReturnCallback(function ($props) use ($validCustomStyle, $validUuid) {
      return $props['uuid'] === $validUuid ? [$validCustomStyle] : [];
    });

    $manager = $this->createMock('\Drupal\Core\Entity\EntityTypeManagerInterface');
    $manager->method('getStorage')->willReturnCallback(function ($entityType) use ($componentStorage, $customStyleStorage) {
      return $entityType === 'cohesion_custom_style' ? $customStyleStorage : $componentStorage;
    });

    $cohesionUtils = $this->createMock('\Drupal\cohesion\Services\CohesionUtils');
    $cohesionUtils->method('loadElementStylesOnPageOnly')->willReturn(FALSE);

    $usageUpdateManager = $this->createMock('\Drupal\cohesion\UsageUpdateManager');
    $usageUpdateManager->method('getEntitiesInUseInSource')
      ->willReturn([
        $validUuid => ['source_uuid' => 'any'],
        $deletedUuid => ['source_uuid' => 'any'],
      ]);

    $renderer = $this->createMock('\Drupal\Core\Render\RendererInterface');
    $renderer->method('render')->willReturnCallback(function ($build) {
      $this->assertArrayHasKey('#attached', $build);
      $libraries = $build['#attached']['library'];
      $this->assertContains('cohesion/coh_custom_style_valid_style', $libraries);
      $this->assertNotContains('cohesion/coh_custom_style_', array_filter($libraries, function ($lib) {
        return $lib === 'cohesion/coh_custom_style_';
      }));
      $this->assertCount(1, $libraries);
      return 'rendered';
    });

    $this->setProtectedProperty($this->twigExtension, 'entityTypeManager', $manager);
    $this->setProtectedProperty($this->twigExtension, 'cohesionUtils', $cohesionUtils);
    $this->setProtectedProperty($this->twigExtension, 'usageUpdateManager', $usageUpdateManager);
    $this->setProtectedProperty($this->twigExtension, 'renderer', $renderer);

    $result = $this->twigExtension->renderStyles('component_skip_null', 'cohesion_component');
    $this->assertEquals('rendered', $result);
  }

  /**
   * Tests drupalViewItem with a single-row render array.
   *
   * Confirms no view-mode override means the row's existing '#view_mode'
   * is preserved.
   *
   * @covers \Drupal\cohesion_templates\TwigExtension\TwigExtension::drupalViewItem
   */
  public function testDrupalViewItemSingleRow(): void {
    $this->mockRenderer(fn($b) => $b['#view_mode'] ?? 'none');
    $row = ['#view_mode' => 'teaser'];
    $this->assertEquals('teaser', $this->twigExtension->drupalViewItem([], $row));
  }

  /**
   * Tests drupalViewItem with a full view render array containing '#rows'.
   *
   * Confirms the first row is extracted and rendered.
   *
   * @covers \Drupal\cohesion_templates\TwigExtension\TwigExtension::drupalViewItem
   */
  public function testDrupalViewItemFullRowsArray(): void {
    $this->mockRenderer(fn($b) => $b['#markup']);
    $rows = ['#rows' => [['#markup' => 'first'], ['#markup' => 'second']]];
    $this->assertEquals('first', $this->twigExtension->drupalViewItem([], $rows));
  }

  /**
   * Tests drupalViewItem returns an empty string for an empty row.
   *
   * @covers \Drupal\cohesion_templates\TwigExtension\TwigExtension::drupalViewItem
   */
  public function testDrupalViewItemEmptyRowReturnsEmptyString(): void {
    $this->assertEquals('', $this->twigExtension->drupalViewItem([], []));
  }

  /**
   * Tests drupalViewItem resolves a bundle-specific view mode from config.
   *
   * @covers \Drupal\cohesion_templates\TwigExtension\TwigExtension::drupalViewItem
   */
  public function testDrupalViewItemResolvesBundleViewMode(): void {
    $entity = $this->createMock('\Drupal\node\Entity\Node');
    $entity->method('getEntityTypeId')->willReturn('node');
    $entity->method('bundle')->willReturn('article');
    $this->mockRenderer(fn($b) => $b);

    $view_modes = [['entity_type' => 'node', 'bundle' => 'article', 'view_mode' => 'card']];
    $row = ['#view_mode' => 'default', 'entity' => $entity];
    $result = $this->twigExtension->drupalViewItem($view_modes, $row);

    $this->assertEquals('card', $result['#view_mode']);
    $this->assertContains('card', $result['#cache']['keys']);
  }

  /**
   * Tests the view iterate trio: set, get, and increment.
   *
   * @covers \Drupal\cohesion_templates\TwigExtension\TwigExtension::setViewIterate
   * @covers \Drupal\cohesion_templates\TwigExtension\TwigExtension::getViewIterate
   * @covers \Drupal\cohesion_templates\TwigExtension\TwigExtension::incrementViewIterate
   */
  public function testViewIterateSetGetIncrement(): void {
    $context = [];
    $this->assertEquals(0, $this->twigExtension->getViewIterate($context));

    $this->twigExtension->setViewIterate($context);
    $this->twigExtension->incrementViewIterate($context);
    $this->twigExtension->incrementViewIterate($context);
    $this->assertEquals(2, $this->twigExtension->getViewIterate($context));

    $this->twigExtension->setViewIterate($context);
    $this->assertEquals(0, $this->twigExtension->getViewIterate($context));
  }

  /**
   * Tests iterate state isolation across independent context arrays.
   *
   * State stored in one context array must not be visible from a
   * different, independent context array.
   *
   * @covers \Drupal\cohesion_templates\TwigExtension\TwigExtension::getViewIterate
   * @covers \Drupal\cohesion_templates\TwigExtension\TwigExtension::incrementViewIterate
   */
  public function testViewIterateStateIsNotSharedAcrossIndependentContexts(): void {
    $context_a = ['view' => (object) ['dom_id' => 'view-a']];
    $context_b = ['view' => (object) ['dom_id' => 'view-b']];

    $this->twigExtension->incrementViewIterate($context_a);
    $this->twigExtension->incrementViewIterate($context_a);

    $this->assertEquals(2, $this->twigExtension->getViewIterate($context_a));
    $this->assertEquals(0, $this->twigExtension->getViewIterate($context_b));
  }

  /**
   * Tests view iterate key fallback to view id and display.
   *
   * Exercises the property_exists() guarded branch when no dom_id is
   * present on the view object.
   *
   * @covers \Drupal\cohesion_templates\TwigExtension\TwigExtension::getViewIterate
   * @covers \Drupal\cohesion_templates\TwigExtension\TwigExtension::incrementViewIterate
   */
  public function testViewIterateKeyFallsBackToViewIdAndDisplay(): void {
    $view_a = $this->getMockBuilder(\stdClass::class)->addMethods(['id'])->getMock();
    $view_a->method('id')->willReturn('view_a');
    $view_a->display_id = 'block_1';

    $view_b = $this->getMockBuilder(\stdClass::class)->addMethods(['id'])->getMock();
    $view_b->method('id')->willReturn('view_b');
    $view_b->display_id = 'block_1';

    $context_a = ['view' => $view_a];
    $context_b = ['view' => $view_b];

    $this->twigExtension->incrementViewIterate($context_a);
    $this->twigExtension->incrementViewIterate($context_a);
    $this->twigExtension->incrementViewIterate($context_b);

    $this->assertEquals(2, $this->twigExtension->getViewIterate($context_a));
    $this->assertEquals(1, $this->twigExtension->getViewIterate($context_b));
  }

  /**
   * Tests view iterate key fallback to 'current_display'.
   *
   * Covers the case where 'display_id' is not a defined property on the
   * view object.
   *
   * @covers \Drupal\cohesion_templates\TwigExtension\TwigExtension::getViewIterate
   * @covers \Drupal\cohesion_templates\TwigExtension\TwigExtension::incrementViewIterate
   */
  public function testViewIterateKeyFallsBackToCurrentDisplayWhenDisplayIdUnset(): void {
    $view = $this->getMockBuilder(\stdClass::class)->addMethods(['id'])->getMock();
    $view->method('id')->willReturn('view_c');
    $view->current_display = 'page_1';
    // Note: 'display_id' is intentionally never set on $view.
    $context = ['view' => $view];
    $this->twigExtension->incrementViewIterate($context);

    $this->assertEquals(1, $this->twigExtension->getViewIterate($context));
  }

  /**
   * Mocks the renderer with a callback and sets it on the twig extension.
   *
   * @param callable $callback
   *   Callback receiving the render array and returning the render result.
   */
  private function mockRenderer(callable $callback): void {
    $renderer = $this->createMock('\Drupal\Core\Render\RendererInterface');
    $renderer->method('render')->willReturnCallback($callback);
    $this->setProtectedProperty($this->twigExtension, 'renderer', $renderer);
  }

}
