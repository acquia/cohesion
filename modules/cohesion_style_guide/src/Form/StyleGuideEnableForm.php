<?php

namespace Drupal\cohesion_style_guide\Form;

use Drupal\cohesion\Form\CohesionEnableForm;
use Drupal\cohesion\Services\RebuildInuseBatch;
use Drupal\cohesion\UsageUpdateManager;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Builds the form to disable custom style.
 */
class StyleGuideEnableForm extends CohesionEnableForm {

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
   *
   * @param \Drupal\cohesion\Services\RebuildInuseBatch $rebuild_inuse_batch
   * @param \Drupal\cohesion\UsageUpdateManager $usage_update_manager
   */
  public function __construct(RebuildInuseBatch $rebuild_inuse_batch, UsageUpdateManager $usage_update_manager) {
    $this->rebuildInUseBatch = $rebuild_inuse_batch;
    $this->usageUpdateManager = $usage_update_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('cohesion.rebuild_inuse_batch'),
      $container->get('cohesion_usage.update_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Enabling a <em>Style guide</em> will enable it within the <em>Style guide manager</em> within theme setting. 
      Token values created by the Style guide will work where applied.');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $in_use_list = $this->usageUpdateManager->getInUseEntitiesList($this->entity);
    parent::submitForm($form, $form_state);

    if (!empty($in_use_list)) {
      $this->rebuildInUseBatch->run($in_use_list);
    }

  }

}
