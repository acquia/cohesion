<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel;

use Drupal\cohesion\LayoutCanvas\Element;
use Drupal\cohesion\LayoutCanvas\ElementModel;
use Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\FieldHandlerBase;

/**
 * Test for the base form field handler.
 *
 * @group Cohesion
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\FieldHandlerBase
 */
class FieldHandlerBaseTest extends FormFieldHandlerTestBase {

  protected function setUp(): void {
    parent::setUp();
    $this->handler = $this->getMockForAbstractClass(
      FieldHandlerBase::class,
      [$this->moduleHandler]
    );
  }

  /**
   * The base class has an empty MAP/SCHEMA by default, so getData() should
   * return an empty array and getStaticSchema() should return an empty
   * array, regardless of the Element/ElementModel passed in.
   */
  public function testGetData() {
    $formField = $this->getMockBuilder(Element::class)
      ->disableOriginalConstructor()
      ->getMock();
    $elementModel = $this->getMockBuilder(ElementModel::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->assertIsArray($this->handler->getStaticSchema());
    $this->assertEquals([], $this->handler->getStaticSchema());
    $this->assertEquals([], $this->handler->getData($formField, $elementModel));
  }

}
