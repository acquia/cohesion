<?php

namespace Drupal\Tests\cohesion\Unit\EventSubscriber;

use Drupal\cohesion\EventSubscriber\CohesionHtmlResponseSubscriber;
use Drupal\Core\Render\HtmlResponse;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @coversDefaultClass \Drupal\cohesion\EventSubscriber\CohesionHtmlResponseSubscriber
 * @group Cohesion
 */
class CohesionHtmlResponseSubscriberTest extends UnitTestCase {

  /**
   * The subscriber under test.
   *
   * @var \Drupal\cohesion\EventSubscriber\CohesionHtmlResponseSubscriber
   */
  protected $subscriber;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->subscriber = new CohesionHtmlResponseSubscriber();
  }

  /**
   * Builds a ResponseEvent wrapping the given HtmlResponse.
   */
  protected function createResponseEvent(HtmlResponse $response): ResponseEvent {
    $kernel = $this->createMock(HttpKernelInterface::class);
    $request = new Request();
    return new ResponseEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);
  }

  /**
   * @covers ::onRespond
   */
  public function testNonHtmlResponseIsIgnored() {
    $response = $this->createMock(\Symfony\Component\HttpFoundation\Response::class);
    $kernel = $this->createMock(HttpKernelInterface::class);
    $event = new ResponseEvent($kernel, new Request(), HttpKernelInterface::MAIN_REQUEST, $response);

    // Should return early without throwing or attempting to touch content.
    $this->subscriber->onRespond($event);
    $this->assertSame($response, $event->getResponse());
  }

  /**
   * @covers ::onRespond
   */
  public function testResponseWithoutCohesionAttachmentsIsUnchanged() {
    $content = '<html><body><p>No styles here</p></body></html>';
    $response = new HtmlResponse($content);
    $event = $this->createResponseEvent($response);

    $this->subscriber->onRespond($event);

    $this->assertSame($content, $event->getResponse()->getContent());
  }

  /**
   * @covers ::onRespond
   */
  public function testCohesionPlaceholderIsReplacedWithMinifiedStyles() {
    $content = '<html><head><cohesion-placeholder></cohesion-placeholder></head><body></body></html>';
    $response = new HtmlResponse($content);
    $response->setAttachments([
      'cohesion' => [
        "<style>\n.foo {\n  color: red;\n}\n</style>",
        "<style>.bar { color: blue; }</style>",
      ],
    ]);
    $event = $this->createResponseEvent($response);

    $this->subscriber->onRespond($event);

    $result = $event->getResponse()->getContent();
    $this->assertStringNotContainsString('<cohesion-placeholder></cohesion-placeholder>', $result);
    $this->assertStringContainsString('<style>.foo {  color: red;}</style>', $result);
    $this->assertStringContainsString('<style>.bar { color: blue; }</style>', $result);
  }

  /**
   * @covers ::onRespond
   *
   * Regression test for ACMS-6400: BigPipe no-JS attribute-safe placeholder
   * markers must be left completely intact, in Cohesion's own inline-style
   * output and everywhere else in the response. This subscriber runs at
   * priority -1, before BigPipe's HtmlResponseBigPipeSubscriber runs at
   * priority -10000 and converts the response to a BigPipeResponse. BigPipe
   * later resolves no-JS placeholders by matching their full marker strings
   * while streaming the response. Previously this subscriber stripped the
   * marker's prefix from Cohesion's style output, which prevented that
   * later pass from finding and resolving it there, leaving the bare
   * placeholder ID in the response instead of the real value.
   */
  public function testBigPipeTokenIsPreservedInCohesionStylesAndRestOfResponse() {
    $form_action_placeholder = 'form_action_p_pvdeGsVG5zNF_XLGPTvYSKCf43t8qZYSwcfZl2uzM';
    $unrelated_bigpipe_marker = 'big_pipe_nojs_placeholder_attribute_safe:' . $form_action_placeholder;
    $style_bigpipe_marker = 'big_pipe_nojs_placeholder_attribute_safe:.foo';

    $content = '<html><head><cohesion-placeholder></cohesion-placeholder></head>'
      . '<body><form action="' . $unrelated_bigpipe_marker . '"></form></body></html>';

    $response = new HtmlResponse($content);
    $response->setAttachments([
      'cohesion' => [
        '<style>' . $style_bigpipe_marker . ' { color: red; }</style>',
      ],
    ]);
    $event = $this->createResponseEvent($response);

    $this->subscriber->onRespond($event);

    $result = $event->getResponse()->getContent();

    // The marker must be left fully intact in Cohesion's own style output,
    // so BigPipe's later pass can still find and resolve it.
    $this->assertStringContainsString('<style>' . $style_bigpipe_marker . ' { color: red; }</style>', $result);
    $this->assertStringNotContainsString('<style>.foo { color: red; }</style>', $result);

    // And the unrelated BigPipe placeholder elsewhere on the page (e.g. the
    // form action) must also be left fully intact and untouched.
    $this->assertStringContainsString('action="' . $unrelated_bigpipe_marker . '"', $result);
    $this->assertStringNotContainsString('action="' . $form_action_placeholder . '"', $result);
  }

  /**
   * @covers ::minifyStyleBlock
   */
  public function testMinifyStyleBlockStripsNewlines() {
    $inline_styles = [];
    $this->subscriber->minifyStyleBlock($inline_styles, "<style>\n.foo {\r\n  color: red;\n}\n</style>");

    $this->assertSame(['<style>.foo {  color: red;}</style>'], $inline_styles);
  }

}
