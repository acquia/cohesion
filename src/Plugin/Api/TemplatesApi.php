<?php

namespace Drupal\cohesion\Plugin\Api;

use Drupal\cohesion\ApiPluginBase;
use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\cohesion_elements\Entity\Component;
use Drupal\cohesion_templates\Entity\ContentTemplates;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Send site studio template to its API.
 *
 * @package Drupal\cohesion
 *
 * @Api(
 *   id = "templates_api",
 *   name = @Translation("Templates send to API"),
 * )
 */
class TemplatesApi extends ApiPluginBase {

  /**
   *
   */
  public function getForms() {
    return [];
  }

  /**
   * @var \Drupal\cohesion\Entity\EntityJsonValuesInterface|\Drupal\cohesion\TemplateEntityTrait
   */
  protected $entity;

  /**
   * @var string
   */
  public $json_values;

  /**
   * @var string
   */
  public $filename;

  /**
   * @var array
   */
  private $content_hashes;

  /**
   * @var bool
   */
  private $is_preview = FALSE;

  /**
   * {@inheritdoc}
   */
  public function setEntity(EntityJsonValuesInterface $entity) {
    parent::setEntity($entity);
    $this->setJsonValues($this->entity->getJsonValues());
  }

  /**
   * Setter.
   *
   * @param $json_values
   */
  public function setJsonValues($json_values) {
    $this->json_values = $json_values;
  }

  /**
   * @param null $is_preview
   *
   * @return bool
   */
  public function isPreview($is_preview = NULL) {
    if ($is_preview !== NULL) {
      $this->is_preview = (bool) $is_preview;
    }

    return $this->is_preview;

  }

  /**
   * Replace the previously hashed content that comes back from the API.
   *
   * @return void
   */
  private function replaceDX8ContentTokens() {
    foreach ($this->getData() as $index => $responseData) {
      if (isset($responseData['template'])) {
        $template_values = Json::decode($responseData['template']);
        if (is_array($this->content_hashes)) {
          foreach ($this->content_hashes as $hash => $string) {

            // Make sure all single quotes are escaped in single quoted values
            // (unescape all then escape all).
            if (strpos($template_values['twig'], "'" . $hash . "'") !== FALSE) {
              $string = str_replace("\'", "'", $string);
              $string = str_replace("'", "\'", $string);
            }
            $string = '{% verbatim %}' . $string . '{% endverbatim %}';
            // Perform the replacement.
            $template_values['twig'] = str_replace($hash, $string, $template_values['twig']);
          }
        }
        $this->response['data'][$index]['template'] = json_encode($template_values, JSON_UNESCAPED_UNICODE);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function prepareData($attach_css = TRUE) {
    parent::prepareData($attach_css);

    // Layout builder field.
    if (isset($this->json_values)) {
      $layoutCanvas = new LayoutCanvas($this->json_values);
    }
    // Content/master/view/etc. template.
    else {
      $layoutCanvas = $this->entity->getLayoutCanvasInstance();
    }

    // If it's a content template, tell the API. This is used to wrap
    // content in an <article> tag for QuickEdit to work correctly.
    if ($this->entity instanceof ContentTemplates) {
      $this->data->settings->isContentTemplate = TRUE;
    }

    // If it's a component template, tell the API.
    if ($this->entity instanceof Component) {
      $this->data->settings->isComponentTemplate = TRUE;
    }

    if ($this->entity instanceof ContentEntityInterface || $this->is_preview) {
      $this->data->settings->isLayoutEntity = TRUE;

      // Rendering a component preview.
      $this->data->settings->isPreview = $this->is_preview;
    }

    // Search through the JSON model and turn tokens
    // into: [token.*|context|context].
    // String replace any raw content so the API doesn't see any sensitive data.
    $layoutCanvas->prepareDataForAPI($this->isPreview());
    $this->content_hashes = $layoutCanvas->getContentHashed();
    $this->data->templates = $layoutCanvas;
    $this->data->entity_id = $this->entity->id();
    $this->data->entity_type_id = $this->entity->getEntityTypeId();
  }

  /**
   * {@inheritdoc}
   */
  public function send() {
    $sendApi = parent::send();

    // If this is a layout builder on an entity, return the twig string.
    if ($this->entity instanceof ContentEntityInterface || $this->is_preview) {
      return $sendApi;
    }

    $this->saveData();
    return $sendApi;
  }

  public function saveData() {

    $templates = [];

    foreach ($this->getData() as $response) {
      if (isset($response['template']) && isset($response['themeName'])) {
        // Check for errors in template markup.
        $decoded_template = Json::decode($response['template']);

        if (isset($decoded_template['error'])) {
          \Drupal::messenger()->addError(t('Template compilation error (template has not been saved): @error', ['@error' => $decoded_template['error']]));
          return FALSE;
        }

        // Store each template in an array to determine whether they are all
        // unique.
        $templates[] = $response['template'];
      }
    }

    if ($this->getSaveData() && !empty($templates)) {
      $templates = array_unique($templates);
      // If each template are the same, none are theme specific (theme specific
      // template are created to have variations base on style guide manager
      // tokens).
      // Only one template has to be saved as it will work for all themes
      if (count($templates) == 1) {
        // Save the unique template and remove any theme specific template that
        // might have been saved if the template contained style guide manager
        // values.
        $this->saveResponseTemplate($templates[0]);
        $this->entity->removeThemeSpecificTemplates();
      }
      else {
        // Remove all theme global twig if any and no theme are set to generate
        // template only.
        $this->entity->removeGlobalTemplate();

        foreach ($this->getData() as $response) {
          if (isset($response['template']) && isset($response['themeName'])) {
            if ($response['themeName'] == 'coh-generic-theme') {
              // If one or more themes are set to generate templates, save a
              // global template for these themes to use.
              $this->saveResponseTemplate($response['template']);
            }
            else {
              $this->saveResponseTemplate($response['template'], $response['themeName']);
            }
          }
        }
      }
    }
  }

  /**
   * @param $template
   * @param null $theme_name
   *
   * @throws \Exception
   */
  private function saveResponseTemplate($template, $theme_name = NULL) {
    $decoded_template = Json::decode($template);
    $this->filename = $this->entity->getTwigFilename($theme_name);

    try {
      \Drupal::keyValue('coh_template_metadata')->set($this->filename, $decoded_template['metadata']);
    }
    catch (\Exception $e) {
      // There was no filename.
      \Drupal::logger('cohesion_templates')->notice("Template metadata did not contain a filename: @template_file", ['@template_file' => $this->filename]);
    }

    \Drupal::service('cohesion.template_storage')
      ->save($this->filename . '.html.twig', $decoded_template['twig']);

    $running_dx8_batch = &drupal_static('running_dx8_batch', FALSE);
    if(!$running_dx8_batch) {
      \Drupal::service('cohesion.template_storage')->commit();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function callApi() {
    $this->response = \Drupal::service('cohesion.api_client')->buildTemplate($this->data);
    $this->replaceDX8ContentTokens();
  }

}
