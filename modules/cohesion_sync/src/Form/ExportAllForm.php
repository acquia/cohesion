<?php

namespace Drupal\cohesion_sync\Form;

use Drupal\cohesion\Entity\CohesionSettingsInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for exporting a single configuration file.
 *
 * @internal
 */
class ExportAllForm extends ExportFormBase {

  /**
   * Tracks the valid config entity type definitions.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface[]
   */
  protected $definitions = [];

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dx8_sync_export_all_form';
  }

  /**
   * The filename of this export.
   *
   * @return string
   */
  private function getExportFilename() {
    // Get a filename safe verison of the site name.
    $site_name = preg_replace('/[^a-z0-9]+/', '-', strtolower(\Drupal::config('system.site')->get('name')));
    return $site_name . '.package.yml';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $config_type = NULL, $config_name = NULL) {

    $form['help'] = [
      '#markup' => $this->t('Export and download the full Site Studio configuration of this site including all dependencies and assets.'),
    ];

    if ($this->entityTypesAvailable()) {
      // The filename label.
      $form['export']['filename'] = [
        '#prefix' => '<p><em class="placeholder">',
        '#suffix' => '</em></p>',
        '#markup' => $this->getExportFilename(),
      ];

      // Add the download/push buttons.
      $this->addActionsToForm($form);
    }
    else {
      $this->showNoEntityTypesMessage();
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Build the excluded entity types up.
    $excluded_entity_type_ids = [];
    foreach ($this->configSyncSettings->get('enabled_entity_types') as $entity_type_id => $enabled) {
      if (!$enabled) {
        $excluded_entity_type_ids[] = $entity_type_id;
      }
    }

    // Loop over each entity type to get all the entities.
    $entities = [];
    foreach ($this->entityTypeManager->getDefinitions() as $entity_type => $definition) {
      if ($definition->entityClassImplements(CohesionSettingsInterface::class) && !in_array($entity_type, $excluded_entity_type_ids) && $entity_type !== 'custom_style_type') {
        try {
          $entity_storage = $this->entityTypeManager->getStorage($entity_type);
        }
        catch (\Exception $e) {
          continue;
        }

        /** @var \Drupal\Core\Config\Entity\ConfigEntityInterface $entity */
        foreach ($entity_storage->loadMultiple() as $entity) {
          if ($entity->status()) {
            $entities[] = $entity;
          }
        }
      }
    }

    // Force a download.
    $response = $this->packagerManager->sendYamlDownload($this->getExportFilename(), $entities, $excluded_entity_type_ids);
    try {
      $response->setContentDisposition('attachment', $this->getExportFilename());
      $form_state->setResponse($response);
    }
    catch (\Throwable $e) {
      // Failed, to build, so ignore the response and just show the error.
    }
  }

}
