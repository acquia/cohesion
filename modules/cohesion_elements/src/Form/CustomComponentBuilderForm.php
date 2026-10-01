<?php

namespace Drupal\cohesion_elements\Form;

use Drupal\cohesion_elements\CustomComponentsService;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Site Studio custom component builder form.
 *
 * @package Drupal\cohesion_elements\Form
 */
class CustomComponentBuilderForm extends FormBase {

  /**
   * Custom Components service.
   *
   * @var \Drupal\cohesion_elements\CustomComponentsService
   */
  protected $customComponents;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * The module handler service
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * @param \Drupal\cohesion_elements\CustomComponentsService $customComponents
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   */
  public function __construct(
    CustomComponentsService $customComponents,
    MessengerInterface $messenger,
    Request $request,
    ModuleHandlerInterface $module_handler,
  ) {
    $this->customComponents = $customComponents;
    $this->messenger = $messenger;
    $this->currentRequest = $request;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('custom.components'),
      $container->get('messenger'),
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('module_handler')
    );
  }

  /**
   * @return string
   */
  public function getFormId() {
    return 'custom-component';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Does the request have a machine name?
    // i.e. it's an existing custom component.
    if ($machineName = $this->currentRequest->attributes->get('machine_name')) {
      if ($customComponent = $this->customComponents->getComponent($machineName)) {
        $jsonValues = $this->customComponents->formatAsComponent($customComponent)->getJsonValues();
      }

      // If we're "editing" / "updating" an existing custom component form
      // add a warning.
      $this->messenger->addWarning(t('When making edits to your custom component form, remember to download and place the updated JSON file in your custom component module.'));

    }

    // Hidden title is required for component form preview.
    $form['title'] = [
      '#type' => 'hidden',
      '#attributes' => [
        'id' => 'edit-label',
      ],
    ];

    // Cohesion field instance.
    $form['cohesion'] = [
      // Drupal\cohesion\Element\CohesionField.
      '#type' => 'cohesionfield',
      '#json_values' => $jsonValues ?? '{}',
      '#json_mapper' => '{}',
      '#cohFormGroup' => 'custom_component',
      '#cohFormId' => 'custom_component',
      '#isContentEntity' => FALSE,
      '#canvas_name' => 'config_layout_canvas',
      '#entity' => 'cohesion_component',
    ];

    $form['download_json'] = [
      '#type' => 'html_tag',
      '#tag' => 'button',
      '#value' => t('Download form JSON'),
      '#attributes' => [
        'id' => 'download_json_values',
        'type' => 'button',
        'class' => 'button button--primary',
      ],
    ];

    // If TMGMT module exists then set it to true to allow toggling of
    // fields to translate.
    if ($this->moduleHandler->moduleExists('tmgmt')) {
      $form['#attached']['drupalSettings']['cohesion']['tmgmt'] = TRUE;
    }

    // Attach custom library for the form JSON download button.
    $form['#attached']['library'][] = 'cohesion_elements/custom-component-form-builder';

    // Attach shared page attachments needed.
    _cohesion_shared_page_attachments($form);

    return $form;
  }

  /**
   * This is use only to display the form
   * @inheritDoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
