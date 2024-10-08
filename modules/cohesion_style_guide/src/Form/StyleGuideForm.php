<?php

namespace Drupal\cohesion_style_guide\Form;

use Drupal\cohesion\ApiUtils;
use Drupal\cohesion\Form\CohesionBaseForm;
use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\cohesion\Services\JsonXss;
use Drupal\cohesion\Services\RebuildInuseBatch;
use Drupal\cohesion\UsageUpdateManager;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Style guide form.
 *
 * @package Drupal\cohesion_style_guide\Form
 */
class StyleGuideForm extends CohesionBaseForm {

  /**
   * The instance of the rebuild in use batch service.
   *
   * @var \Drupal\cohesion\Services\RebuildInuseBatch
   */
  protected $rebuildInUseBatch;

  /**
   * The instance of the update usage manager batch service.
   *
   * @var \Drupal\cohesion\UsageUpdateManager
   */
  protected $usageUpdateManager;

  /**
   * StyleGuideForm constructor.
   *
   * @param \Drupal\cohesion\ApiUtils $api_utils
   * @param \Drupal\cohesion\Services\JsonXss $json_xss
   * @param \Drupal\cohesion\Services\RebuildInuseBatch $rebuild_inuse_batch
   * @param \Drupal\cohesion\UsageUpdateManager $usage_update_manager
   */
  public function __construct(ApiUtils $api_utils, JsonXss $json_xss, RebuildInuseBatch $rebuild_inuse_batch, UsageUpdateManager $usage_update_manager) {
    parent::__construct($api_utils, $json_xss);
    $this->rebuildInUseBatch = $rebuild_inuse_batch;
    $this->usageUpdateManager = $usage_update_manager;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return \Drupal\cohesion\Form\CohesionBaseForm|\Drupal\Core\Entity\EntityForm
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new self(
    // Load the service required to construct this class.
      $container->get('cohesion.api.utils'),
      $container->get('cohesion.xss'),
      $container->get('cohesion.rebuild_inuse_batch'),
      $container->get('cohesion_usage.update_manager')
    );
  }

  /**
   * @var \Drupal\cohesion_style_guide\Entity\StyleGuide
   */
  protected $entity;

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    $form = parent::form($form, $form_state);
    $form['cohesion']['#token_browser'] = 'all';
    $form['cohesion']['#cohFormId'] = $this->entity->getAssetName();
    unset($form['cohesion']['#json_mapper']);

    // Machine name.
    $form['details']['machine_name'] = [
      '#title' => $this->t('Machine name'),
      '#maxlength' => 32 - strlen($this->entity->getEntityMachineNamePrefix()),
      '#access' => TRUE,
      '#weight' => 1,
      '#description' => $this->entity->getEntityMachineNamePrefix(),
      '#description_display' => 'before',
      '#default_value' => str_replace($this->entity->getEntityMachineNamePrefix(), '', $this->entity->id() ?? ''),
      '#type' => 'ajax_machine_name',
      '#required' => FALSE,
      '#machine_name' => [
        'source' => ['details', 'label'],
        'label' => t('Machine name'),
        'replace_pattern' => '[^a-z0-9\_]+',
        'replace' => '_',
        'field_prefix' => $this->entity->getEntityMachineNamePrefix(),
        'exists' => [$this, 'checkUniqueMachineName'],
        'entity_type_id' => $this->entity->getEntityTypeId(),
        'entity_id' => $this->entity->id(),
      ],
      '#disabled' => !$this->entity->canEditMachineName(),
    ];

    return $form;
  }

  /**
   * Required by machine name field validation.
   *
   * @param $value
   *
   * @return bool
   */
  public function exists($value) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state, $redirect = NULL) {

    $this->setEntityIdFromForm($this->entity, $form_state);
    $current_canvas = NULL;
    try {
      $current_entity = $this->entityTypeManager->getStorage($this->entity->getEntityTypeId())
        ->load($this->entity->id());
      if ($current_entity) {
        $current_canvas = $current_entity->getLayoutCanvasInstance();
      }
    }
    catch (\Exception $e) {
    }

    $status = parent::save($form, $form_state);

    $in_use_list = $this->usageUpdateManager->getInUseEntitiesList($this->entity);
    if (!empty($in_use_list) && $current_canvas) {
      $run_rebuild = FALSE;
      $canvasInstance = $this->entity->getLayoutCanvasInstance();
      $new_models = $canvasInstance->iterateModels('style_guide_form');
      foreach ($current_canvas->iterateModels('style_guide_form') as $model_uuid => $model) {
        // Rebuild if fields has been deleted or if field values have been
        // changed.
        if (!array_key_exists($model_uuid, $new_models) || $new_models[$model_uuid]->getValues() != $model->getValues()) {
          $run_rebuild = TRUE;
        }
      }

      if ($run_rebuild) {
        $this->rebuildInUseBatch->run($in_use_list);
      }
    }
    return $status;
  }

  /**
   * Validate the Element form.
   *
   * @inheritdoc
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    if ($json_values = $form_state->getValue('json_values')) {
      $canvasInstance = new LayoutCanvas($form_state->getValue('json_values'));

      $machine_names = [];
      $undefined_machines_names = [];
      foreach ($canvasInstance->iterateModels('style_guide_form') as $model) {
        if ($model->getElement()->getProperty(['type']) == 'form-field') {

          $machine_name = $model->getProperty(['settings', 'machineName']);
          $element_title = $model->getProperty(['settings', 'title']);
          if ($machine_name == '') {
            $undefined_machines_names[$model->getUUID()] = $element_title;
          }
          else {
            $machine_names[$machine_name][$model->getUUID()] = $element_title;
          }

        }
      }

      $error_count = 0;
      $layout_canvas_error = [];
      if (!empty($undefined_machines_names)) {
        $error_count++;
        $form_state->setErrorByName('cohesion_' . $error_count, $this->t('Undefined machine name(s). Please make sure to define a machine name for these form elements: %machine_names', ['%machine_names' => implode(', ', $undefined_machines_names)]));
        $layout_canvas_error = array_merge($layout_canvas_error, array_keys($undefined_machines_names));
      }

      foreach ($machine_names as $element_machine_name) {
        if (count($element_machine_name) > 1) {
          $error_count++;
          $form_state->setErrorByName('cohesion_' . $error_count, $this->t('Duplicate machine names. Please make sure to define unique machine names form these elements: %machine_names', ['%machine_names' => implode(', ', $element_machine_name)]));
          $layout_canvas_error = array_merge($layout_canvas_error, array_keys($element_machine_name));
        }
      }
    }

    if (!empty($layout_canvas_error)) {
      $form['#attached']['drupalSettings']['cohesion']['layout_canvas_errors'] = $layout_canvas_error;
    }

    // Check if the machine name is empty.
    if (empty($form_state->getValue('machine_name'))) {
      $form_state->setErrorByName('machine_name', $this->t('The machine name cannot be empty.'));
    }
  }

}
