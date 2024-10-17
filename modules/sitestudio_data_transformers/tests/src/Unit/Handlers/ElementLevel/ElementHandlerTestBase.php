<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel;

use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\jsonapi\ResourceType\ResourceType;
use Drupal\jsonapi\ResourceType\ResourceTypeRepositoryInterface;
use Drupal\Tests\UnitTestCase;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use Shaper\Validator\JsonSchemaValidator;

/**
 * Base class for element handler unit tests.
 */
abstract class ElementHandlerTestBase extends UnitTestCase {

  const JSON_VALUES = '{}';

  /**
   * InputHandler.
   *
   * @var \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\FormFieldLevelHandlerInterface
   */
  protected $handler;

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Expected result output.
   *
   * @var array
   */
  protected $result;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $moduleExtensionMock = $this->getMockBuilder(Extension::class)
      ->disableOriginalConstructor()
      ->getMock();
    $moduleExtensionMock->expects($this->any())
      ->method('getPath')
      ->willReturn(__DIR__ . '/../../../../..');

    $moduleHandlerMock = $this->getMockBuilder(ModuleHandlerInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $moduleHandlerMock->expects($this->any())
      ->method('getModule')
      ->willReturn($moduleExtensionMock);
    $this->moduleHandler = $moduleHandlerMock;
  }

  /**
   *
   */
  public function testGetStaticSchema() {
    $this->assertIsArray($this->handler->getStaticSchema());
  }

  public function testGetData() {
    $layoutCanvas = new LayoutCanvas($this::JSON_VALUES);

    foreach ($layoutCanvas->iterateCanvas() as $element) {
      if ($element->isElement() && $element->getModel()) {
        foreach ($layoutCanvas->getCanvasElements() as $elementItem) {
          if (is_string($elementItem->getProperty('uid'))) {
            $elementData = $this->handler->getData($elementItem, $element->getModel());
          }
        }
      }
    }

    $this->assertEquals($this->result, $elementData);
    $validator = new JsonSchemaValidator(
      $this->handler->getStaticSchema(),
      new Validator(),
      Constraint::CHECK_MODE_TYPE_CAST
    );
    $this->assertTrue($validator->isValid($elementData));
  }

  /**
   * Builds and returns UrlGenerator Mock.
   *
   * @return \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected function getUrlGeneratorMock(): UrlGeneratorInterface {
    $urlGeneratorMock = $this->getMockBuilder(UrlGeneratorInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $urlGeneratorMock->expects($this->any())
      ->method('generateFromRoute')
      ->willReturnCallback(function ($name, $parameters) {
        return $name . '/' . $parameters['entity'];
      });

    return $urlGeneratorMock;
  }

  /**
   * Builds and returns UrlGenerator Mock.
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected function getEntityTypeManagerMock(): EntityTypeManagerInterface {
    $entityStorageMock = $this->getMockBuilder(EntityStorageInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $entityStorageMock->expects($this->any())
      ->method('loadByProperties')
      ->willReturnCallback(function () {
        $entityMock = $this->getMockBuilder(ContentEntityInterface::class)
          ->disableOriginalConstructor()
          ->getMock();
        $entityMock->expects($this->any())
          ->method('uuid')
          ->will($this->returnValue($this::ENTITY["entity"]));

        return [$entityMock];
      });
    $entityTypeManagerMock = $this->getMockBuilder(EntityTypeManagerInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $entityTypeManagerMock->expects($this->any())
      ->method('getStorage')
      ->willReturn($entityStorageMock);

    return $entityTypeManagerMock;
  }

  /**
   * Builds and returns ResourceTypeRepository Mock.
   * @return \Drupal\jsonapi\ResourceType\ResourceTypeRepositoryInterface
   */
  protected function getResourceTypeManagerMock(): ResourceTypeRepositoryInterface{
    $resourceTypeManagerMock = $this->getMockBuilder(ResourceTypeRepositoryInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $resourceTypeManagerMock->expects($this->any())
      ->method('get')
      ->willReturnCallback(function ($argument) {
        $resourceTypeMock = $this->getMockBuilder(ResourceType::class)
          ->disableOriginalConstructor()
          ->getMock();
        $resourceTypeMock->expects($this->any())
          ->method('getTypeName')
          ->will($this->returnValue($argument));

        return $resourceTypeMock;
      });

    return $resourceTypeManagerMock;
  }

}
