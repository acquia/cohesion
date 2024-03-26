<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel;

use Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\FieldHandlerBase;

class FieldHandlerBaseTest extends FormFieldHandlerTestBase {

  protected function setUp(): void {
    parent::setUp();
    $this->handler = $this->getMockForAbstractClass(
      FieldHandlerBase::class,
      [$this->moduleHandler]
    );
  }

  public function testGetData() {
    $this->assertIsArray();
  }
}
