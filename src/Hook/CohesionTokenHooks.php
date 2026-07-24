<?php

namespace Drupal\cohesion\Hook;

use Drupal\cohesion\ImageBrowserUpdateManager;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Hook implementations for Cohesion tokens.
 */
class CohesionTokenHooks {

  public function __construct(
    protected ImageBrowserUpdateManager $image_browser_update_manager,
    protected LanguageManagerInterface $language_manager,
    protected CsrfTokenGenerator $csrf_token,
  ) {}

  /**
   * Implements hook_token_info().
   */
  #[Hook('token_info')]
  public function token_info() {
    $info = [];
    $info['types']['media-reference'] = [
      'name' => t('Media reference'),
      'description' => t('Site Studio Group'),
    ];
    $info['tokens']['media-reference'] = [
      'file' => [
        'name' => t('File entity reference'),
        'title' => t('File entity reference'),
        'description' => t('A token to reference a file entity within Site Studio.'),
        'dynamic' => TRUE,
      ],
      'media' => [
        'title' => t('Media entity reference'),
        'name' => t('Media entity reference'),
        'description' => t('A token to reference a media entity, field and index within Site Studio.'),
        'dynamic' => TRUE,
      ],
    ];

    $info['types']['media-entity-reference'] = [
      'name' => t('Media entity reference'),
      'description' => t('Tokens for media entity references in Site Studio components.'),
      'needs-data' => 'cohesion_component',
    ];

    $info['tokens']['media-entity-reference'] = [
      'media-alt' => [
        'name' => t('Media entity reference alt text'),
        'title' => t('Media entity reference alt text'),
        'description' => t('Alt text of the media entity selected in a Site Studio component Image/Picture element.'),
      ],
    ];

    return $info;
  }

  /**
   * Implements hook_tokens().
   */
  #[Hook('tokens')]
  public function tokens($type, $tokens) {
    $replacements = [];
    if ($type == 'media-reference') {
      foreach ($tokens as $original) {
        if ($image = $this->image_browser_update_manager
          ->decodeToken($original)) {
          $replacements[$original] = $image['path'];
        }
      }
    }

    return $replacements;
  }

  /**
   * Implements template_preprocess_token_tree_link().
   *
   * Make the token modal appear in the center of the body.
   */
  #[Hook('preprocess_token_tree_link')]
  public function preprocess_token_tree_link(&$variables) {
    $variables['options']['attributes']['data-dialog-options'] = Json::encode([
      'dialogClass' => 'token-tree-dialog',
      'width' => 600,
      'height' => 400,
      'position' => ['my' => 'center left'],
      'draggable' => TRUE,
      'autoResize' => FALSE,
    ]);

    $variables['link'] = Link::createFromRoute(
      $variables['text'],
      'token.tree',
      [],
      $variables['options']
    )->toRenderable();
    $variables['url'] = new Url('token.tree', [], $variables['options']);
    $variables['attributes'] = $variables['options']['attributes'];
    // Add Drupal tokens link to 'drupalSettings' JS
    $this->cohesion_expose_drupal_token_links($variables);
  }

  /**
   *
   * @param array theme(cohesion_preprocess_token_tree_link) $variables
   * Add Drupal tokens link to 'drupalSettings'
   *   JS(drupalSettings.cohesion.drupalTokensUri,
   *   drupalSettings.cohesion.drupalTokensLink)
   */
  private function cohesion_expose_drupal_token_links(&$variables) {
    $language_none = $this->language_manager
      ->getLanguage(LanguageInterface::LANGCODE_NOT_APPLICABLE);
    $url = new Url('token.tree', [], $variables['options']);
    // Generate valid csrf token
    $options = $url->getOptions();
    $options['query']['token'] = $this->csrf_token->get($url->getInternalPath());
    $options['language'] = $language_none;
    $url->setOptions($options);
    $variables['#attached']['drupalSettings']['cohesion']['drupalTokensUri'] = urldecode($url->toString());
  }

}
