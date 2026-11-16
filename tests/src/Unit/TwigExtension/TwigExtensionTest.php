<?php

namespace Drupal\Tests\cohesion\Unit\Template;

use Drupal\cohesion_templates\TwigExtension\TwigExtension;
use Drupal\Core\Render\RendererInterface;
use Drupal\node\Entity\Node;
use Drupal\Tests\UnitTestCase;

/**
 * @group CohesionCurrent
 */
class TwigExtensionTest extends UnitTestCase {

  /**
   * The twig extension class to be tested
   */

  public $twigExtension;

  /**
   * Cohesion current route match service
   *
   * @var \Drupal\cohesion\Routing\CohesionCurrentRouteMatch|\PHPUnit\Framework\MockObject\MockObject
   */
  public $cohesion_current_route_match;

  /**
   * A user
   *
   * @var \Drupal\Core\Session\AccountInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  public $user;

  /**
   * A route object
   *
   * @var \Symfony\Component\Routing\Route|\PHPUnit\Framework\MockObject\MockObject
   */
  public $route;

  /**
   * Before a test method is run, setUp() is invoked.
   * Create new unit object.
   */
  public function setUp(): void {
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
    $this->cohesion_current_route_match = $this->createMock('Drupal\cohesion\Routing\CohesionCurrentRouteMatch');
    $this->cohesion_current_route_match->method('getRouteObject')->willReturn($this->route);
    $this->user = $this->createMock('Drupal\Core\Session\AccountInterface');

    $this->twigExtension = new TwigExtension($renderer, $token, $themeRegistry, $twigEnvironment, $uuid,
      $entity_type_manager, $stream_wrapper_manager, $extension_mime_type_guesser,
      $theme_manager, $cohesion_utils, $loggerChannelFactory, $this->cohesion_current_route_match, $this->user);

  }

  /**
   * Test addComponentFrontEndBuilderMarkup from TwigExtension
   *
   * @dataProvider addComponentFrontEndBuilderMarkupDataProvider
   *
   * @cover \Drupal\cohesion_templates\TwigExtension\TwigExtension::addComponentFrontEndBuilderMarkup
   */
  public function testAddComponentFrontEndBuilderMarkup($page_entity, $context, $componentInstanceUuid, $input_build, $expectation, $has_permission, $is_page_builder, $component_content_UUID = NULL, $component_content_id = NULL) {
    $this->user->method('hasPermission')->willReturn($has_permission);
    $this->route->method('getOption')->with('sitestudio_build')->willReturn($is_page_builder);
    $this->cohesion_current_route_match->method('getRouteEntities')->willReturn([$page_entity]);
    $test_build = $this->twigExtension->addComponentFrontEndBuilderMarkup($input_build, $context, $componentInstanceUuid, $component_content_UUID, $component_content_id);
    $this->assertEquals($expectation, $test_build);
  }

  /**
   * Data provider for ::testAddComponentFrontEndBuilderMarkup.
   * @return array
   */
  public function addComponentFrontEndBuilderMarkupDataProvider() {
    // Assertion data
    $node = $this->createMock('Drupal\node\Entity\Node');
    $block = $this->createMock('Drupal\block_content\Entity\BlockContent');

    $componentInstanceUuid = 'A-UUID';
    $input_build = ['key' => 'value'];

    $with_markup_expectation = [
      [
        '#type' => 'container',
        '#attributes' => [
          'data-coh-start' => [$componentInstanceUuid],
        ],
      ],
      $input_build,
      [
        '#type' => 'container',
        '#attributes' => [
          'data-coh-end' => [$componentInstanceUuid],
        ],
      ],
    ];

    $no_markup_expectation = [
      $input_build
    ];

    $cases = [];

    // Test that if the context contains the current entity of the route it adds the correct markup for the VPB to catch components
    $context = ['node' => $node];
    $cases[] = [$node, $context, $componentInstanceUuid, $input_build, $with_markup_expectation , TRUE, 'TRUE'];

    $context = ['block' => $block];
    // Test that if the context does not contain the current entity of the route it doesn ot adds the markup for the VPB to catch components
    $cases[] = [$node, $context, $componentInstanceUuid, $input_build, $no_markup_expectation , TRUE, 'TRUE'];

    // Test that if the user does not have permissions it does not return the VPB markup
    $context = ['node' => $node];
    $cases[] = [$node, $context, $componentInstanceUuid, $input_build, $no_markup_expectation , FALSE, 'TRUE'];

    // Test that if this is not a VPB api call it does not return the VPB markup
    $context = ['node' => $node];
    $cases[] = [$node, $context, $componentInstanceUuid, $input_build, $no_markup_expectation , TRUE, 'FALSE'];

    // Test that if the context contains hideContextualLinks it does not return the VPB markup
    $context = ['node' => $node, 'hideContextualLinks' => TRUE];
    $cases[] = [$node, $context, $componentInstanceUuid, $input_build, $no_markup_expectation , TRUE, 'TRUE'];

    // Test that if this is a preview and the context contains isPreview it does not return the VPB markup
    $context = ['node' => $node, 'isPreview' => TRUE];
    $cases[] = [$node, $context, $componentInstanceUuid, $input_build, $no_markup_expectation , TRUE, 'TRUE'];

    // Test that if a component content uuid is provided it renders an extra attribute
    $context = ['node' => $node];
    $component_content_UUID = 'component-content-UUID';
    $component_content_id = 'component-content-id';
    $component_content_expecation = $with_markup_expectation;
    $component_content_expecation[0]['#attributes']['data-coh-component-content-uuid'] = $component_content_UUID;
    $component_content_expecation[0]['#attributes']['data-coh-component-content-id'] = $component_content_id;
    $cases[] = [$node, $context, $componentInstanceUuid, $input_build, $component_content_expecation , TRUE, 'TRUE', $component_content_UUID, $component_content_id];

    return $cases;
  }

}
