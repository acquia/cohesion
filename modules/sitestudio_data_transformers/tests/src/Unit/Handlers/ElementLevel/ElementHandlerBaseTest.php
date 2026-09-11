<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel;

use Drupal\sitestudio_data_transformers\Handlers\ElementLevel\ElementHandlerBase;

class ElementHandlerBaseTest extends ElementHandlerTestBase {

  protected function setUp(): void {
    parent::setUp();
    $this->handler = $this->getMockForAbstractClass(
      ElementHandlerBase::class,
      [$this->moduleHandler]
    );
  }

  public function testGetData() {
    $this->assertIsArray();
  }
}
