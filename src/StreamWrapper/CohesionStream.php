<?php

namespace Drupal\cohesion\StreamWrapper;

use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\DrupalKernel;
use Drupal\Core\Site\Settings;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class CohesionStream.
 *
 * Defines a Drupal public (cohesion://) stream wrapper class.
 *
 * Provides support for storing publicly accessible files with the Drupal file
 * interface.
 *
 * @package Drupal\cohesion\StreamWrapper
 */
class CohesionStream extends PublicStream {
  use StringTranslationTrait;

  /**
   * CohesionStream constructor.
   */
  public function __construct() {
    $this->stringTranslation = \Drupal::translation();
  }

  /**
   * {@inheritdoc}
   */
  public static function getType() {
    return StreamWrapperInterface::HIDDEN;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->t('Acquia Cohesion files');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Acquia Cohesion local files served by the webserver.');
  }

  /**
   * {@inheritdoc}
   */
  public function getDirectoryPath() {
    return static::basePath();
  }

  /**
   * {@inheritdoc}
   */
  public function getExternalUrl() {
    $path = str_replace('\\', '/', $this->getTarget());
    return static::baseUrl() . '/' . UrlHelper::encodePath($path);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseUrl() {
    $settings_base_url = Settings::get('file_public_base_url', '');
    if ($settings_base_url) {
      return (string) $settings_base_url . '/cohesion';
    }
    else {
      return $GLOBALS['base_url'] . '/' . static::basePath();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function basePath($site_path = NULL) {
    if ($site_path === NULL) {
      // Find the site path. Kernel service is not always available at this
      // point, but is preferred, when available.
      if (\Drupal::hasService('kernel')) {
        $site_path = \Drupal::service('site.path');
      }
      else {
        // If there is no kernel available yet, we call the static
        // findSitePath().
        $site_path = DrupalKernel::findSitePath(Request::createFromGlobals());
      }
    }
    return Settings::get('file_public_path', $site_path . '/files/cohesion');
  }

}
