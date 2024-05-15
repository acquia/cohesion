<?php

namespace Drupal\cohesion;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base plugin for image browser element.
 *
 * @package Drupal\cohesion
 */
abstract class ImageBrowserPluginBase extends PluginBase implements ImageBrowserPluginInterface, ContainerFactoryPluginInterface {

  /**
   * The StreamWrapperManager service.
   *
   * @var \Drupal\Core\StreamWrapper\StreamWrapperManager
   */
  protected $streamWrapperManager;

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * ImageBrowserPluginBase constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\Core\StreamWrapper\StreamWrapperManager $stream_wrapper_manager
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, StreamWrapperManager $stream_wrapper_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    // Save the injected services.
    $this->streamWrapperManager = $stream_wrapper_manager;
    $this->configFactory = $config_factory;
    $this->config = $config_factory->getEditable('cohesion.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('config.factory'), $container->get('stream_wrapper_manager'));
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->pluginDefinition['name'];
  }

  /**
   * {@inheritdoc}
   */
  public function getModule() {
    return $this->pluginDefinition['module'];
  }

}
