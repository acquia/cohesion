<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel;

use Drupal\cohesion\LayoutCanvas\Element;
use Drupal\cohesion\LayoutCanvas\ElementModel;
use Drupal\sitestudio_data_transformers\Handlers\ElementLevel\ElementHandlerBase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;

/**
 * Test for the base form field handler.
 *
 * @group Cohesion
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\ElementHandlerBase
 */
#[Group('Cohesion')]
#[CoversClass(\Drupal\sitestudio_data_transformers\Handlers\ElementLevel\ElementHandlerBase::class)]
class ElementHandlerBaseTest extends ElementHandlerTestBase {

  protected function setUp(): void {
    parent::setUp();
    $this->handler = $this->getMockForAbstractClass(
      ElementHandlerBase::class,
      [$this->moduleHandler]
    );
  }

  public function testGetData() {
    $element = $this->createMock(Element::class);
    $elementModel = $this->createMock(ElementModel::class);

    $this->assertSame([], $this->handler->getData($element, $elementModel));
  }
}
